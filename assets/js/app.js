/**
 * Client-side Interactivity & AJAX API Handler
 */

document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // Modal Overlay Controls
    // ----------------------------------------------------
    const modals = document.querySelectorAll('.modal-overlay');
    
    window.openModal = (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // ----------------------------------------------------
    // Client-side Project Card Filtering (Instant Search)
    // ----------------------------------------------------
    const searchInput = document.getElementById('searchInput');
    const projectCards = document.querySelectorAll('.project-card');

    if (searchInput && projectCards.length > 0) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();

            projectCards.forEach(card => {
                const title = card.getAttribute('data-title') || '';
                const owner = card.getAttribute('data-owner') || '';
                const desc = card.getAttribute('data-desc') || '';

                if (title.includes(query) || owner.includes(query) || desc.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // ----------------------------------------------------
    // Quick Update Progress Slider Listener
    // ----------------------------------------------------
    const progressSlider = document.getElementById('progressSlider');
    const progressValueDisplay = document.getElementById('progressValueDisplay');

    if (progressSlider && progressValueDisplay) {
        progressSlider.addEventListener('input', (e) => {
            progressValueDisplay.textContent = e.target.value + '%';
        });
    }

    // ----------------------------------------------------
    // AJAX: Update Project Progress & Status
    // ----------------------------------------------------
    window.updateProjectProgress = async (projectId, newProgress, newStatus) => {
        try {
            const formData = new FormData();
            formData.append('action', 'update_progress');
            formData.append('project_id', projectId);
            formData.append('progress_percent', newProgress);
            if (newStatus) formData.append('status', newStatus);

            const res = await fetch('api.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error updating progress: ' + (data.message || 'Unknown error'));
            }
        } catch (err) {
            console.error('AJAX error:', err);
            alert('Failed to save progress update.');
        }
    };

    // ----------------------------------------------------
    // AJAX: Toggle Pending Status (Open <-> Resolved)
    // ----------------------------------------------------
    window.togglePendingStatus = async (pendingId, newStatus) => {
        try {
            const formData = new FormData();
            formData.append('action', 'toggle_pending');
            formData.append('pending_id', pendingId);
            formData.append('status', newStatus);

            const res = await fetch('api.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error updating item: ' + (data.message || 'Unknown error'));
            }
        } catch (err) {
            console.error('AJAX error:', err);
        }
    };
});
