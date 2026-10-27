import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Filter chips point at their field so a click lands on the input that owns the value.
document.addEventListener('click', (event) => {
    const link = event.target.closest('[data-focus]');

    if (!link) {
        return;
    }

    const field = document.querySelector(link.dataset.focus);

    if (!field) {
        return;
    }

    event.preventDefault();

    // The panel may be collapsed, and an anchor cannot scroll to a hidden field.
    field.closest('details')?.setAttribute('open', '');
    field.focus({ preventScroll: true });
    field.scrollIntoView({ block: 'center' });
});
