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

    if (field) {
        field.focus({ preventScroll: true });
    }
});
