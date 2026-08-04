<?php
require_once __DIR__ . '/includes/header.php';

// Fetch projects needing attention
$attentionProjects = getProjects(['only_attention' => true]);

// Fetch open pending blockers
$openPendings = getPendings(null, true);
?>

<div style="margin-bottom: 2rem;">
  <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.4rem;">
    <h2 style="font-size: 1.6rem; font-weight: 700; color: var(--accent-rose);">
      <i class="fa-solid fa-triangle-exclamation"></i> "What Needs Attention" Action Center
    </h2>
  </div>
  <p style="color: var(--text-muted); font-size: 0.9rem;">
    Central control hub highlighting active project blockers, critical bottlenecks, and unassigned action items requiring manager intervention.
  </p>
</div>

<!-- Section 1: Flagged Projects & Blockers -->
<div class="panel-card" style="border-color: rgba(244, 63, 94, 0.3);">
  <div class="panel-title">
    <span>Projects Flagged with Active Blockers (<?= count($attentionProjects) ?>)</span>
    <span class="badge badge-danger">High Priority Intervention</span>
  </div>

  <?php if (empty($attentionProjects)): ?>
    <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
      <i class="fa-solid fa-circle-check" style="font-size: 2.5rem; color: var(--accent-emerald); margin-bottom: 0.75rem;"></i>
      <p style="font-weight: 600; color: var(--text-main);">No projects currently flagged as blocked or needing attention!</p>
    </div>
  <?php else: ?>
    <div class="projects-grid">
      <?php foreach ($attentionProjects as $project): ?>
        <?php 
          $daysInfo = getRemainingDaysInfo($project['target_completion_date'], $project['status']);
          $priorityClass = getPriorityBadgeClass($project['priority']);
        ?>
        <div class="project-card attention-border">
          <div>
            <div class="project-card-header">
              <span class="category-tag" style="border-left: 3px solid <?= $project['category_color'] ?>;">
                <?= htmlspecialchars($project['category_name']) ?>
              </span>
              <span class="badge <?= $priorityClass ?>"><?= $project['priority'] ?></span>
            </div>

            <h3 class="project-title">
              <a href="project_detail.php?id=<?= $project['id'] ?>">
                <?= htmlspecialchars($project['title']) ?>
              </a>
            </h3>

            <div class="attention-banner">
              <i class="fa-solid fa-triangle-exclamation"></i>
              <div>
                <strong>Active Blocker:</strong><br>
                <?= htmlspecialchars($project['attention_reason'] ?: 'Flagged as requiring urgent attention.') ?>
              </div>
            </div>

            <div class="progress-section">
              <div class="progress-header">
                <span>Progress</span>
                <span><?= $project['progress_percent'] ?>%</span>
              </div>
              <div class="progress-bar-bg">
                <div class="progress-bar-fill critical" style="width: <?= $project['progress_percent'] ?>%;"></div>
              </div>
            </div>
          </div>

          <div class="project-card-footer">
            <span style="color: var(--text-muted);">Lead: <strong><?= htmlspecialchars($project['owner_name']) ?></strong></span>
            <a href="project_detail.php?id=<?= $project['id'] ?>" class="btn-primary-action" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; box-shadow: none;">
              Resolve Blocker <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Section 2: Open Action Items & Pendings Table -->
<div class="panel-card">
  <div class="panel-title">
    <span>Open Pending Action Items across all Projects (<?= count($openPendings) ?>)</span>
  </div>

  <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
    <thead>
      <tr style="background: rgba(15, 23, 42, 0.6); border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">
        <th style="padding: 0.85rem 1rem;">Action Item</th>
        <th style="padding: 0.85rem 1rem;">Project</th>
        <th style="padding: 0.85rem 1rem;">Assignee</th>
        <th style="padding: 0.85rem 1rem;">Priority</th>
        <th style="padding: 0.85rem 1rem;">Due Date</th>
        <th style="padding: 0.85rem 1rem; text-align: right;">Quick Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($openPendings)): ?>
        <tr>
          <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-muted);">
            No open pendings recorded!
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($openPendings as $item): ?>
          <?php $prioClass = getPriorityBadgeClass($item['priority']); ?>
          <tr style="border-bottom: 1px solid var(--border-color);">
            <td style="padding: 0.85rem 1rem;">
              <strong style="color: var(--text-main);"><?= htmlspecialchars($item['title']) ?></strong>
              <?php if (!empty($item['description'])): ?>
                <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($item['description']) ?></div>
              <?php endif; ?>
            </td>

            <td style="padding: 0.85rem 1rem;">
              <a href="project_detail.php?id=<?= $item['project_id'] ?>" style="color: var(--primary); font-weight: 500;">
                <?= htmlspecialchars($item['project_title']) ?>
              </a>
            </td>

            <td style="padding: 0.85rem 1rem; color: var(--text-muted);">
              <?= htmlspecialchars($item['assigned_to'] ?: 'Unassigned') ?>
            </td>

            <td style="padding: 0.85rem 1rem;">
              <span class="badge <?= $prioClass ?>"><?= $item['priority'] ?></span>
            </td>

            <td style="padding: 0.85rem 1rem; color: var(--text-muted);">
              <?= $item['due_date'] ? date('M j, Y', strtotime($item['due_date'])) : 'N/A' ?>
            </td>

            <td style="padding: 0.85rem 1rem; text-align: right;">
              <button class="btn-primary-action" style="padding: 0.3rem 0.6rem; font-size: 0.78rem; background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); box-shadow: none;"
                      onclick="togglePendingStatus(<?= $item['id'] ?>, 'Resolved')">
                <i class="fa-solid fa-check"></i> Mark Resolved
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
