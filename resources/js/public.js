import { Modal } from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const errorModal = document.querySelector('[data-open-on-errors]');

    if (errorModal) {
        Modal.getOrCreateInstance(errorModal).show();
    }
});

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-share-button]');

    if (!button) {
        return;
    }

    const shareData = { title: button.dataset.shareTitle, url: button.dataset.shareUrl };

    if (navigator.share) {
        await navigator.share(shareData);
        return;
    }

    await navigator.clipboard.writeText(shareData.url);
    const originalText = button.textContent;
    button.textContent = 'Link copied';
    window.setTimeout(() => { button.textContent = originalText; }, 2000);
});
