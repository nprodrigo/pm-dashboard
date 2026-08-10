<?php
require_once __DIR__ . '/includes/header.php';

$projects = getProjects();
$selectedProjectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
$viewMode = isset($_GET['mode']) && $_GET['mode'] === 'detailed' ? 'detailed' : 'summary';

// Filter single project if selected
if ($selectedProjectId > 0) {
    $filteredProjects = array_filter($projects, function($p) use ($selectedProjectId) {
        return (int)$p['id'] === $selectedProjectId;
    });
} else {
    $filteredProjects = $projects;
}

// Calculate Timeline Date Range: 2 weeks ago to 12 weeks ahead (Monday-aligned)
$mondayThisWeek = strtotime('monday this week');
$startTimestamp = strtotime('-2 weeks', $mondayThisWeek);
$endTimestamp   = strtotime('+12 weeks', $mondayThisWeek);

// Generate Weekly Columns
$weeks = [];
$currentWeek = $startTimestamp;

while ($currentWeek < $endTimestamp) {
    $weeks[] = [
        'week_num' => date('W', $currentWeek),
        'label'    => 'W' . date('W', $currentWeek),
        'sublabel' => date('M d', $currentWeek),
        'start_ts' => $currentWeek,
        'end_ts'   => strtotime('+6 days 23:59:59', $currentWeek)
    ];
    $currentWeek = strtotime('+1 week', $currentWeek);
}

$totalTimelineDays = max(1, ($endTimestamp - $startTimestamp) / 86400);
?>

<style>
/* Weekly Gantt Timeline Styles */
.timeline-card {
  background: var(--bg-card);
  backdrop-filter: var(--backdrop-blur);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.timeline-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.mode-toggle-group {
  display: flex;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  padding: 3px;
}

.mode-toggle-btn {
  padding: 0.45rem 0.9rem;
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--text-muted);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  gap: 0.4rem;
  transition: var(--transition);
}

.mode-toggle-btn.active {
  background: var(--primary);
  color: #ffffff;
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
  background: rgba(15, 23, 42, 0.85);
  position: sticky;
  top: 0;
  z-index: 10;
}

.timeline-project-col {
  width: 280px;
  min-width: 280px;
  padding: 0.85rem 1rem;
  font-weight: 600;
  font-size: 0.85rem;
  color: var(--text-muted);
  border-right: 1px solid var(--border-color);
  background: rgba(15, 23, 42, 0.95);
}

.timeline-weeks-col {
  display: flex;
  flex: 1;
  min-width: 840px;
}

.timeline-week-cell {
  flex: 1;
  text-align: center;
  padding: 0.6rem 0.25rem;
  border-right: 1px solid var(--border-color);
}

.timeline-week-cell .week-title {
  font-size: 0.8rem;
  font-weight: 700;
  color: #ffffff;
}

.timeline-week-cell .week-date {
  font-size: 0.72rem;
  color: var(--text-dim);
}

.timeline-week-cell.current-week {
  background: rgba(99, 102, 241, 0.12);
}

.timeline-row {
  display: flex;
  border-bottom: 1px solid var(--border-color);
  align-items: center;
}

.timeline-row.project-master-row {
  background: rgba(30, 41, 59, 0.5);
}

.timeline-row.sub-item-row {
  background: rgba(15, 23, 42, 0.25);
  font-size: 0.82rem;
}

.timeline-track-cell {
  flex: 1;
  min-width: 840px;
  position: relative;
  height: 48px;
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
  overflow: hidden;
  text-overflow: ellipsis;
}

.timeline-marker {
  position: absolute;
  height: 20px;
  padding: 0 0.5rem;
  border-radius: 4px;
  font-size: 0.72rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  white-space: nowrap;
}

.marker-milestone {
  background: rgba(6, 182, 212, 0.2);
  color: #67e8f9;
  border: 1px solid rgba(6, 182, 212, 0.4);
}

.marker-milestone.completed {
  background: rgba(16, 185, 129, 0.2);
  color: #6ee7b7;
  border-color: rgba(16, 185, 129, 0.4);
}

.marker-pending {
  background: rgba(244, 63, 94, 0.2);
  color: #fca5a5;
  border: 1px solid rgba(244, 63, 94, 0.4);
}
</style>

