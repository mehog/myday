window.nasdanSwitchLocale = (locale) => {
    const nextUrl = new window.URL(window.location.href);
    nextUrl.searchParams.set('locale', locale);
    window.location.assign(nextUrl.toString());
};

document.addEventListener('alpine:init', () => {
    const media = window.matchMedia('(prefers-color-scheme: dark)');

    Alpine.store('dashboardAppearance', {
        mode: localStorage.getItem('dashboard_appearance') || 'system',
        resolved: 'light',
        init() {
            this.apply();
            media.addEventListener('change', () => {
                if (this.mode === 'system') {
                    this.apply();
                }
            });
        },
        set(mode) {
            this.mode = mode;
            localStorage.setItem('dashboard_appearance', mode);
            this.apply();
        },
        apply() {
            const dark = this.mode === 'dark' || (this.mode === 'system' && media.matches);
            this.resolved = dark ? 'dark' : 'light';
            document.documentElement.classList.toggle('dark', dark);
        },
    });
});
