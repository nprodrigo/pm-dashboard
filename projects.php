<?php
require_once __DIR__ . '/includes/header.php';

$selectedCategory = $_GET['category'] ?? '';
$selectedStatus   = $_GET['status'] ?? '';
$projects = getProjects(['category' => $selectedCategory, 'status' => $selectedStatus]);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h2 style="font-size: 1.5rem; font-weight: 700;">All Projects Directory</h2>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Comprehensive list of active, planned, and completed initiatives across all teams.</p>
  </div>
  
  <button class="btn-primary-action" onclick="openModal('addProjectModal')">
    <i class="fa-solid fa-plus"></i> Add Project
  </button>
</div>

<!-- Table Container -->
<div class="panel-card" style="padding: 0; overflow-x: auto;">
  <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
    <thead>
      <tr style="background: rgba(15, 23, 42, 0.6); border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">
        <th style="padding: 1rem 1.25rem;">Project Title</th>
        <th style="padding: 1rem;">Category</th>
        <th style="padding: 1rem;">Priority</th>
        <th style="padding: 1rem;">Status</th>
        <th style="padding: 1rem; width: 180px;">Progress</th>
        <th style="padding: 1rem;">Target End Date</th>
        <th style="padding: 1rem;">Owner</th>
        <th style="padding: 1rem; text-align: right;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($projects)): ?>
        <tr>
          <td colspan="8" style="padding: 2.5rem; text-align: center; color: var(--text-muted);">
            No projects found matching the criteria.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($projects as $project): ?>
          <?php 
            $daysInfo = getRemainingDaysInfo($project['target_completion_date'], $project['status']);
            $statusClass = getStatusBadgeClass($project['status']);
            $priorityClass = getPriorityBadgeClass($project['priority']);
          ?>
          <tr style="border-bottom: 1px solid var(--border-color); transition: var(--transition);" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
            <!-- Title -->
            <td style="padding: 1rem 1.25rem;">
              <a href="project_detail.php?id=<?= $project['id'] ?>" style="font-weight: 600; color: var(--text-main);">
                <?= htmlspecialchars($project['title']) ?>
              </a>
              <?php if ($project['needs_attention']): ?>
                <i class="fa-solid fa-triangle-exclamation" style="color: var(--accent-rose); margin-left: 0.4rem;" title="Needs Attention"></i>
              <?php endif; ?>
            </td>

            <!-- Category -->
            <td style="padding: 1rem;">
              <span class="category-tag" style="border-left: 3px solid <?= $project['category_color'] ?>;">
                <?= htmlspecialchars($project['category_name']) ?>
              </span>
            </td>

            <!-- Priority -->
            <td style="padding: 1rem;">
              <span class="badge <?= $priorityClass ?>"><?= $project['priority'] ?></span>
            </td>

            <!-- Status -->
            <td style="padding: 1rem;">
              <span class="badge <?= $statusClass ?>"><?= $project['status'] ?></span>
            </td>

            <!-- Progress -->
            <td style="padding: 1rem;">
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="progress-bar-bg" style="flex: 1;">
                  <div class="progress-bar-fill" style="width: <?= $project['progress_percent'] ?>%;"></div>
                </div>
                <span style="font-size: 0.8rem; font-weight: 700; min-width: 32px;"><?= $project['progress_percent'] ?>%</span>
              </div>
            </td>

            <!-- Target Date -->
            <td style="padding: 1rem;">
              <div><?= date('M j, Y', strtotime($project['target_completion_date'])) ?></div>
              <span class="badge <?= $daysInfo['class'] ?>" style="font-size: 0.7rem; padding: 0.1rem 0.4rem; margin-top: 0.2rem;">
                <?= $daysInfo['text'] ?>
              </span>
            </td>

            <!-- Owner -->
            <td style="padding: 1rem; color: var(--text-muted); font-weight: 500;">
              <?= htmlspecialchars($project['owner_name']) ?>
            </td>

            <!-- Action -->
            <td style="padding: 1rem; text-align: right;">
              <a href="project_detail.php?id=<?= $project['id'] ?>" class="btn-primary-action" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; box-shadow: none;">
                View Detail <i class="fa-solid fa-chevron-right"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
