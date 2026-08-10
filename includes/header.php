<?php
session_start();

// Authentication Guard: Redirect unauthenticated users
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/functions.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$metrics = getDashboardMetrics();
$categories = getCategories();
$dbConnected = (getDBConnection() !== null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Project Executive Tracker | Progress & Pendings Dashboard</title>
  
  <!-- FontAwesome 6 Free Icons CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Custom Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Header & Navbar -->
<header class="navbar">
  <a href="index.php" class="brand">
    <div class="brand-icon">
      <i class="fa-solid fa-chart-line"></i>
    </div>
    <span>ProjectPulse Tracker</span>
  </a>

  <nav>
    <ul class="nav-links">
      <li class="nav-item">
        <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
          <i class="fa-solid fa-table-cells"></i> Overview
        </a>
      </li>
      <li class="nav-item">
        <a href="projects.php" class="<?= $currentPage === 'projects.php' ? 'active' : '' ?>">
          <i class="fa-solid fa-diagram-project"></i> All Projects
        </a>
      </li>
      <li class="nav-item">
        <a href="timeline.php" class="<?= $currentPage === 'timeline.php' ? 'active' : '' ?>">
          <i class="fa-solid fa-calendar-days"></i> Timeline
        </a>
      </li>
      <li class="nav-item">
        <a href="attention.php" class="<?= $currentPage === 'attention.php' ? 'active' : '' ?>">
          <i class="fa-solid fa-triangle-exclamation"></i> Needs Attention
        </a>
      </li>
      <li class="nav-item">
        <a href="weekly_report.php" class="<?= $currentPage === 'weekly_report.php' ? 'active' : '' ?>">
          <i class="fa-solid fa-file-invoice"></i> Weekly Report
        </a>
      </li>
      <li class="nav-item">
        <a href="daily_report.php" class="<?= $currentPage === 'daily_report.php' ? 'active' : '' ?>">
          <i class="fa-solid fa-pen-to-square"></i> Daily Report
        </a>
      </li>
      <li class="nav-item">
        <a href="team.php" class="<?= $currentPage === 'team.php' ? 'active' : '' ?>">
          <i class="fa-solid fa-users"></i> Team
        </a>
      </li>
    </ul>
  </nav>

  <div style="display: flex; align-items: center; gap: 1rem;">
    <span style="font-size: 0.85rem; color: var(--text-muted);">
      <i class="fa-solid fa-circle-user" style="color: var(--primary);"></i> <?= htmlspecialchars($_SESSION['full_name']) ?>
    </span>
    <a href="logout.php" class="btn-primary-action" style="background: rgba(244, 63, 94, 0.2); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.4); padding: 0.4rem 0.85rem;">
      <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
  </div>
</header>

<main class="main-container">

<?php if (!$dbConnected): ?>
  <!-- Database Connection Warning Banner -->
  <div class="db-warning-card">
    <div class="db-warning-icon">
      <i class="fa-solid fa-database"></i>
    </div>
    <div class="db-warning-content">
      <h3>Remote MySQL Database Connection Warning</h3>
      <p>Could not connect to MySQL using current configuration parameters in <code>config/database.php</code>.</p>
      <p>Please ensure your MySQL server is running, the host IP/Domain is reachable from this PHP server, and import <code>sql/schema.sql</code> into your remote MySQL database.</p>
      <div class="db-warning-code">
        Current Host: <?= htmlspecialchars(DB_HOST) ?>:<?= htmlspecialchars(DB_PORT) ?> | DB Name: <?= htmlspecialchars(DB_NAME) ?> | User: <?= htmlspecialchars(DB_USER) ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- Top Executive Summary Cards -->
<section class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon blue">
      <i class="fa-solid fa-folder-open"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $metrics['total_projects'] ?></div>
      <div class="stat-label">Total Projects</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon emerald">
      <i class="fa-solid fa-bars-progress"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $metrics['avg_progress'] ?>%</div>
      <div class="stat-label">Avg Active Completion</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon rose">
      <i class="fa-solid fa-triangle-exclamation"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $metrics['attention_needed'] ?></div>
      <div class="stat-label">Needs Attention / Blocked</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon amber">
      <i class="fa-solid fa-clock"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $metrics['finishing_soon'] ?></div>
      <div class="stat-label">Finishing Next 30 Days</div>
    </div>
  </div>
</section>