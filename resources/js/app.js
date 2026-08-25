import Alpine from 'alpinejs';

Alpine.data('conceptToggle', (slug, active) => ({
    active,
    pending: false,

    async toggle() {
        this.pending = true;

        const response = await fetch(`/concepts/${slug}/toggle`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        ({ active: this.active } = await response.json());
        this.pending = false;
    },
}));

window.Alpine = Alpine;
Alpine.start();

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
