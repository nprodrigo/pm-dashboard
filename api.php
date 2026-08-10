<?php
/**
 * Backend API & Form Request Handler
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
          || (isset($_POST['action']) && $_POST['action'] === 'update_progress');

$db = getDBConnection();
if (!$db) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database connection failed. Check config/database.php']);
        exit;
    } else {
        die('Database Connection Failed.');
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create_project':
    $title            = trim($_POST['title'] ?? '');
    $categoryId       = (int)($_POST['category_id'] ?? 1);
    $buId             = !empty($_POST['bu_id']) ? (int)$_POST['bu_id'] : null;
    $managerId        = !empty($_POST['manager_id']) ? (int)$_POST['manager_id'] : null;
    $teamMemberIds    = $_POST['team_member_ids'] ?? [];
    $priority         = $_POST['priority'] ?? 'Medium';
    $targetDate       = $_POST['target_completion_date'] ?? date('Y-m-d');
    $description      = trim($_POST['description'] ?? '');
    $needsAttention   = isset($_POST['needs_attention']) ? 1 : 0;
    $attentionReason  = trim($_POST['attention_reason'] ?? '');
    $startDate        = date('Y-m-d');

    // Fetch Manager Name for owner_name fallback
    $ownerName = 'Unassigned';
    if ($managerId) {
        $stmtM = $db->prepare("SELECT full_name FROM team_members WHERE id = :mid");
        $stmtM->execute(['mid' => $managerId]);
        $ownerName = $stmtM->fetchColumn() ?: 'Unassigned';
    }

    if ($title) {
        $stmt = $db->prepare("INSERT INTO projects (title, category_id, bu_id, manager_id, priority, owner_name, start_date, target_completion_date, description, needs_attention, attention_reason, status) 
                              VALUES (:title, :category_id, :bu_id, :manager_id, :priority, :owner_name, :start_date, :target_date, :description, :needs_attention, :attention_reason, :status)");
        $stmt->execute([
            'title'            => $title,
            'category_id'      => $categoryId,
            'bu_id'            => $buId,
            'manager_id'       => $managerId,
            'priority'         => $priority,
            'owner_name'       => $ownerName,
            'start_date'       => $startDate,
            'target_date'      => $targetDate,
            'description'      => $description,
            'needs_attention'  => $needsAttention,
            'attention_reason' => $needsAttention ? $attentionReason : null,
            'status'           => $needsAttention ? 'Needs Attention' : 'In Progress'
        ]);
        $newId = $db->lastInsertId();

        // Assign Team Members
        if (!empty($teamMemberIds)) {
            updateProjectTeam($newId, $teamMemberIds);
        }

        header("Location: project_detail.php?id=" . $newId);
        exit;
    }
    break;

    case 'add_daily_log':
        $projectId = (int)($_POST['project_id'] ?? 0);
        $logText   = trim($_POST['log_text'] ?? '');
        $isBlocked = isset($_POST['is_blocked']) ? 1 : 0;

        if ($projectId > 0 && !empty($logText)) {
            addDailyLog($projectId, $logText, $isBlocked);
            header("Location: project_detail.php?id=" . $projectId . "&msg=log_added");
            exit;
        }
        break;

    case 'update_project':
        $projectId        = (int)($_POST['project_id'] ?? 0);
        $progressPercent  = (int)($_POST['progress_percent'] ?? 0);
        $status           = $_POST['status'] ?? 'In Progress';
        $priority         = $_POST['priority'] ?? 'Medium';
        $needsAttention   = isset($_POST['needs_attention']) ? 1 : 0;
        $attentionReason  = trim($_POST['attention_reason'] ?? '');

        if ($progressPercent >= 100) {
            $status = 'Completed';
            $needsAttention = 0;
        }

        if ($projectId > 0) {
            $stmt = $db->prepare("UPDATE projects SET 
                                  progress_percent = :progress, 
                                  status = :status, 
                                  priority = :priority, 
                                  needs_attention = :needs_attention, 
                                  attention_reason = :attention_reason 
                                  WHERE id = :id");
            $stmt->execute([
                'progress'         => $progressPercent,
                'status'           => $status,
                'priority'         => $priority,
                'needs_attention'  => $needsAttention,
                'attention_reason' => $needsAttention ? $attentionReason : null,
                'id'               => $projectId
            ]);
            header("Location: project_detail.php?id=" . $projectId);
            exit;
        }
        break;

    case 'update_progress':
        header('Content-Type: application/json');
        $projectId       = (int)($_POST['project_id'] ?? 0);
        $progressPercent = (int)($_POST['progress_percent'] ?? 0);
        $status          = $_POST['status'] ?? null;

        if ($projectId > 0) {
            $sql = "UPDATE projects SET progress_percent = :progress";
            $params = ['progress' => $progressPercent, 'id' => $projectId];
            if ($status) {
                $sql .= ", status = :status";
                $params['status'] = $status;
            }
            if ($progressPercent >= 100) {
                $sql .= ", status = 'Completed', needs_attention = 0";
            }
            $sql .= " WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false, 'message' => 'Invalid project ID']);
        exit;

    case 'toggle_pending':
        header('Content-Type: application/json');
        $pendingId = (int)($_POST['pending_id'] ?? 0);
        $newStatus = $_POST['status'] ?? 'Resolved';

        if ($pendingId > 0) {
            $stmt = $db->prepare("UPDATE pendings SET status = :status, resolved_at = NOW() WHERE id = :id");
            $stmt->execute(['status' => $newStatus, 'id' => $pendingId]);
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false, 'message' => 'Invalid pending ID']);
        exit;

    case 'create_pending':
        $projectId   = (int)($_POST['project_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $assignedTo  = trim($_POST['assigned_to'] ?? '');
        $priority    = $_POST['priority'] ?? 'High';
        $description = trim($_POST['description'] ?? '');

        if ($projectId > 0 && $title) {
            $stmt = $db->prepare("INSERT INTO pendings (project_id, title, assigned_to, priority, description) 
                                  VALUES (:project_id, :title, :assigned_to, :priority, :description)");
            $stmt->execute([
                'project_id'  => $projectId,
                'title'       => $title,
                'assigned_to' => $assignedTo,
                'priority'    => $priority,
                'description' => $description
            ]);
            header("Location: project_detail.php?id=" . $projectId);
            exit;
        }
        break;

    case 'create_milestone':
        $projectId = (int)($_POST['project_id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $dueDate   = $_POST['due_date'] ?? date('Y-m-d');
        $status    = $_POST['status'] ?? 'Pending';

        if ($projectId > 0 && $title) {
            $stmt = $db->prepare("INSERT INTO milestones (project_id, title, due_date, status) VALUES (:project_id, :title, :due_date, :status)");
            $stmt->execute([
                'project_id' => $projectId,
                'title'      => $title,
                'due_date'   => $dueDate,
                'status'     => $status
            ]);
            header("Location: project_detail.php?id=" . $projectId);
            exit;
        }
        break;

        case 'create_task':
    $projectId   = (int)($_POST['project_id'] ?? 0);
    $assignedTo  = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
    $title       = trim($_POST['title'] ?? '');
    $priority    = $_POST['priority'] ?? 'Medium';
    $dueDate     = $_POST['due_date'] ?? date('Y-m-d');
    $description = trim($_POST['description'] ?? '');

    if ($projectId > 0 && $title) {
        $stmt = $db->prepare("INSERT INTO tasks (project_id, assigned_to, title, priority, due_date, description) 
                              VALUES (:pid, :assigned, :title, :priority, :due_date, :desc)");
        $stmt->execute([
            'pid'      => $projectId,
            'assigned' => $assignedTo,
            'title'    => $title,
            'priority' => $priority,
            'due_date' => $dueDate,
            'desc'     => $description
        ]);
        header("Location: project_detail.php?id=" . $projectId);
        exit;
    }
    break;

    case 'toggle_task':
    header('Content-Type: application/json');
    $taskId    = (int)($_POST['task_id'] ?? 0);
    $newStatus = $_POST['status'] ?? 'Completed';

    if ($taskId > 0) {
        $stmt = $db->prepare("UPDATE tasks SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $newStatus, 'id' => $taskId]);
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
    exit;
}

header("Location: index.php");
exit;