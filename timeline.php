<?php
require_once __DIR__ . '/includes/header.php';

$projects = getProjects();
$selectedProjectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;

// Filter for single project if selected
if ($selectedProjectId > 0) {
    $filteredProjects = array_filter($projects, function($p) use ($selectedProjectId) {
        return (int)$p['id'] === $selectedProjectId;
    });
} else {
    $filteredProjects = $projects;
}

// Calculate Timeline Date Range (Past 3 months to Next 6 months)
$startTimestamp = strtotime('-2 months');
$endTimestamp   = strtotime('+6 months');

// Generate Months Headers
$months = [];
$current = strtotime(date('Y-m-01', $startTimestamp));
$last = strtotime(date('Y-m-01', $endTimestamp));

while ($current <= $last) {
    $months[] = [
        'key'   => date('Y-m', $current),
        'label' => date('M Y', $current),
        'ts'    => $current
    ];
    $current = strtotime('+1 month', $current);
}

$totalTimelineDays = max(1, ($endTimestamp - $startTimestamp) / 86400);
?>

<style>
/* Interactive Timeline Styles */
.timeline-card {
  background: var(--bg-card);
  backdrop-filter: var(--backdrop-blur);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.timeline-filter-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.timeline-grid-container {
  overflow-x: auto;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  background: rgba(15, 23, 42, 0.4);
}

.timeline-header-row {
  display: flex;
  border-bottom: 1px solid var(--border-color);
  background: rgba(15, 23, 42, 0.8);
}

.timeline-project-col {
  width: 260px;
  min-width: 260px;
  padding: 0.85rem 1rem;
  font-weight: 600;
  font-size: 0.85rem;
  color: var(--text-muted);
  border-right: 1px solid var(--border-color);
}

.timeline-months-col {
  display: flex;
  flex: 1;
  min-width: 700px;
}

.timeline-month-cell {
  flex: 1;
  text-align: center;
  padding: 0.85rem 0.5rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text-muted);
  border-right: 1px solid var(--border-color);
}

.timeline-month-cell:last-child {
  border-right: none;
}

.timeline-row {
  display: flex;
  border-bottom: 1px solid var(--border-color);
  align-items: center;
  transition: var(--transition);
}

.timeline-row:last-child {
  border-bottom: none;
}

.timeline-row:hover {
  background: rgba(255, 255, 255, 0.02);
}

.timeline-track-cell {
  flex: 1;
  min-width: 700px;
  position: relative;
  height: 52px;
  display: flex;
  align-items: center;
}

.timeline-bar {
  position: absolute;
  height: 24px;
  border-radius: 999px;
  background: var(--primary);
  display: flex;
  align-items: center;
  padding: 0 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: #ffffff;
  white-space: nowrap;
  box-shadow: 0 2px 8px rgba(0,0,0,0.3);
  transition: opacity 0.2s ease;
}

.timeline-bar:hover {
  opacity: 0.9;
}
</style>

<div class="timeline-card">
  <!-- Top Control Filter Bar -->
  <div class="timeline-filter-bar">
    <div>
      <h2 style="font-size: 1.4rem; font-weight: 700; color: #ffffff;">
        <i class="fa-solid fa-calendar-days"></i> Project Completion Timeline
      </h2>
      <p style="color: var(--text-muted); font-size: 0.85rem;">
        Visual schedule of start and target completion dates across active initiatives.
      </p>
    </div>

    <form method="GET" action="timeline.php" style="display: flex; gap: 0.75rem; align-items: center;">
      <label for="project_id" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Filter View:</label>
      <select name="project_id" id="project_id" class="form-select" onchange="this.form.submit()">
        <option value="0">All Projects (Overview)</option>
        <?php foreach ($projects as $p): ?>
          <option value="<?= $p['id'] ?>" <?= $selectedProjectId === (int)$p['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($p['title']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      
      <?php if ($selectedProjectId > 0): ?>
        <a href="timeline.php" class="btn-primary-action" style="padding: 0.45rem 0.85rem; font-size: 0.8rem; background: rgba(255,255,255,0.1);">
          Reset
        </a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Interactive Timeline Grid -->
  <div class="timeline-grid-container">
    <!-- Header Row -->
    <div class="timeline-header-row">
      <div class="timeline-project-col">Project Title</div>
      <div class="timeline-months-col">
        <?php foreach ($months as $m): ?>
          <div class="timeline-month-cell"><?= $m['label'] ?></div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Rows per Project -->
    <?php if (empty($filteredProjects)): ?>
      <div style="padding: 3rem; text-align: center; color: var(--text-muted);">
        No matching projects found for this timeline view.
      </div>
    <?php else: ?>
      <?php foreach ($filteredProjects as $project): ?>
        <?php
          // Calculate start & target offsets for bar positioning
          $pStart = strtotime($project['start_date']);
          $pEnd   = strtotime($project['target_completion_date']);

          // Clamp values to timeline view limits
          $pStartClamped = max($startTimestamp, $pStart);
          $pEndClamped   = min($endTimestamp, $pEnd);

          $startOffsetDays = max(0, ($pStartClamped - $startTimestamp) / 86400);
          $durationDays    = max(2, ($pEndClamped - $pStartClamped) / 86400);

          $leftPercent  = ($startOffsetDays / $totalTimelineDays) * 100;
          $widthPercent = ($durationDays / $totalTimelineDays) * 100;

          // Status Badge Class
          $statusClass = getStatusBadgeClass($project['status']);
        ?>
        <div class="timeline-row">
          <div class="timeline-project-col">
            <a href="project_detail.php?id=<?= $project['id'] ?>" style="color: var(--text-main); font-size: 0.9rem; font-weight: 600;">
              <?= htmlspecialchars($project['title']) ?>
            </a>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.2rem;">
              Lead: <?= htmlspecialchars($project['owner_name']) ?>
            </div>
          </div>

          <div class="timeline-track-cell">
            <div class="timeline-bar" 
                 style="left: <?= number_format($leftPercent, 2) ?>%; width: <?= number_format($widthPercent, 2) ?>%; background: <?= $project['status'] === 'Needs Attention' ? 'var(--accent-rose)' : 'var(--primary)' ?>;">
              <?= $project['progress_percent'] ?>% Completed &bull; Target: <?= date('M d', strtotime($project['target_completion_date'])) ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>