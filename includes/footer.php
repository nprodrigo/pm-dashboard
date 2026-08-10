</main> <!-- End .main-container -->

<footer class="footer">
  <p>&copy; <?= date('Y') ?> ProjectPulse Executive Dashboard &bull; Built with PHP &amp; MySQL (Remote DB Ready)</p>
</footer>

<!-- Modal: Add New Project -->
<div class="modal-overlay" id="addProjectModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 style="font-size: 1.2rem; font-weight: 700;">Create New Project</h3>
      <button class="modal-close" onclick="closeModal('addProjectModal')">&times;</button>
    </div>
    
    <form action="api.php" method="POST">
      <input type="hidden" name="action" value="create_project">
      
      <div class="form-group">
        <label class="form-label">Project Title</label>
        <input type="text" name="title" class="form-input" style="width: 100%;" placeholder="e.g. ERP Cloud Sync Integration" required>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category_id" class="form-select" style="width: 100%;" required>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Business Unit (BU)</label>
          <select name="bu_id" class="form-select" style="width: 100%;">
            <option value="">Select BU...</option>
            <?php foreach (getBusinessUnits() as $bu): ?>
              <option value="<?= $bu['id'] ?>"><?= htmlspecialchars($bu['name']) ?> (<?= $bu['code'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Project Manager (PM)</label>
          <select name="manager_id" class="form-select" style="width: 100%;">
            <option value="">Select Project Manager...</option>
            <?php foreach (getTeamMembers() as $tm): ?>
              <option value="<?= $tm['id'] ?>"><?= htmlspecialchars($tm['full_name']) ?> (<?= htmlspecialchars($tm['role_title']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Target Completion Date</label>
          <input type="date" name="target_completion_date" class="form-input" style="width: 100%;" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
        </div>
      </div>

      <!-- Assign Project Team Members -->
      <div class="form-group">
        <label class="form-label">Assign Project Team Members</label>
        <div style="max-height: 120px; overflow-y: auto; background: var(--bg-input); padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
          <?php foreach (getTeamMembers() as $tm): ?>
            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-main); margin-bottom: 0.4rem; cursor: pointer;">
              <input type="checkbox" name="team_member_ids[]" value="<?= $tm['id'] ?>">
              <?= htmlspecialchars($tm['full_name']) ?> <span style="color: var(--text-dim); font-size: 0.78rem;">(<?= htmlspecialchars($tm['role_title']) ?>)</span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Project Description</label>
        <textarea name="description" class="form-input" style="width: 100%; height: 70px;" placeholder="Brief scope and objectives..."></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
        <button type="button" class="btn-primary-action" style="background: rgba(255,255,255,0.1);" onclick="closeModal('addProjectModal')">Cancel</button>
        <button type="submit" class="btn-primary-action">Save Project</button>
      </div>
    </form>

  </div>
</div>

<!-- Custom JS Application Logic -->
<script src="assets/js/app.js"></script>

</body>
</html>
