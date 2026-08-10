<?php
/**
 * Core Helper Functions & Database Access Logic
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Fetch top summary stats for executive header/dashboard
 */
function getDashboardMetrics() {
    $db = getDBConnection();
    if (!$db) {
        return [
            'total_projects' => 0,
            'avg_progress' => 0,
            'attention_needed' => 0,
            'finishing_soon' => 0,
            'total_pendings' => 0,
        ];
    }

    try {
        $totalProjects = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
        $avgProgress   = $db->query("SELECT COALESCE(ROUND(AVG(progress_percent)), 0) FROM projects WHERE status != 'Completed'")->fetchColumn();
        $attention     = $db->query("SELECT COUNT(*) FROM projects WHERE needs_attention = 1 OR status = 'Needs Attention'")->fetchColumn();
        
        // Projects finishing within 30 days
        $today = date('Y-m-d');
        $thirtyDays = date('Y-m-d', strtotime('+30 days'));
        $stmt = $db->prepare("SELECT COUNT(*) FROM projects WHERE target_completion_date BETWEEN :today AND :thirtyDays AND status != 'Completed'");
        $stmt->execute(['today' => $today, 'thirtyDays' => $thirtyDays]);
        $finishingSoon = $stmt->fetchColumn();

        $openPendings = $db->query("SELECT COUNT(*) FROM pendings WHERE status != 'Resolved'")->fetchColumn();

        return [
            'total_projects'   => (int)$totalProjects,
            'avg_progress'     => (int)$avgProgress,
            'attention_needed' => (int)$attention,
            'finishing_soon'   => (int)$finishingSoon,
            'total_pendings'   => (int)$openPendings,
        ];
    } catch (PDOException $e) {
        return [
            'total_projects' => 0, 'avg_progress' => 0, 'attention_needed' => 0, 'finishing_soon' => 0, 'total_pendings' => 0
        ];
    }
}

/**
 * Get all project categories
 */
