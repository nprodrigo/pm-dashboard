<?php
require_once __DIR__ . '/includes/header.php';

// Retrieve filter parameters
$selectedCategory = $_GET['category'] ?? '';
$selectedStatus   = $_GET['status'] ?? '';
$selectedPriority = $_GET['priority'] ?? '';
$searchQuery      = $_GET['search'] ?? '';

$projects = getProjects([
    'category' => $selectedCategory,
    'status'   => $selectedStatus,
    'priority' => $selectedPriority,
    'search'   => $searchQuery
]);
?>

<!-- Control Bar: Category Pills, Filters & Search -->
<div class="category-pills">
  <a href="index.php" class="pill-btn <?= empty($selectedCategory) ? 'active' : '' ?>">
    All Categories
  </a>
  <?php foreach ($categories as $cat): ?>
    <a href="index.php?category=<?= urlencode($cat['slug']) ?>" 
       class="pill-btn <?= $selectedCategory === $cat['slug'] ? 'active' : '' ?>">
      <span class="category-dot" style="background-color: <?= $cat['color_code'] ?>;"></span>
      <?= htmlspecialchars($cat['name']) ?>
    </a>
  <?php endforeach; ?>
</div>

<form action="index.php" method="GET" class="control-bar">
  <?php if (!empty($selectedCategory)): ?>
    <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory) ?>">
  <?php endif; ?>

  <div class="filter-group">
    <select name="status" class="form-select" onchange="this.form.submit()">
      <option value="">All Statuses</option>
      <option value="In Progress" <?= $selectedStatus === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
      <option value="Needs Attention" <?= $selectedStatus === 'Needs Attention' ? 'selected' : '' ?>>Needs Attention / Blocked</option>
      <option value="Under Review" <?= $selectedStatus === 'Under Review' ? 'selected' : '' ?>>Under Review</option>
      <option value="Planning" <?= $selectedStatus === 'Planning' ? 'selected' : '' ?>>Planning</option>
      <option value="On Hold" <?= $selectedStatus === 'On Hold' ? 'selected' : '' ?>>On Hold</option>
      <option value="Completed" <?= $selectedStatus === 'Completed' ? 'selected' : '' ?>>Completed</option>
    </select>

    <select name="priority" class="form-select" onchange="this.form.submit()">
      <option value="">All Priorities</option>
      <option value="Critical" <?= $selectedPriority === 'Critical' ? 'selected' : '' ?>>Critical</option>
      <option value="High" <?= $selectedPriority === 'High' ? 'selected' : '' ?>>High</option>
      <option value="Medium" <?= $selectedPriority === 'Medium' ? 'selected' : '' ?>>Medium</option>
      <option value="Low" <?= $selectedPriority === 'Low' ? 'selected' : '' ?>>Low</option>
    </select>
  </div>

  <div class="search-input-wrapper">
    <i class="fa-solid fa-magnifying-glass search-icon"></i>
    <input type="text" id="searchInput" name="search" class="form-input" placeholder="Search projects or owners..." value="<?= htmlspecialchars($searchQuery) ?>">
  </div>
</form>

<!-- Projects Cards Grid -->
<?php if (empty($projects)): ?>
  <div class="panel-card" style="text-align: center; padding: 4rem 2rem;">
    <i class="fa-solid fa-folder-open" style="font-size: 3rem; color: var(--text-dim); margin-bottom: 1rem;"></i>
    <h3>No Projects Found</h3>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">Try adjusting your filters or click "New Project" to add one.</p>
  </div>
<?php else: ?>
  <div class="projects-grid">
    <?php foreach ($projects as $project): ?>
      <?php 
        $daysInfo = getRemainingDaysInfo($project['target_completion_date'], $project['status']);
        $statusClass = getStatusBadgeClass($project['status']);
        $priorityClass = getPriorityBadgeClass($project['priority']);
        $ownerInitial = strtoupper(substr($project['owner_name'], 0, 1));
      ?>
      <div class="project-card <?= ($project['needs_attention'] || $project['status'] === 'Needs Attention') ? 'attention-border' : '' ?>"
           data-title="<?= strtolower(htmlspecialchars($project['title'])) ?>"
           data-owner="<?= strtolower(htmlspecialchars($project['owner_name'])) ?>"
           data-desc="<?= strtolower(htmlspecialchars($project['description'] ?? '')) ?>">

        <div>
          <!-- Card Header: Category & Status -->
          <div class="project-card-header">
            <span class="category-tag" style="border-left: 3px solid <?= $project['category_color'] ?>;">
              <span class="category-dot" style="background-color: <?= $project['category_color'] ?>;"></span>
              <?= htmlspecialchars($project['category_name']) ?>
            </span>
            
            <div style="display: flex; gap: 0.4rem;">
              <span class="badge <?= $priorityClass ?>"><?= $project['priority'] ?></span>
              <span class="badge <?= $statusClass ?>"><?= $project['status'] ?></span>
            </div>
          </div>

          <!-- Title -->
          <h3 class="project-title">
            <a href="project_detail.php?id=<?= $project['id'] ?>">
              <?= htmlspecialchars($project['title']) ?>
            </a>
          </h3>

          <p class="project-desc"><?= htmlspecialchars($project['description'] ?: 'No description provided.') ?></p>

          <!-- Blocker Banner if Attention Needed -->
          <?php if ($project['needs_attention'] && !empty($project['attention_reason'])): ?>
            <div class="attention-banner">
              <i class="fa-solid fa-triangle-exclamation"></i>
              <div>
                <strong>Blocker / Attention Reason:</strong><br>
                <?= htmlspecialchars($project['attention_reason']) ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Progress Section -->
          <div class="progress-section">
            <div class="progress-header">
              <span style="color: var(--text-muted);">Overall Progress</span>
              <span style="color: var(--text-main); font-weight: 700;"><?= $project['progress_percent'] ?>%</span>
            </div>
            <div class="progress-bar-bg">
              <div class="progress-bar-fill <?= $project['progress_percent'] == 100 ? 'complete' : ($project['needs_attention'] ? 'critical' : '') ?>" 
                   style="width: <?= $project['progress_percent'] ?>%;"></div>
            </div>
          </div>
        </div>

        <!-- Meta Footer -->
        <div class="project-card-footer">
          <div class="owner-info">
            <div class="owner-avatar"><?= $ownerInitial ?></div>
            <span><?= htmlspecialchars($project['owner_name']) ?></span>
          </div>

          <div style="display: flex; gap: 0.75rem; align-items: center;">
            <?php if ($project['open_pendings_count'] > 0): ?>
              <span class="badge badge-warning" title="Open Pendings">
                <i class="fa-solid fa-circle-exclamation"></i> <?= $project['open_pendings_count'] ?> pending
              </span>
            <?php endif; ?>

            <span class="badge <?= $daysInfo['class'] ?>">
              <i class="fa-regular fa-clock"></i> <?= $daysInfo['text'] ?>
            </span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
