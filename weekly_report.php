<?php
require_once __DIR__ . '/includes/header.php';

$rawLogs = getWeeklyReportData();

// Group logs by Business Unit and Project
$report = [];
foreach ($rawLogs as $log) {
    $buName = $log['bu_name'] ?: 'General Projects';
    $projectTitle = $log['project_title'];
    $report[$buName][$projectTitle][] = $log;
}
?>

<style>
    @media print {
        .navbar, .footer, .no-print { display: none !important; }
        body { background: #fff !important; color: #000 !important; }
        .report-card { border: 1px solid #ccc !important; box-shadow: none !important; }
    }
</style>

<div class="container" style="padding: 1.5rem 0;">
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2><i class="fa-solid fa-file-invoice"></i> Executive Weekly Status Report</h2>
            <p style="color: #94a3b8; font-size: 0.9rem;">Auto-aggregated logs for the past 7 days (<?= date('M d, Y') ?>)</p>
        </div>
        <button onclick="window.print()" class="btn-primary-action" style="background: #10b981;"><i class="fa-solid fa-print"></i> Print / Save to PDF</button>
    </div>

    <?php if (empty($report)): ?>
        <div class="stat-card" style="padding: 2rem; text-align: center; color: #94a3b8;">
            No daily logs recorded in the past 7 days. Add updates in the <a href="daily_log.php">Daily Workspace</a>.
        </div>
    <?php else: ?>
        <?php foreach ($report as $buName => $projects): ?>
            <div class="stat-card report-card" style="display: block; margin-bottom: 1.5rem; padding: 1.5rem;">
                <h3 style="border-bottom: 2px solid #3b82f6; padding-bottom: 0.5rem; margin-bottom: 1rem; color: #60a5fa;">
                    Business Unit: <?= htmlspecialchars($buName) ?>
                </h3>

                <?php foreach ($projects as $projectTitle => $logs): ?>
                    <div style="margin-left: 1rem; margin-bottom: 1.25rem;">
                        <h4 style="font-size: 1.05rem; margin-bottom: 0.5rem; color: #f8fafc;"><?= htmlspecialchars($projectTitle) ?></h4>
                        <ul style="list-style: none; padding-left: 0;">
                            <?php foreach ($logs as $item): ?>
                                <li style="margin-bottom: 0.5rem; padding-left: 1rem; border-left: 3px solid <?= $item['is_blocked'] ? '#f87171' : '#3b82f6' ?>;">
                                    <div style="font-size: 0.8rem; color: #94a3b8;">
                                        <?= date('M d, Y H:i', strtotime($item['created_at'])) ?> &bull; Manager: <?= htmlspecialchars($item['owner_name']) ?>
                                    </div>
                                    <div style="font-size: 0.95rem;">
                                        <?= htmlspecialchars($item['log_text']) ?>
                                        <?php if ($item['is_blocked']): ?>
                                            <span class="badge badge-danger" style="margin-left: 0.5rem;">BLOCKER</span>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>