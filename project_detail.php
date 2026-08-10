<?php
require_once __DIR__ . '/includes/header.php';

$projectId = (int)($_GET['id'] ?? 0);
$project = getProjectById($projectId);

if (!$project) {
    echo '<div class="panel-card" style="text-align: center; padding: 4rem;">
            <h2>Project Not Found</h2>
            <p style="color: var(--text-muted); margin-top: 0.5rem;"><a href="index.php" style="color: var(--primary);">Return to Overview</a></p>
          </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$milestones = getMilestones($projectId);
$pendings   = getPendings($projectId);
$daysInfo   = getRemainingDaysInfo($project['target_completion_date'], $project['status']);
$statusClass = getStatusBadgeClass($project['status']);
$priorityClass = getPriorityBadgeClass($project['priority']);
?>

<!-- Detail View Header -->
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
  <div>
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
      <a href="index.php" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back to Dashboard</a>
      <span class="category-tag" style="border-left: 3px solid <?= $project['category_color'] ?>;">
        <span class="category-dot" style="background-color: <?= $project['category_color'] ?>;"></span>
        <?= htmlspecialchars($project['category_name']) ?>
      </span>
    </div>
    <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">
      <?= htmlspecialchars($project['title']) ?>
    </h1>
    <div style="display: flex; gap: 0.5rem; align-items: center; font-size: 0.9rem; color: var(--text-muted);">
      <span>Lead: <strong style="color: var(--text-main);"><?= htmlspecialchars($project['owner_name']) ?></strong></span> &bull; 
      <span>Started: <?= date('M j, Y', strtotime($project['start_date'])) ?></span> &bull; 
      <span>Target Completion: <?= date('M j, Y', strtotime($project['target_completion_date'])) ?></span>
    </div>
  </div>

  <div style="display: flex; gap: 0.75rem; align-items: center;">
    <span class="badge <?= $priorityClass ?>" style="padding: 0.5rem 1rem; font-size: 0.85rem;"><?= $project['priority'] ?> Priority</span>
    <span class="badge <?= $statusClass ?>" style="padding: 0.5rem 1rem; font-size: 0.85rem;"><?= $project['status'] ?></span>
    <button class="btn-primary-action" onclick="openModal('quickUpdateModal')">
      <i class="fa-solid fa-sliders"></i> Update Progress & Status
    </button>
  </div>
</div>

<!-- Attention Alert if flagged -->
<?php if ($project['needs_attention']): ?>
  <div class="attention-banner" style="font-size: 0.95rem; padding: 1rem 1.25rem; margin-bottom: 2rem;">
    <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.3rem;"></i>
    <div>
      <strong>Project Flagged as Blocked / Needing Attention:</strong><br>
      <?= htmlspecialchars($project['attention_reason'] ?: 'Requires executive escalation or team resolution.') ?>
    </div>
  </div>
<?php endif; ?>

<!-- Main 2-Column Detail Grid -->
<div class="detail-grid">
  <!-- Left Column: Overview & Milestones -->
  <div>
    <!-- Panel 1: Description & Progress -->
    <div class="panel-card">
      <div class="panel-title">
        <span>Project Overview</span>
        <span style="font-weight: 700; color: var(--accent-cyan);"><?= $project['progress_percent'] ?>% Completed</span>
      </div>

      <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem;">
        <?= nl2br(htmlspecialchars($project['description'] ?: 'No detailed description recorded.')) ?>
      </p>

      <div class="progress-section">
        <div class="progress-bar-bg" style="height: 12px;">
          <div class="progress-bar-fill <?= $project['progress_percent'] == 100 ? 'complete' : '' ?>" style="width: <?= $project['progress_percent'] ?>%;"></div>
        </div>
      </div>
    </div>

    <!-- Panel 2: Milestones Checklist -->
    <div class="panel-card">
      <div class="panel-title">
        <span>Project Milestones & Key Deliverables (<?= count($milestones) ?>)</span>
        <button class="btn-primary-action" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;" onclick="openModal('addMilestoneModal')">
          + Add Milestone
        </button>
      </div>

      <?php if (empty($milestones)): ?>
        <p style="color: var(--text-muted); font-size: 0.9rem;">No milestones created for this project yet.</p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <?php foreach ($milestones as $m): ?>
            <?php 
              $mClass = 'badge-info';
              if ($m['status'] === 'Completed') $mClass = 'badge-success';
              if ($m['status'] === 'Delayed')   $mClass = 'badge-danger';
            ?>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; background: rgba(15, 23, 42, 0.4); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <i class="fa-regular <?= $m['status'] === 'Completed' ? 'fa-circle-check' : 'fa-circle' ?>" 
                   style="font-size: 1.1rem; color: <?= $m['status'] === 'Completed' ? 'var(--accent-emerald)' : 'var(--text-dim)' ?>;"></i>
                <div>
                  <div style="font-weight: 600; color: var(--text-main); font-size: 0.92rem;"><?= htmlspecialchars($m['title']) ?></div>
                  <div style="font-size: 0.78rem; color: var(--text-muted);">Due: <?= date('M j, Y', strtotime($m['due_date'])) ?></div>
                </div>
              </div>

              <span class="badge <?= $mClass ?>"><?= $m['status'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right Column: Action Items & Pendings -->
  <div>
    <div class="panel-card">
      <div class="panel-title">
        <span>Active Pendings & Action Items</span>
        <button class="btn-primary-action" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;" onclick="openModal('addPendingModal')">
          + New Action Item
        </button>
      </div>

      <?php if (empty($pendings)): ?>
        <p style="color: var(--text-muted); font-size: 0.9rem; text-align: center; padding: 1.5rem 0;">
          No active pendings recorded for this project.
        </p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
          <?php foreach ($pendings as $p): ?>
            <?php $isResolved = ($p['status'] === 'Resolved'); ?>
            <div style="background: rgba(15, 23, 42, 0.5); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); opacity: <?= $isResolved ? '0.6' : '1' ?>;">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-main);"><?= htmlspecialchars($p['title']) ?></h4>
                <span class="badge <?= getPriorityBadgeClass($p['priority']) ?>" style="font-size: 0.7rem;"><?= $p['priority'] ?></span>
              </div>

              <?php if (!empty($p['description'])): ?>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.75rem;"><?= htmlspecialchars($p['description']) ?></p>
              <?php endif; ?>

              <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem; color: var(--text-dim);">
                <span>Assigned: <strong style="color: var(--text-muted);"><?= htmlspecialchars($p['assigned_to'] ?: 'Unassigned') ?></strong></span>
                
                <?php if ($isResolved): ?>
                  <span class="badge badge-success"><i class="fa-solid fa-check"></i> Resolved</span>
                <?php else: ?>
                  <button type="button" class="btn-primary-action" style="padding: 0.2rem 0.5rem; font-size: 0.72rem; background: rgba(16, 185, 129, 0.2); color: #34d399; box-shadow: none;"
                          onclick="togglePendingStatus(<?= $p['id'] ?>, 'Resolved')">
                    Mark Resolved
                  </button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal: Quick Update Progress & Status -->
<div class="modal-overlay" id="quickUpdateModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 style="font-size: 1.2rem; font-weight: 700;">Update Progress &amp; Status</h3>
      <button class="modal-close" onclick="closeModal('quickUpdateModal')">&times;</button>
    </div>

    <form action="api.php" method="POST">
      <input type="hidden" name="action" value="update_project">
      <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

      <div class="form-group">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem;">
          <label class="form-label">Completion Percentage</label>
          <strong id="progressValueDisplay" style="color: var(--accent-cyan); font-size: 1.1rem;"><?= $project['progress_percent'] ?>%</strong>
        </div>
        <input type="range" id="progressSlider" name="progress_percent" min="0" max="100" value="<?= $project['progress_percent'] ?>" style="width: 100%; accent-color: var(--primary);">
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" style="width: 100%;">
            <option value="In Progress" <?= $project['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
            <option value="Needs Attention" <?= $project['status'] === 'Needs Attention' ? 'selected' : '' ?>>Needs Attention</option>
            <option value="Under Review" <?= $project['status'] === 'Under Review' ? 'selected' : '' ?>>Under Review</option>
            <option value="Planning" <?= $project['status'] === 'Planning' ? 'selected' : '' ?>>Planning</option>
            <option value="On Hold" <?= $project['status'] === 'On Hold' ? 'selected' : '' ?>>On Hold</option>
            <option value="Completed" <?= $project['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Priority</label>
          <select name="priority" class="form-select" style="width: 100%;">
            <option value="Critical" <?= $project['priority'] === 'Critical' ? 'selected' : '' ?>>Critical</option>
            <option value="High" <?= $project['priority'] === 'High' ? 'selected' : '' ?>>High</option>
            <option value="Medium" <?= $project['priority'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
            <option value="Low" <?= $project['priority'] === 'Low' ? 'selected' : '' ?>>Low</option>
          </select>
        </div>
      </div>

      <div class="form-group" style="background: rgba(244, 63, 94, 0.08); padding: 1rem; border-radius: var(--radius-md); border: 1px solid rgba(244, 63, 94, 0.2);">
        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; font-weight: 600; color: #fecdd3;">
          <input type="checkbox" name="needs_attention" value="1" <?= $project['needs_attention'] ? 'checked' : '' ?> onchange="document.getElementById('editAttentionReasonGroup').style.display = this.checked ? 'block' : 'none';">
          Flag as "Needs Attention" / Blocked
        </label>
        <div id="editAttentionReasonGroup" style="display: <?= $project['needs_attention'] ? 'block' : 'none' ?>; margin-top: 0.75rem;">
          <input type="text" name="attention_reason" class="form-input" style="width: 100%;" value="<?= htmlspecialchars($project['attention_reason'] ?? '') ?>" placeholder="Describe active blocker or issue...">
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
        <button type="button" class="btn-primary-action" style="background: rgba(255,255,255,0.1); box-shadow: none;" onclick="closeModal('quickUpdateModal')">Cancel</button>
        <button type="submit" class="btn-primary-action">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Add Pending Action Item -->
<div class="modal-overlay" id="addPendingModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 style="font-size: 1.2rem; font-weight: 700;">Add Action Item / Pending</h3>
      <button class="modal-close" onclick="closeModal('addPendingModal')">&times;</button>
    </div>

    <form action="api.php" method="POST">
      <input type="hidden" name="action" value="create_pending">
      <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

      <div class="form-group">
        <label class="form-label">Title / Blocker Summary</label>
        <input type="text" name="title" class="form-input" style="width: 100%;" placeholder="e.g. Client approval for production certificate" required>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Assignee</label>
          <input type="text" name="assigned_to" class="form-input" style="width: 100%;" placeholder="e.g. Sarah Jenkins">
        </div>

        <div class="form-group">
          <label class="form-label">Priority</label>
          <select name="priority" class="form-select" style="width: 100%;">
            <option value="Urgent">Urgent</option>
            <option value="High" selected>High</option>
            <option value="Medium">Medium</option>
            <option value="Low">Low</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Description / Context</label>
        <textarea name="description" class="form-input" style="width: 100%; height: 70px;"></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
        <button type="button" class="btn-primary-action" style="background: rgba(255,255,255,0.1); box-shadow: none;" onclick="closeModal('addPendingModal')">Cancel</button>
        <button type="submit" class="btn-primary-action">Add Item</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Add Milestone -->
<div class="modal-overlay" id="addMilestoneModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 style="font-size: 1.2rem; font-weight: 700;">Add Project Milestone</h3>
      <button class="modal-close" onclick="closeModal('addMilestoneModal')">&times;</button>
    </div>

    <form action="api.php" method="POST">
      <input type="hidden" name="action" value="create_milestone">
      <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

      <div class="form-group">
        <label class="form-label">Milestone Title</label>
        <input type="text" name="title" class="form-input" style="width: 100%;" placeholder="e.g. User Acceptance Testing Sign-off" required>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Target Due Date</label>
          <input type="date" name="due_date" class="form-input" style="width: 100%;" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" style="width: 100%;">
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
          </select>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
        <button type="button" class="btn-primary-action" style="background: rgba(255,255,255,0.1); box-shadow: none;" onclick="closeModal('addMilestoneModal')">Cancel</button>
        <button type="submit" class="btn-primary-action">Save Milestone</button>
      </div>
    </form>
  </div>
</div>

<!-- Panel: Daily Updates Log History -->
    <div class="panel-card" style="margin-top: 1.5rem;">
      <div class="panel-title">
        <span>Daily Progress Updates</span>
      </div>

      <!-- Quick Add Daily Log Form -->
      <form action="api.php" method="POST" style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; align-items: center;">
        <input type="hidden" name="action" value="add_daily_log">
        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
        
        <input type="text" name="log_text" class="form-input" style="flex: 1;" placeholder="Log today's progress or update..." required>
        
        <label style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.85rem; color: #f87171; cursor: pointer; white-space: nowrap;">
          <input type="checkbox" name="is_blocked" value="1"> Flag Blocker
        </label>

        <button type="submit" class="btn-primary-action" style="padding: 0.55rem 1rem;">Post Log</button>
      </form>

      <!-- Logs Timeline -->
      <?php
        $db = getDBConnection();
        $stmtLogs = $db->prepare("SELECT * FROM daily_logs WHERE project_id = :pid ORDER BY created_at DESC LIMIT 10");
        $stmtLogs->execute(['pid' => $project['id']]);
        $dailyLogs = $stmtLogs->fetchAll();
      ?>

      <?php if (empty($dailyLogs)): ?>
        <p style="color: var(--text-muted); font-size: 0.88rem;">No daily update notes logged yet for this project.</p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <?php foreach ($dailyLogs as $log): ?>
            <div style="padding: 0.75rem 1rem; background: rgba(15, 23, 42, 0.4); border-radius: var(--radius-md); border-left: 3px solid <?= $log['is_blocked'] ? '#f87171' : '#6366f1' ?>;">
              <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                <span>Logged on: <?= date('M d, Y H:i', strtotime($log['created_at'])) ?></span>
                <?php if ($log['is_blocked']): ?>
                  <span class="badge badge-danger">BLOCKER REPORTED</span>
                <?php endif; ?>
              </div>
              <div style="font-size: 0.9rem; color: var(--text-main);"><?= htmlspecialchars($log['log_text']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Panel: Project Team Members -->
<div class="panel-card" style="margin-top: 1.5rem;">
  <div class="panel-title">
    <span>Project Team Allocation</span>
  </div>
  
  <?php 
    $projectTeam = getProjectTeam($project['id']);
  ?>
  <?php if (empty($projectTeam)): ?>
    <p style="color: var(--text-muted); font-size: 0.88rem;">No team members assigned to this project team yet.</p>
  <?php else: ?>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
      <?php foreach ($projectTeam as $tm): ?>
        <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-color); padding: 0.5rem 0.85rem; border-radius: var(--radius-md); display: flex; align-items: center; gap: 0.5rem;">
          <div class="owner-avatar" style="width: 22px; height: 22px; font-size: 0.65rem;">
            <?= strtoupper(substr($tm['full_name'], 0, 1)) ?>
          </div>
          <span style="font-size: 0.85rem; color: #fff; font-weight: 500;"><?= htmlspecialchars($tm['full_name']) ?></span>
          <span style="font-size: 0.75rem; color: var(--text-muted);">(<?= htmlspecialchars($tm['role_title']) ?>)</span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Panel: Tasks List -->
<div class="panel-card" style="margin-top: 1.5rem;">
  <div class="panel-title">
    <span>Project Tasks</span>
    <button class="btn-primary-action" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;" onclick="openModal('addTaskModal')">
      + Create Task
    </button>
  </div>

  <?php 
    $tasks = getProjectTasks($project['id']);
  ?>
  <?php if (empty($tasks)): ?>
    <p style="color: var(--text-muted); font-size: 0.88rem;">No tasks created for this project yet.</p>
  <?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
      <?php foreach ($tasks as $task): ?>
        <div style="padding: 0.75rem 1rem; background: rgba(15, 23, 42, 0.4); border-radius: var(--radius-md); border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
          <div>
            <div style="font-weight: 600; color: #fff; font-size: 0.92rem;"><?= htmlspecialchars($task['title']) ?></div>
            <div style="font-size: 0.78rem; color: var(--text-muted);">
              Assignee: <strong><?= htmlspecialchars($task['assignee_name'] ?: 'Unassigned') ?></strong> &bull; Due: <?= date('M d, Y', strtotime($task['due_date'])) ?>
            </div>
          </div>
          <span class="badge <?= getPriorityBadgeClass($task['priority']) ?>"><?= $task['priority'] ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Modal: Create Task -->
<div class="modal-overlay" id="addTaskModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 style="font-size: 1.2rem; font-weight: 700; color: #fff;">Create Project Task</h3>
      <button class="modal-close" onclick="closeModal('addTaskModal')">&times;</button>
    </div>

    <form action="api.php" method="POST">
      <input type="hidden" name="action" value="create_task">
      <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

      <div class="form-group">
        <label class="form-label">Task Title</label>
        <input type="text" name="title" class="form-input" style="width: 100%;" placeholder="e.g. Prepare staging environment" required>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Assign To (Project Team)</label>
          <select name="assigned_to" class="form-select" style="width: 100%;">
            <option value="">Unassigned</option>
            <?php foreach ($projectTeam as $tm): ?>
              <option value="<?= $tm['id'] ?>"><?= htmlspecialchars($tm['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Priority</label>
          <select name="priority" class="form-select" style="width: 100%;">
            <option value="Medium">Medium</option>
            <option value="High">High</option>
            <option value="Critical">Critical</option>
            <option value="Low">Low</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Target Due Date</label>
        <input type="date" name="due_date" class="form-input" style="width: 100%;" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
        <button type="button" class="btn-primary-action" style="background: rgba(255,255,255,0.1);" onclick="closeModal('addTaskModal')">Cancel</button>
        <button type="submit" class="btn-primary-action">Save Task</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