function getCategories() {
    $db = getDBConnection();
    if (!$db) return [];
    try {
        return $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch projects with optional filters (category, status, priority, search)
 */
function getProjects($filters = []) {
    $db = getDBConnection();
    if (!$db) return [];

    $sql = "SELECT p.*, c.name as category_name, c.color_code as category_color, c.slug as category_slug,
            (SELECT COUNT(*) FROM pendings WHERE project_id = p.id AND status != 'Resolved') as open_pendings_count,
            (SELECT COUNT(*) FROM milestones WHERE project_id = p.id) as total_milestones,
            (SELECT COUNT(*) FROM milestones WHERE project_id = p.id AND status = 'Completed') as completed_milestones
            FROM projects p
            JOIN categories c ON p.category_id = c.id
            WHERE 1=1";
    
    $params = [];

    if (!empty($filters['category'])) {
        $sql .= " AND c.slug = :category";
        $params['category'] = $filters['category'];
    }

    if (!empty($filters['status'])) {
        if ($filters['status'] === 'Needs Attention') {
            $sql .= " AND (p.status = 'Needs Attention' OR p.needs_attention = 1)";
        } else {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }
    }

    if (!empty($filters['priority'])) {
        $sql .= " AND p.priority = :priority";
        $params['priority'] = $filters['priority'];
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (p.title LIKE :search OR p.owner_name LIKE :search OR p.description LIKE :search)";
        $params['search'] = '%' . $filters['search'] . '%';
    }

    if (!empty($filters['only_attention'])) {
        $sql .= " AND (p.needs_attention = 1 OR p.status = 'Needs Attention')";
    }

    // Default sorting: Needs attention first, then by priority/target completion
    $sql .= " ORDER BY p.needs_attention DESC, FIELD(p.priority, 'Critical', 'High', 'Medium', 'Low'), p.target_completion_date ASC";

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch single project detail with category info
 */
function getProjectById($id) {
    $db = getDBConnection();
    if (!$db) return null;

    try {
        $stmt = $db->prepare("SELECT p.*, c.name as category_name, c.color_code as category_color, c.slug as category_slug 
                              FROM projects p 
                              JOIN categories c ON p.category_id = c.id 
                              WHERE p.id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Fetch milestones for a project
 */
function getMilestones($projectId) {
    $db = getDBConnection();
    if (!$db) return [];
    try {
        $stmt = $db->prepare("SELECT * FROM milestones WHERE project_id = :project_id ORDER BY sort_order ASC, due_date ASC");
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch pendings / action items for a project
 */
function getPendings($projectId = null, $onlyOpen = false) {
    $db = getDBConnection();
    if (!$db) return [];
    
    $sql = "SELECT pend.*, proj.title as project_title, cat.name as category_name, cat.color_code as category_color
            FROM pendings pend
            JOIN projects proj ON pend.project_id = proj.id
            JOIN categories cat ON proj.category_id = cat.id
            WHERE 1=1";
    $params = [];

    if ($projectId !== null) {
        $sql .= " AND pend.project_id = :project_id";
        $params['project_id'] = $projectId;
    }

    if ($onlyOpen) {
        $sql .= " AND pend.status != 'Resolved'";
    }

    $sql .= " ORDER BY FIELD(pend.priority, 'Urgent', 'High', 'Medium', 'Low'), pend.due_date ASC";

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Helper: Calculate remaining days or overdue status
 */
function getRemainingDaysInfo($targetDate, $status = '') {
    if ($status === 'Completed') {
        return ['text' => 'Completed', 'class' => 'badge-success', 'is_overdue' => false];
    }

    $now = new DateTime(date('Y-m-d'));
    $target = new DateTime($targetDate);
    $diff = $now->diff($target);

    if ($now > $target) {
        $days = $diff->days;
        return [
            'text' => ($days === 0 ? 'Due Today' : "{$days} Days Overdue"),
            'class' => 'badge-danger',
            'is_overdue' => true,
            'days' => -$days
        ];
    } else {
        $days = $diff->days;
        if ($days <= 7) {
            $class = 'badge-warning';
        } else {
            $class = 'badge-info';
        }
        return [
            'text' => ($days === 0 ? 'Due Today' : "{$days} Days Left"),
            'class' => $class,
            'is_overdue' => false,
            'days' => $days
        ];
    }
}

/**
 * Helper: Get CSS class for project status
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Completed':       return 'status-completed';
        case 'In Progress':     return 'status-in-progress';
        case 'Needs Attention': return 'status-attention';
        case 'Under Review':    return 'status-review';
        case 'On Hold':         return 'status-on-hold';
        case 'Planning':        return 'status-planning';
        default:                return 'status-default';
    }
}

/**
 * Helper: Get CSS class for priority badge
 */
function getPriorityBadgeClass($priority) {
    switch ($priority) {
        case 'Critical':
        case 'Urgent':   return 'priority-critical';
        case 'High':     return 'priority-high';
        case 'Medium':   return 'priority-medium';
        case 'Low':      return 'priority-low';
        default:         return 'priority-default';
    }
}

/**
 * Get all Business Units
 */
function getBusinessUnits() {
    $db = getDBConnection();
    if (!$db) return [];
    try {
        return $db->query("SELECT * FROM business_units ORDER BY name ASC")->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Add a daily log entry for a project
 */
function addDailyLog($projectId, $logText, $isBlocked = 0) {
    $db = getDBConnection();
    if (!$db) return false;

    try {
        $stmt = $db->prepare("INSERT INTO daily_logs (project_id, log_text, is_blocked) VALUES (:project_id, :log_text, :is_blocked)");
        $stmt->execute([
            'project_id' => $projectId,
            'log_text'   => $logText,
            'is_blocked' => $isBlocked
        ]);

        if ($isBlocked) {
            $stmtAtt = $db->prepare("UPDATE projects SET needs_attention = 1, attention_reason = :reason WHERE id = :id");
            $stmtAtt->execute(['reason' => $logText, 'id' => $projectId]);
        }
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Fetch daily updates logged over the last 7 days grouped by Business Unit
 */
function getWeeklyReportData() {
    $db = getDBConnection();
    if (!$db) return [];

    $sql = "SELECT 
                bu.name AS bu_name,
                p.title AS project_title,
                p.owner_name,
                dl.log_text,
                dl.is_blocked,
                dl.created_at
            FROM daily_logs dl
            JOIN projects p ON dl.project_id = p.id
            LEFT JOIN business_units bu ON p.bu_id = bu.id
            WHERE dl.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY bu.name ASC, p.title ASC, dl.created_at DESC";

    try {
        return $db->query($sql)->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch daily updates logged for a specific date (defaults to today)
 */
function getDailyReportData($targetDate = null) {
    $db = getDBConnection();
    if (!$db) return [];

    if (!$targetDate) {
        $targetDate = date('Y-m-d');
    }

    $sql = "SELECT 
                bu.name AS bu_name,
                p.id AS project_id,
                p.title AS project_title,
                p.owner_name,
                p.status AS project_status,
                dl.id AS log_id,
                dl.log_text,
                dl.is_blocked,
                dl.created_at
            FROM daily_logs dl
            JOIN projects p ON dl.project_id = p.id
            LEFT JOIN business_units bu ON p.bu_id = bu.id
            WHERE DATE(dl.created_at) = :targetDate
            ORDER BY bu.name ASC, p.title ASC, dl.created_at DESC";

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute(['targetDate' => $targetDate]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch all team members
 */
function getTeamMembers() {
    $db = getDBConnection();
    if (!$db) return [];
    try {
        return $db->query("SELECT * FROM team_members ORDER BY full_name ASC")->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch team members assigned to a specific project
 */
function getProjectTeam($projectId) {
    $db = getDBConnection();
    if (!$db) return [];
    try {
        $stmt = $db->prepare("SELECT tm.* FROM team_members tm
                              JOIN project_teams pt ON tm.id = pt.member_id
                              WHERE pt.project_id = :pid
                              ORDER BY tm.full_name ASC");
        $stmt->execute(['pid' => $projectId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Assign a list of team members to a project
 */
function updateProjectTeam($projectId, $memberIds = []) {
    $db = getDBConnection();
    if (!$db) return false;
    try {
        $stmt = $db->prepare("DELETE FROM project_teams WHERE project_id = :pid");
        $stmt->execute(['pid' => $projectId]);

        if (!empty($memberIds)) {
            $insertStmt = $db->prepare("INSERT INTO project_teams (project_id, member_id) VALUES (:pid, :mid)");
            foreach ($memberIds as $mId) {
                $insertStmt->execute(['pid' => $projectId, 'mid' => (int)$mId]);
            }
        }
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Fetch tasks for a project with assignee details
 */
function getProjectTasks($projectId) {
    $db = getDBConnection();
    if (!$db) return [];
    try {
        $stmt = $db->prepare("SELECT t.*, tm.full_name as assignee_name 
                              FROM tasks t 
                              LEFT JOIN team_members tm ON t.assigned_to = tm.id 
                              WHERE t.project_id = :pid 
                              ORDER BY FIELD(t.priority, 'Critical', 'High', 'Medium', 'Low'), t.due_date ASC");
        $stmt->execute(['pid' => $projectId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}