<div class="timeline-card">
  <!-- Control Header Bar -->
  <div class="timeline-controls">
    <div>
      <h2 style="font-size: 1.4rem; font-weight: 700; color: #ffffff;">
        <i class="fa-solid fa-calendar-days"></i> Project Completion Timeline
      </h2>
      <p style="color: var(--text-muted); font-size: 0.85rem;">
        Weekly roadmap with week number breakdown and single/detailed view toggle[cite: 14].
      </p>
    </div>

    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
      <!-- Mode Toggle: Summary vs Detailed -->
      <div class="mode-toggle-group">
        <a href="timeline.php?project_id=<?= $selectedProjectId ?>&mode=summary" 
           class="mode-toggle-btn <?= $viewMode === 'summary' ? 'active' : '' ?>">
          <i class="fa-solid fa-bars"></i> Summary Format
        </a>
        <a href="timeline.php?project_id=<?= $selectedProjectId ?>&mode=detailed" 
           class="mode-toggle-btn <?= $viewMode === 'detailed' ? 'active' : '' ?>">
          <i class="fa-solid fa-list-check"></i> Detailed Format
        </a>
      </div>

      <!-- Single Project Filter -->
      <form method="GET" action="timeline.php" style="display: flex; gap: 0.5rem; align-items: center;">
        <input type="hidden" name="mode" value="<?= $viewMode ?>">
        <select name="project_id" class="form-select" onchange="this.form.submit()">
          <option value="0">All Projects Overview</option>
          <?php foreach ($projects as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $selectedProjectId === (int)$p['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>

  <!-- Gantt Grid -->
  <div class="timeline-grid-container">
    <!-- Header Row: Week Numbers -->
    <div class="timeline-header-row">
      <div class="timeline-project-col">
        <?= $viewMode === 'detailed' ? 'Project / Deliverable Breakdown' : 'Project Title' ?>
      </div>
      <div class="timeline-weeks-col">
        <?php foreach ($weeks as $w): ?>
          <?php $isCurrent = (date('W') === $w['week_num']); ?>
          <div class="timeline-week-cell <?= $isCurrent ? 'current-week' : '' ?>">
            <div class="week-title"><?= $w['label'] ?></div>
            <div class="week-date"><?= $w['sublabel'] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Rows Rendering -->
    <?php if (empty($filteredProjects)): ?>
      <div style="padding: 3rem; text-align: center; color: var(--text-muted);">
        No matching projects found for this view[cite: 13, 14].
      </div>
    <?php else: ?>
      <?php foreach ($filteredProjects as $project): ?>
        <?php
          // Main Project Bar Coordinates
          $pStart = strtotime($project['start_date']);
          $pEnd   = strtotime($project['target_completion_date']);

          $pStartClamped = max($startTimestamp, $pStart);
          $pEndClamped   = min($endTimestamp, $pEnd);

          $startOffsetDays = max(0, ($pStartClamped - $startTimestamp) / 86400);
          $durationDays    = max(2, ($pEndClamped - $pStartClamped) / 86400);

          $leftPercent  = ($startOffsetDays / $totalTimelineDays) * 100;
          $widthPercent = ($durationDays / $totalTimelineDays) * 100;
        ?>

        <!-- Project Main Row -->
        <div class="timeline-row project-master-row">
          <div class="timeline-project-col">
            <a href="project_detail.php?id=<?= $project['id'] ?>" style="color: #ffffff; font-weight: 600;">
              <?= htmlspecialchars($project['title']) ?>
            </a>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.2rem;">
              Owner: <?= htmlspecialchars($project['owner_name']) ?> &bull; <?= $project['status'] ?>[cite: 13, 14]
            </div>
          </div>

          <div class="timeline-track-cell">
            <div class="timeline-bar" 
                 style="left: <?= number_format($leftPercent, 2) ?>%; width: <?= number_format($widthPercent, 2) ?>%; background: <?= $project['status'] === 'Needs Attention' ? 'var(--accent-rose)' : 'var(--primary)' ?>;">
              <?= $project['progress_percent'] ?>% Completed &bull; Due: <?= date('M d', strtotime($project['target_completion_date'])) ?>[cite: 13, 14]
            </div>
          </div>
        </div>

        <!-- Detailed Mode Rows: Sub-Items (Milestones & Pendings) -->
        <?php if ($viewMode === 'detailed'): ?>
          <?php 
            $milestones = getMilestones($project['id']);
            $pendings   = getPendings($project['id'], true);
          ?>

          <!-- Milestones Rows -->
          <?php foreach ($milestones as $m): ?>
            <?php
              $mTs = strtotime($m['due_date']);
              if ($mTs < $startTimestamp || $mTs > $endTimestamp) continue;

              $mOffset = max(0, ($mTs - $startTimestamp) / 86400);
              $mLeft = ($mOffset / $totalTimelineDays) * 100;
              $isComp = ($m['status'] === 'Completed');
            ?>
            <div class="timeline-row sub-item-row">
              <div class="timeline-project-col" style="padding-left: 2rem; color: var(--text-muted);">
                <i class="fa-regular <?= $isComp ? 'fa-circle-check' : 'fa-flag' ?>" style="color: <?= $isComp ? 'var(--accent-emerald)' : 'var(--accent-cyan)' ?>;"></i>
                <?= htmlspecialchars($m['title']) ?>[cite: 3]
              </div>
              <div class="timeline-track-cell">
                <div class="timeline-marker <?= $isComp ? 'marker-milestone completed' : 'marker-milestone' ?>" style="left: <?= number_format($mLeft, 2) ?>%;">
                  <i class="fa-solid fa-flag"></i> Due: <?= date('M d', $mTs) ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

          <!-- Pending Action Items Rows -->
          <?php foreach ($pendings as $pend): ?>
            <?php
              if (empty($pend['due_date'])) continue;
              $pTs = strtotime($pend['due_date']);
              if ($pTs < $startTimestamp || $pTs > $endTimestamp) continue;

              $pOffset = max(0, ($pTs - $startTimestamp) / 86400);
              $pLeft = ($pOffset / $totalTimelineDays) * 100;
            ?>
            <div class="timeline-row sub-item-row">
              <div class="timeline-project-col" style="padding-left: 2rem; color: #fca5a5;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($pend['title']) ?>[cite: 3]
              </div>
              <div class="timeline-track-cell">
                <div class="timeline-marker marker-pending" style="left: <?= number_format($pLeft, 2) ?>%;">
                  <i class="fa-solid fa-clock"></i> Blocker: <?= date('M d', $pTs) ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>