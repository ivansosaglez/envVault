// Livewire 3 ships and boots Alpine, so we only register what we need on top of it.

const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');

const savedMode = () => {
    try {
        return localStorage.getItem('theme') ?? 'system';
    } catch {
        return 'system';
    }
};

const applyTheme = (mode = savedMode()) => {
    const dark = mode === 'dark' || (mode === 'system' && prefersDark.matches);
    document.documentElement.classList.toggle('dark', dark);
};

// wire:navigate replaces the attributes of <html> with the ones from the server-rendered
// page, which knows nothing about the chosen theme. Put the class back right away
// (a mutation callback runs before the next paint, so there is no visible flash).
new MutationObserver(() => {
    const isDark = document.documentElement.classList.contains('dark');
    const shouldBeDark = savedMode() === 'dark' || (savedMode() === 'system' && prefersDark.matches);

    if (isDark !== shouldBeDark) {
        applyTheme();
    }
}).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

document.addEventListener('livewire:navigated', () => applyTheme());
prefersDark.addEventListener('change', () => applyTheme());

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        mode: savedMode(),

        set(mode) {
            this.mode = mode;
            try {
                localStorage.setItem('theme', mode);
            } catch {
                // Storage can be unavailable (private mode); the choice just won't persist.
            }
            applyTheme(mode);
        },
    });

    Alpine.data('clipboard', (text = '') => ({
        copied: false,

        async copy(value = text) {
            await navigator.clipboard.writeText(value);
            this.copied = true;
            setTimeout(() => (this.copied = false), 1800);
        },
    }));
});
