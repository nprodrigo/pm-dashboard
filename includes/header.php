<?php
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
  
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Custom Glassmorphic Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Sticky Header & Navbar -->
<header class="navbar">
  <a href="index.php" class="brand">
    <div class="brand-icon">
      <i class="fa-solid fa-chart-line">
    </div>
    <span>ProjectPulse Tracker</span>
  </a>

  <nav>
    <ul class="nav-links">
      <li class="nav-item">
        <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
          <i class="fa-solid fa-grid-2"></i> Overview
        </a>
      </li>
      <li class="nav-item">
        <a href="projects.php" class="<?= $currentPage === 'projects.php' ? 'active' : '' ?>">
          <i class="fa-solid fa-diagram-project"></i> All Projects
        </a>
      </li>
      <li class="nav-item">
        <a href="timeline.php" class="<?= $currentPage === 'timeline.php' ? 'active' : '' ?>">
          <i class="fa-solid fa-calendar-days"></i> "What Finishes When"
        </a>
      </li>
      <li class="nav-item">
        <a href="attention.php" class="<?= $currentPage === 'attention.php' ? 'active' : '' ?>">
          <i class="fa-solid fa-triangle-exclamation"></i> Needs Attention
          <?php if ($metrics['attention_needed'] > 0): ?>
            <span class="badge badge-danger" style="margin-left: 0.2rem; padding: 0.15rem 0.4rem; font-size: 0.7rem;"><?= $metrics['attention_needed'] ?></span>
          <?php endif; ?>
        </a>
      </li>
      <li class="nav-item">
  <a href="weekly_report.php" class="<?= $currentPage === 'weekly_report.php' ? 'active' : '' ?>">
    <i class="fa-solid fa-file-invoice"></i> Weekly Report
  </a>
</li>
<li class="nav-item">
  <a href="daily_log.php" class="<?= $currentPage === 'daily_log.php' ? 'active' : '' ?>">
    <i class="fa-solid fa-file-invoice"></i> Daily Report
  </a>
</li>
    </ul>
  </nav>

  <div>
    <button class="btn-primary-action" onclick="openModal('addProjectModal')">
      <i class="fa-solid fa-plus"></i> New Project
    </button>
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
