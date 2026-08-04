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
          <label class="form-label">Priority</label>
          <select name="priority" class="form-select" style="width: 100%;">
            <option value="Medium">Medium</option>
            <option value="Low">Low</option>
            <option value="High">High</option>
            <option value="Critical">Critical</option>
          </select>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Project Owner / Manager</label>
          <input type="text" name="owner_name" class="form-input" style="width: 100%;" placeholder="e.g. Sarah Jenkins" required>
        </div>

        <div class="form-group">
          <label class="form-label">Target Completion Date</label>
          <input type="date" name="target_completion_date" class="form-input" style="width: 100%;" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Project Description</label>
        <textarea name="description" class="form-input" style="width: 100%; height: 80px;" placeholder="Brief scope and objectives..."></textarea>
      </div>

      <div class="form-group" style="background: rgba(244, 63, 94, 0.08); padding: 1rem; border-radius: var(--radius-md); border: 1px solid rgba(244, 63, 94, 0.2);">
        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; font-weight: 600; color: #fecdd3;">
          <input type="checkbox" name="needs_attention" value="1" onchange="document.getElementById('addAttentionReasonGroup').style.display = this.checked ? 'block' : 'none';">
          Flag as "Needs Attention" / Blocked immediately
        </label>
        <div id="addAttentionReasonGroup" style="display: none; margin-top: 0.75rem;">
          <input type="text" name="attention_reason" class="form-input" style="width: 100%;" placeholder="Describe the blocker or risk...">
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
        <button type="button" class="btn-primary-action" style="background: rgba(255,255,255,0.1); box-shadow: none;" onclick="closeModal('addProjectModal')">Cancel</button>
        <button type="submit" class="btn-primary-action">Save Project</button>
      </div>
    </form>
  </div>
</div>

<!-- Custom JS Application Logic -->
<script src="assets/js/app.js"></script>

</body>
</html>
