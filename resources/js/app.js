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
