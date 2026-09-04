window.nasdanSwitchLocale = (locale) => {
    const nextUrl = new window.URL(window.location.href);
    nextUrl.searchParams.set('locale', locale);
    window.location.assign(nextUrl.toString());
};

document.addEventListener('alpine:init', () => {
    const media = window.matchMedia('(prefers-color-scheme: dark)');
    const desktopMq = window.matchMedia('(min-width: 1024px)');

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

    Alpine.data('dashboardSheet', () => ({
        dragging: false,
        startY: 0,
        lastY: 0,
        lastTs: 0,
        dy: 0,
        velocity: 0,
        pointerId: null,

        isDesktop() {
            return desktopMq.matches;
        },

        onPointerDown(event) {
            if (this.isDesktop()) {
                return;
            }

            if (event.pointerType === 'mouse' && event.button !== 0) {
                return;
            }

            if (event.target.closest('button, a, input, select, textarea, label')) {
                return;
            }

            if (! event.target.closest('[data-sheet-drag]')) {
                return;
            }

            this.dragging = true;
            this.pointerId = event.pointerId;
            this.startY = event.clientY;
            this.lastY = event.clientY;
            this.lastTs = event.timeStamp;
            this.dy = 0;
            this.velocity = 0;

            this.$refs.panel?.classList.add('is-dragging');
            this.$refs.overlay?.classList.add('is-dragging');

            try {
                event.currentTarget.setPointerCapture(event.pointerId);
            } catch {
                // Ignore browsers that reject capture on this target.
            }

            event.preventDefault();
        },

        onPointerMove(event) {
            if (! this.dragging || event.pointerId !== this.pointerId) {
                return;
            }

            const now = event.timeStamp;
            const y = event.clientY;
            const elapsed = Math.max(1, now - this.lastTs);
            const delta = y - this.lastY;

            this.velocity = delta / elapsed;
            this.lastY = y;
            this.lastTs = now;
            this.dy = Math.max(0, y - this.startY);

            const panel = this.$refs.panel;
            const overlay = this.$refs.overlay;
            if (panel) {
                panel.style.transform = `translate3d(0, ${this.dy}px, 0)`;
            }
            if (overlay) {
                const progress = Math.min(1, this.dy / 280);
                overlay.style.setProperty('--sheet-overlay-opacity', String(1 - progress * 0.8));
            }

            event.preventDefault();
        },

        onPointerUp(event) {
            if (! this.dragging || event.pointerId !== this.pointerId) {
                return;
            }

            this.finishDrag();
        },

        onPointerCancel(event) {
            if (! this.dragging || event.pointerId !== this.pointerId) {
                return;
            }

            this.finishDrag({ forceSnap: true });
        },

        finishDrag({ forceSnap = false } = {}) {
            this.dragging = false;
            this.pointerId = null;

            const panel = this.$refs.panel;
            const overlay = this.$refs.overlay;
            panel?.classList.remove('is-dragging');
            overlay?.classList.remove('is-dragging');

            const shouldClose = ! forceSnap && (this.dy > 96 || this.velocity > 0.6);

            if (shouldClose) {
                if (panel) {
                    panel.style.transform = `translate3d(0, 100%, 0)`;
                }
                if (overlay) {
                    overlay.style.setProperty('--sheet-overlay-opacity', '0');
                }
                this.$wire?.dispatch('close-dashboard-modal');
                return;
            }

            if (panel) {
                panel.style.transform = '';
            }
            if (overlay) {
                overlay.style.removeProperty('--sheet-overlay-opacity');
            }
            this.dy = 0;
            this.velocity = 0;
        },
    }));

    Alpine.data('guestContactPicker', (config = {}) => ({
        error: '',
        unsupportedMsg: config.unsupportedMsg || '',
        failedMsg: config.failedMsg || '',

        supportsContacts() {
            return typeof navigator !== 'undefined'
                && 'contacts' in navigator
                && 'ContactsManager' in window
                && window.isSecureContext;
        },

        firstNonEmpty(arr) {
            if (! Array.isArray(arr)) {
                return null;
            }

            const value = arr.find((entry) => typeof entry === 'string' && entry.trim());

            return value ? value.trim() : null;
        },

        async pickFromContacts() {
            this.error = '';

            if (! this.supportsContacts()) {
                this.error = this.unsupportedMsg;

                return;
            }

            try {
                const available = await navigator.contacts.getProperties();
                const wanted = ['name', 'email', 'tel'].filter((prop) => available.includes(prop));

                if (wanted.length === 0) {
                    this.error = this.failedMsg;

                    return;
                }

                const contacts = await navigator.contacts.select(wanted, { multiple: false });

                if (! contacts?.length) {
                    return;
                }

                const contact = contacts[0];
                const name = this.firstNonEmpty(contact.name);
                const email = this.firstNonEmpty(contact.email);
                const phone = this.firstNonEmpty(contact.tel);

                if (name) {
                    await this.$wire.set('name', name);
                }
                if (email) {
                    await this.$wire.set('email', email);
                }
                if (phone) {
                    await this.$wire.set('phone', phone);
                }
            } catch (error) {
                if (error?.name === 'AbortError') {
                    return;
                }

                this.error = this.failedMsg;
            }
        },
    }));
});
