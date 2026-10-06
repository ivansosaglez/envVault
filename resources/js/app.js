// Livewire 3 ships and boots Alpine, so we only register what we need on top of it.

document.addEventListener('alpine:init', () => {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');

    const read = () => {
        try {
            return localStorage.getItem('theme') ?? 'system';
        } catch {
            return 'system';
        }
    };

    const apply = (mode) => {
        const dark = mode === 'dark' || (mode === 'system' && prefersDark.matches);
        document.documentElement.classList.toggle('dark', dark);
    };

    Alpine.store('theme', {
        mode: read(),

        set(mode) {
            this.mode = mode;
            try {
                localStorage.setItem('theme', mode);
            } catch {
                // Storage can be unavailable (private mode); the choice just won't persist.
            }
            apply(mode);
        },
    });

    prefersDark.addEventListener('change', () => apply(Alpine.store('theme').mode));

    Alpine.data('clipboard', (text = '') => ({
        copied: false,

        async copy(value = text) {
            await navigator.clipboard.writeText(value);
            this.copied = true;
            setTimeout(() => (this.copied = false), 1800);
        },
    }));
});
