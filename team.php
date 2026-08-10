<?php
require_once __DIR__ . '/includes/header.php';

// Handle Team Member Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_team_member') {
    $fullName  = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $roleTitle = trim($_POST['role_title'] ?? 'Team Member');

    if ($fullName && $email) {
        $db = getDBConnection();
        $stmt = $db->prepare("INSERT INTO team_members (full_name, email, role_title) VALUES (:name, :email, :role)");
        $stmt->execute(['name' => $fullName, 'email' => $email, 'role' => $roleTitle]);
        header("Location: team.php?msg=added");
        exit;
    }
}

$teamMembers = getTeamMembers();
$db = getDBConnection();
?>

<div class="main-container">
  <div class="control-bar" style="margin-bottom: 2rem;">
    <div>
      <h2 style="font-size: 1.4rem; font-weight: 700; color: #ffffff;">
        <i class="fa-solid fa-users"></i> Team Directory & Workload Center
      </h2>
      <p style="color: var(--text-muted); font-size: 0.85rem;">
        Manage global team resources, role titles, and cross-project allocations[cite: 2].
      </p>
    </div>

    <button class="btn-primary-action" onclick="openModal('addTeamMemberModal')">
      <i class="fa-solid fa-user-plus"></i> Add Team Member
    </button>
  </div>

  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
    <div class="attention-banner" style="background: rgba(16, 185, 129, 0.15); border-left-color: var(--accent-emerald); color: #6ee7b7; margin-bottom: 1.5rem;">
      <i class="fa-solid fa-circle-check" style="color: var(--accent-emerald);"></i>
      <div>Team member added successfully!</div>
    </div>
  <?php endif; ?>

  <!-- Team Directory Cards Grid -->
  <div class="projects-grid">
    <?php foreach ($teamMembers as $member): ?>
      <?php
        // Fetch assigned projects count
        $stmtP = $db->prepare("SELECT COUNT(*) FROM project_teams WHERE member_id = :mid");
        $stmtP->execute(['mid' => $member['id']]);
        $projectCount = $stmtP->fetchColumn();

        // Fetch managed projects count
        $stmtM = $db->prepare("SELECT COUNT(*) FROM projects WHERE manager_id = :mid");
        $stmtM->execute(['mid' => $member['id']]);
        $managedCount = $stmtM->fetchColumn();

        // Fetch open tasks count
        $stmtT = $db->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = :mid AND status != 'Completed'");
        $stmtT->execute(['mid' => $member['id']]);
        $openTaskCount = $stmtT->fetchColumn();

        $initial = strtoupper(substr($member['full_name'], 0, 1));
      ?>
      <div class="project-card">
        <div>
          <div style="display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1rem;">
            <div class="owner-avatar" style="width: 42px; height: 42px; font-size: 1.1rem; flex-shrink: 0;">
              <?= $initial ?>
            </div>
            <div>
              <h3 style="font-size: 1.1rem; font-weight: 700; color: #ffffff; margin: 0;"><?= htmlspecialchars($member['full_name']) ?></h3>
              <div style="font-size: 0.82rem; color: var(--text-muted);"><?= htmlspecialchars($member['role_title']) ?></div>
            </div>
          </div>

          <div style="font-size: 0.82rem; color: var(--text-dim); margin-bottom: 1rem;">
            <i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($member['email']) ?>
          </div>

          <!-- Workload Stats Pill Row -->
          <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
            <span class="badge badge-info" title="Managing Projects">
              <i class="fa-solid fa-user-gear"></i> PM: <?= $managedCount ?> Projects
            </span>
            <span class="badge badge-success" title="Team Member On">
              <i class="fa-solid fa-diagram-project"></i> Team: <?= $projectCount ?> Projects
            </span>
            <span class="badge badge-warning" title="Active Tasks">
              <i class="fa-solid fa-list-check"></i> Tasks: <?= $openTaskCount ?> Open
            </span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Modal: Add Team Member -->
<div class="modal-overlay" id="addTeamMemberModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 style="font-size: 1.2rem; font-weight: 700; color: #fff;">Add New Team Member</h3>
      <button class="modal-close" onclick="closeModal('addTeamMemberModal')">&times;</button>
    </div>

    <form action="team.php" method="POST">
      <input type="hidden" name="action" value="create_team_member">

      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="full_name" class="form-input" style="width: 100%;" placeholder="e.g. John Doe" required>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-input" style="width: 100%;" placeholder="john.d@company.com" required>
        </div>

        <div class="form-group">
          <label class="form-label">Role Title</label>
          <input type="text" name="role_title" class="form-input" style="width: 100%;" placeholder="e.g. Senior Developer">
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
        <button type="button" class="btn-primary-action" style="background: rgba(255,255,255,0.1);" onclick="closeModal('addTeamMemberModal')">Cancel</button>
        <button type="submit" class="btn-primary-action">Save Member</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>