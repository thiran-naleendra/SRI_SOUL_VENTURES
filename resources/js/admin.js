import 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const confirmationModal = document.getElementById('confirmationModal');

    confirmationModal?.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        const form = confirmationModal.querySelector('form');

        confirmationModal.querySelector('[data-confirmation-message]').textContent =
            trigger?.dataset.confirmMessage || 'Are you sure you want to continue?';

        if (form && trigger?.dataset.confirmAction) {
            form.action = trigger.dataset.confirmAction;
        }
    });
});
