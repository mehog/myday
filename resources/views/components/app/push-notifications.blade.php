@if (auth()->check() && config('webpush.vapid.public_key'))
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; i++) {
                outputArray[i] = rawData.charCodeAt(i);
            }

            return outputArray;
        }

        function getDeviceLabel() {
            const ua = navigator.userAgent;
            let browser = 'Browser';

            if (/Edg/i.test(ua)) {
                browser = 'Edge';
            } else if (/Chrome/i.test(ua)) {
                browser = 'Chrome';
            } else if (/Safari/i.test(ua)) {
                browser = 'Safari';
            } else if (/Firefox/i.test(ua)) {
                browser = 'Firefox';
            }

            let os = 'Unknown';

            if (/iPhone/i.test(ua)) {
                os = 'iPhone';
            } else if (/iPad/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)) {
                os = 'iPad';
            } else if (/Android/i.test(ua)) {
                os = 'Android';
            } else if (/Mac/i.test(ua)) {
                os = 'Mac';
            } else if (/Windows/i.test(ua)) {
                os = 'Windows';
            } else if (/Linux/i.test(ua)) {
                os = 'Linux';
            }

            return `${browser} on ${os}`;
        }

        async function sendSubscriptionToServer(subscription) {
            const payload = subscription.toJSON();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            const response = await fetch(@js(route('push.user.subscribe')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    endpoint: payload.endpoint,
                    keys: payload.keys,
                    content_encoding: 'aesgcm',
                    device_label: getDeviceLabel(),
                }),
            });

            return response.ok;
        }

        window.subscribeToPushAsUser = async function () {
            try {
                const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent)
                    || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
                const isStandalone = window.navigator.standalone === true
                    || window.matchMedia('(display-mode: standalone)').matches;

                if (isIos && !isStandalone && !('PushManager' in window)) {
                    return { ok: false, error: 'push_needs_install' };
                }

                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    return {
                        ok: false,
                        error: isIos ? 'push_ios_update' : 'push_error_not_supported',
                    };
                }

                const vapidKey = document.querySelector('meta[name="vapid-public-key"]')?.content;

                if (!vapidKey) {
                    return { ok: false, error: 'push_error_config' };
                }

                const permission = await Notification.requestPermission();

                if (permission === 'denied') {
                    return { ok: false, error: 'push_error_denied' };
                }

                if (permission !== 'granted') {
                    return { ok: false, error: null };
                }

                const registration = await navigator.serviceWorker.ready;

                const existing = await registration.pushManager.getSubscription();
                if (existing) {
                    await existing.unsubscribe();
                }

                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidKey),
                });

                const ok = await sendSubscriptionToServer(subscription);

                if (!ok) {
                    return { ok: false, error: 'push_error_server' };
                }

                return { ok: true, error: null };
            } catch (e) {
                return { ok: false, error: 'push_error_unknown' };
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
                return;
            }

            if (Notification.permission === 'granted') {
                navigator.serviceWorker.ready.then(async (registration) => {
                    const existing = await registration.pushManager.getSubscription();
                    if (existing) {
                        const ok = await sendSubscriptionToServer(existing);
                        if (ok) {
                            window.dispatchEvent(new CustomEvent('user-push-subscribed'));
                        }
                    }
                });
            }
        });
    </script>

    <div
        x-data="{
            showBanner: false,
            subscribing: false,
            init() {
                if (!('Notification' in window)) {
                    return;
                }

                this.showBanner = Notification.permission === 'default';
            },
            async enablePush() {
                this.subscribing = true;
                const result = await subscribeToPushAsUser();
                this.subscribing = false;

                if (result.ok) {
                    this.showBanner = false;
                    window.dispatchEvent(new CustomEvent('user-push-subscribed'));
                }
            },
        }"
        x-show="showBanner"
        x-cloak
        class="fixed inset-x-4 bottom-4 z-50 mx-auto max-w-xl rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-white/10 dark:bg-gray-900 sm:inset-x-auto sm:right-6"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-950 dark:text-white">
                    {{ __('app.couple_push_banner_title') }}
                </p>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    {{ __('app.couple_push_banner_body') }}
                </p>
            </div>

            <div class="flex shrink-0 gap-2">
                <button
                    type="button"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5"
                    @click="showBanner = false"
                >
                    {{ __('app.push_notifications_maybe_later') }}
                </button>
                <button
                    type="button"
                    class="rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white hover:bg-primary-500 disabled:opacity-60"
                    :disabled="subscribing"
                    @click="enablePush()"
                >
                    <span x-show="! subscribing">{{ __('app.push_enable_notifications') }}</span>
                    <span x-show="subscribing">{{ __('app.couple_push_banner_enabling') }}</span>
                </button>
            </div>
        </div>
    </div>
@endif
