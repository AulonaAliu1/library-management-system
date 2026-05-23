document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form[data-ajax-request-status]');

    forms.forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(form);
            const row = form.closest('tr');
            const statusCell = row?.querySelector('[data-request-status]');
            const actionsCell = row?.querySelector('[data-request-actions]');

            try {
                const response = await fetch('../../public/api/request-status.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const result = await response.json();

                if (!result.success) {
                    alert(result.message || 'Request update failed.');
                    return;
                }

                if (statusCell && result.request) {
                    statusCell.textContent = result.request.statusLabel;
                    statusCell.className = result.request.statusClass;
                }

                if (actionsCell) {
                    actionsCell.innerHTML = '<span class="text-muted">Processed</span>';
                }

                if (result.counts) {
                    const total = document.getElementById('requests-total');
                    const pending = document.getElementById('requests-pending');
                    const approved = document.getElementById('requests-approved');
                    const rejected = document.getElementById('requests-rejected');

                    if (total) total.textContent = String(result.counts.total ?? 0);
                    if (pending) pending.textContent = String(result.counts.pending ?? 0);
                    if (approved) approved.textContent = String(result.counts.approved ?? 0);
                    if (rejected) rejected.textContent = String(result.counts.rejected ?? 0);
                }
            } catch (error) {
                alert('AJAX request failed. Please try again.');
            }
        });
    });
});
