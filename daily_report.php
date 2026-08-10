<?php
require_once __DIR__ . '/includes/header.php';

$selectedDate = $_GET['date'] ?? date('Y-m-d');

// Handle Daily Log Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_daily_log'])) {
    $projectId = (int)$_POST['project_id'];
    $logText   = trim($_POST['log_text']);
    $isBlocked = isset($_POST['is_blocked']) ? 1 : 0;

    if ($projectId > 0 && !empty($logText)) {
        addDailyLog($projectId, $logText, $isBlocked);
        header("Location: daily_report.php?date=" . urlencode($selectedDate) . "&msg=success");
        exit;
    }
}

// Fetch all active projects for quick log entry
$allProjects = getProjects();

// Fetch today's logs for report display
$rawDailyLogs = getDailyReportData($selectedDate);

// Group daily logs by Business Unit -> Project
$groupedLogs = [];
foreach ($rawDailyLogs as $log) {
    $buName = $log['bu_name'] ?: 'General / Unassigned BU';
    $projectTitle = $log['project_title'];
    $groupedLogs[$buName][$projectTitle][] = $log;
}
?>

<style>
@media print {
  .navbar, .footer, .no-print, .log-entry-section { display: none !important; }
  body { background: #ffffff !important; color: #000000 !important; }
  .main-container { padding: 0 !important; }
  .report-card { border: 1px solid #ccc !important; box-shadow: none !important; background: #fff !important; color: #000 !important; }
  .report-card h3, .report-card h4 { color: #000 !important; }
}
</style>

<div class="main-container">

  <!-- Top Action & Filter Bar -->
  <div class="control-bar no-print" style="margin-bottom: 2rem;">
    <div>
      <h2 style="font-size: 1.4rem; font-weight: 700; color: #ffffff;">
        <i class="fa-solid fa-pen-to-square"></i> Daily Status & Report Workspace
      </h2>
      <p style="color: var(--text-muted); font-size: 0.85rem;">
        Log updates for today or view daily executive summaries[cite: 2, 4].
      </p>
    </div>

    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
      <!-- Date Filter -->
      <form method="GET" action="daily_report.php" style="display: flex; gap: 0.5rem; align-items: center;">
        <label for="date" style="font-size: 0.85rem; color: var(--text-muted);">Date:</label>
        <input type="date" id="date" name="date" class="form-input" value="<?= htmlspecialchars($selectedDate) ?>" onchange="this.form.submit()">
      </form>

      <!-- Print Button -->
      <button onclick="window.print()" class="btn-primary-action" style="background: var(--accent-emerald);">
        <i class="fa-solid fa-print"></i> Print / Save PDF
      </button>
    </div>
  </div>

  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
    <div class="attention-banner no-print" style="background: rgba(16, 185, 129, 0.15); border-left-color: var(--accent-emerald); color: #6ee7b7; margin-bottom: 1.5rem;">
      <i class="fa-solid fa-circle-check" style="color: var(--accent-emerald);"></i>
      <div>Daily update recorded successfully!</div>
    </div>
  <?php endif; ?>

  <!-- Section 1: Quick Log Entry Workspace (Hidden when printing) -->
  <div class="panel-card log-entry-section no-print" style="margin-bottom: 2rem;">
    <div class="panel-title">
      <span>Log Progress Update for Today (<?= date('M d, Y', strtotime($selectedDate)) ?>)</span>
    </div>

    <form method="POST" action="daily_report.php?date=<?= urlencode($selectedDate) ?>" style="display: flex; flex-direction: column; gap: 1rem;">
      <div style="display: grid; grid-template-columns: 1fr 2fr 120px 100px; gap: 1rem; align-items: center;">
        
        <!-- Project Dropdown -->
        <select name="project_id" class="form-select" required>
          <option value="">Select Project...</option>
          <?php foreach ($allProjects as $proj): ?>
            <option value="<?= $proj['id'] ?>"><?= htmlspecialchars($proj['title']) ?></option>
          <?php endforeach; ?>
        </select>

        <!-- Progress Text -->
        <input type="text" name="log_text" class="form-input" placeholder="Type today's progress, milestone update, or status..." required>

        <!-- Blocker Toggle -->
        <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; color: var(--accent-rose); cursor: pointer; white-space: nowrap;">
          <input type="checkbox" name="is_blocked" value="1"> Blocker
        </label>

        <!-- Submit Button -->
        <button type="submit" name="submit_daily_log" class="btn-primary-action" style="padding: 0.55rem 1rem;">
          Save
        </button>
      </div>
    </form>
  </div>

  <!-- Section 2: Generated Daily Executive Summary Report -->
  <div class="panel-card report-card">
    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1.5rem;">
      <div>
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #ffffff;">Daily Executive Progress Summary</h3>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">
          Date: <strong><?= date('F d, Y', strtotime($selectedDate)) ?></strong>
        </p>
      </div>
      <div style="text-align: right; font-size: 0.8rem; color: var(--text-dim);">
        Report Generated: <?= date('H:i T') ?>
      </div>
    </div>

    <?php if (empty($groupedLogs)): ?>
      <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
        <i class="fa-solid fa-file-circle-exclamation" style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--text-dim);"></i>
        <p>No daily progress updates logged for <?= date('M d, Y', strtotime($selectedDate)) ?>.</p>
        <p style="font-size: 0.82rem; margin-top: 0.4rem;">Use the input form above to submit today's updates.</p>
      </div>
    <?php else: ?>
      <?php foreach ($groupedLogs as $buName => $projects): ?>
        <div style="margin-bottom: 2rem;">
          <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--accent-cyan); border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem; margin-bottom: 1rem;">
            Business Unit: <?= htmlspecialchars($buName) ?>
          </h4>

          <?php foreach ($projects as $projectTitle => $logs): ?>
            <div style="margin-left: 1rem; margin-bottom: 1.25rem;">
              <h5 style="font-size: 0.95rem; font-weight: 600; color: #ffffff; margin-bottom: 0.5rem;">
                Project: <?= htmlspecialchars($projectTitle) ?>
              </h5>

              <ul style="list-style: none; padding-left: 0;">
                <?php foreach ($logs as $item): ?>
                  <li style="margin-bottom: 0.6rem; padding-left: 0.85rem; border-left: 3px solid <?= $item['is_blocked'] ? 'var(--accent-rose)' : 'var(--primary)' ?>;">
                    <div style="font-size: 0.78rem; color: var(--text-muted);">
                      Logged at <?= date('H:i', strtotime($item['created_at'])) ?> &bull; Manager: <?= htmlspecialchars($item['owner_name']) ?>
                    </div>
                    <div style="font-size: 0.9rem; color: var(--text-main); margin-top: 0.15rem;">
                      <?= htmlspecialchars($item['log_text']) ?>
                      <?php if ($item['is_blocked']): ?>
                        <span class="badge badge-danger" style="margin-left: 0.5rem;">BLOCKER REPORTED</span>
                      <?php endif; ?>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>