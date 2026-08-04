<?php
require_once __DIR__ . '/includes/header.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_daily_log'])) {
    $projectId = (int)$_POST['project_id'];
    $logText   = trim($_POST['log_text']);
    $isBlocked = isset($_POST['is_blocked']) ? 1 : 0;

    if (!empty($logText)) {
        addDailyLog($projectId, $logText, $isBlocked);
        echo "<script>window.location.href='daily_log.php?success=1';</script>";
        exit;
    }
}

$projects = getProjects();
?>

<div class="container" style="padding: 1.5rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2><i class="fa-solid fa-pen-to-square"></i> Daily Progress Workspace</h2>
        <a href="weekly_report.php" class="btn-primary-action"><i class="fa-solid fa-file-export"></i> View Weekly Report</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem;">
            Update logged successfully!
        </div>
    <?php endif; ?>

    <div style="display: grid; gap: 1.5rem;">
        <?php foreach ($projects as $project): ?>
            <div class="stat-card" style="display: block; padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                    <h3 style="margin: 0; font-size: 1.1rem;"><?= htmlspecialchars($project['title']) ?></h3>
                    <span class="badge" style="background-color: <?= $project['category_color'] ?>; color: #fff;">
                        <?= htmlspecialchars($project['category_name']) ?>
                    </span>
                </div>
                <p style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 1rem;">
                    Owner: <strong><?= htmlspecialchars($project['owner_name']) ?></strong> | 
                    Status: <span class="badge <?= getStatusBadgeClass($project['status']) ?>"><?= $project['status'] ?></span>
                </p>

                <!-- Daily Log Input Form -->
                <form method="POST" action="daily_log.php" style="display: flex; gap: 0.75rem; align-items: center;">
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                    <input type="text" name="log_text" class="form-input" style="flex: 1;" placeholder="Add today's update or comment..." required>
                    
                    <label style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.85rem; color: #f87171; cursor: pointer; white-scope: nowrap;">
                        <input type="checkbox" name="is_blocked" value="1"> Blocker
                    </label>

                    <button type="submit" name="submit_daily_log" class="btn-primary-action" style="padding: 0.5rem 1rem;">Save Log</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>