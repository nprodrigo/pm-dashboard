<?php
require_once __DIR__ . '/includes/header.php';

$projects = getProjects();

// Group projects into Overdue, Current Month, Upcoming Months, and Completed
$timelineGroups = [
    'overdue' => ['title' => '🚨 Overdue / Past Target Date', 'projects' => [], 'color' => 'var(--accent-rose)'],
    'this_month' => ['title' => '📅 Finishing This Month (' . date('F Y') . ')', 'projects' => [], 'color' => 'var(--accent-cyan)'],
    'next_month' => ['title' => '🔮 Finishing Next Month (' . date('F Y', strtotime('+1 month')) . ')', 'projects' => [], 'color' => 'var(--primary)'],
    'later' => ['title' => '📌 Future Target Dates', 'projects' => [], 'color' => 'var(--text-muted)'],
    'completed' => ['title' => '✅ Recently Completed Projects', 'projects' => [], 'color' => 'var(--accent-emerald)'],
];

$thisMonthKey = date('Y-m');
$nextMonthKey = date('Y-m', strtotime('+1 month'));
$today = date('Y-m-d');

foreach ($projects as $project) {
    if ($project['status'] === 'Completed') {
        $timelineGroups['completed']['projects'][] = $project;
        continue;
    }

    $targetMonth = date('Y-m', strtotime($project['target_completion_date']));

    if ($project['target_completion_date'] < $today) {
        $timelineGroups['overdue']['projects'][] = $project;
    } elseif ($targetMonth === $thisMonthKey) {
        $timelineGroups['this_month']['projects'][] = $project;
    } elseif ($targetMonth === $nextMonthKey) {
        $timelineGroups['next_month']['projects'][] = $project;
    } else {
        $timelineGroups['later']['projects'][] = $project;
    }
}
?>

<div style="margin-bottom: 2rem;">
  <h2 style="font-size: 1.6rem; font-weight: 700;">"What Finishes When" Completion Roadmap</h2>
  <p style="color: var(--text-muted); font-size: 0.9rem;">Chronological timeline breakdown of target completion dates across all project categories.</p>
</div>

<div class="timeline-wrapper">
  <?php foreach ($timelineGroups as $key => $group): ?>
    <?php if (empty($group['projects'])) continue; ?>

    <div class="timeline-group">
      <div class="timeline-group-header" style="color: <?= $group['color'] ?>;">
        <span><?= $group['title'] ?></span>
        <span class="badge" style="background: rgba(255,255,255,0.06); font-size: 0.8rem;"><?= count($group['projects']) ?> projects</span>
      </div>

      <?php foreach ($group['projects'] as $p): ?>
        <?php 
          $daysInfo = getRemainingDaysInfo($p['target_completion_date'], $p['status']);
          $statusClass = getStatusBadgeClass($p['status']);
        ?>
        <div class="timeline-item">
          <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
            <div style="min-width: 120px; font-weight: 700; font-size: 0.9rem; color: var(--text-main);">
              <?= date('M j, Y', strtotime($p['target_completion_date'])) ?>
            </div>

            <div style="flex: 1;">
              <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.2rem;">
                <span class="category-dot" style="background-color: <?= $p['category_color'] ?>;"></span>
                <a href="project_detail.php?id=<?= $p['id'] ?>" style="font-weight: 700; font-size: 1rem; color: var(--text-main);">
                  <?= htmlspecialchars($p['title']) ?>
                </a>
                <span class="badge <?= $statusClass ?>" style="font-size: 0.72rem;"><?= $p['status'] ?></span>
              </div>
              <div style="font-size: 0.82rem; color: var(--text-muted);">
                Category: <strong><?= htmlspecialchars($p['category_name']) ?></strong> &bull; Lead: <strong><?= htmlspecialchars($p['owner_name']) ?></strong>
              </div>
            </div>
          </div>

          <!-- Progress & Remaining -->
          <div style="display: flex; align-items: center; gap: 1.5rem; text-align: right;">
            <div style="width: 140px;">
              <div style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.2rem;">Progress: <?= $p['progress_percent'] ?>%</div>
              <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: <?= $p['progress_percent'] ?>%;"></div>
              </div>
            </div>

            <span class="badge <?= $daysInfo['class'] ?>" style="padding: 0.4rem 0.8rem; min-width: 110px; justify-content: center;">
              <?= $daysInfo['text'] ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
