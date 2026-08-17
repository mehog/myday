window.nasdanSwitchLocale = (locale) => {
    const nextUrl = new window.URL(window.location.href);
    nextUrl.searchParams.set('locale', locale);
    window.location.assign(nextUrl.toString());
};

document.addEventListener('alpine:init', () => {
    Alpine.data('countdown', (targetDate, labels = {}) => ({
        units: [
            { value: '00', label: labels.days ?? 'Days' },
            { value: '00', label: labels.hours ?? 'Hours' },
            { value: '00', label: labels.minutes ?? 'Minutes' },
            { value: '00', label: labels.seconds ?? 'Seconds' },
        ],
        interval: null,
        start() {
            this.tick();
            this.interval = setInterval(() => this.tick(), 1000);
        },
        tick() {
            const target = new Date(targetDate).getTime();
            let diff = Math.max(0, target - Date.now());

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            diff -= days * (1000 * 60 * 60 * 24);
            const hours = Math.floor(diff / (1000 * 60 * 60));
            diff -= hours * (1000 * 60 * 60);
            const minutes = Math.floor(diff / (1000 * 60));
            diff -= minutes * (1000 * 60);
            const seconds = Math.floor(diff / 1000);

            this.units = [
                { value: String(days).padStart(2, '0'), label: this.units[0].label },
                { value: String(hours).padStart(2, '0'), label: this.units[1].label },
                { value: String(minutes).padStart(2, '0'), label: this.units[2].label },
                { value: String(seconds).padStart(2, '0'), label: this.units[3].label },
            ];
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
