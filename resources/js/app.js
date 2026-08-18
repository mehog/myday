window.nasdanSwitchLocale = (locale) => {
    const nextUrl = new window.URL(window.location.href);
    nextUrl.searchParams.set('locale', locale);
    window.location.assign(nextUrl.toString());
};

document.addEventListener('alpine:init', () => {
    Alpine.data('countdown', (targetDate, labels = {}, unitCount = 4) => ({
        units: [
            { value: '00', label: labels.days ?? 'Days' },
            { value: '00', label: labels.hours ?? 'Hours' },
            { value: '00', label: labels.minutes ?? 'Minutes' },
            { value: '00', label: labels.seconds ?? 'Seconds' },
        ].slice(0, unitCount),
        interval: null,
        start() {
            this.tick();
            this.interval = setInterval(() => this.tick(), 1000);
        },
        tick() {
            const target = new Date(targetDate).getTime();
            let diff = Math.max(0, target - Date.now());

            const values = [
                Math.floor(diff / (1000 * 60 * 60 * 24)),
                Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
                Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
                Math.floor((diff % (1000 * 60)) / 1000),
            ];

            this.units = this.units.map((unit, index) => ({
                value: String(values[index] ?? 0).padStart(2, '0'),
                label: unit.label,
            }));
        },
    }));

    Alpine.data('invitationReturn', () => ({
        url: null,
        init() {
            try {
                const raw = localStorage.getItem('nd_invitation_url');
                if (raw) {
                    const data = JSON.parse(raw);
                    if (data.expires > Date.now()) {
                        this.url = data.url;
                    }
                }
            } catch (e) {}
        },
    }));

    Alpine.data('invitationPreviewModal', (defaultTitle = '') => ({
        open: false,
        url: '',
        title: defaultTitle,
        defaultTitle,
        show(detail = {}) {
            const nextUrl = detail?.url ?? '';

            if (! nextUrl) {
                return;
            }

            this.url = nextUrl;
            this.title = detail?.title || this.defaultTitle;
            this.open = true;
            document.documentElement.style.overflow = 'hidden';
        },
        hide() {
            this.open = false;
            this.url = '';
            this.title = this.defaultTitle;
            document.documentElement.style.overflow = '';
        },
    }));
});
