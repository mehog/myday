<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-google-analytics />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (config('webpush.vapid.public_key'))
        <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    @endif

    @isset($event)
        <title>{{ $event->couple_names }} | {{ __('invitation.title') }}</title>
        <meta name="description" content="{{ __('invitation.meta_description', ['couple' => $event->couple_names]) }}">
        <meta property="og:title" content="{{ $event->couple_names }} | {{ __('invitation.title') }}">
        <meta property="og:description" content="{{ __('invitation.meta_og_description', ['date' => $event->wedding_date->translatedFormat('j. F Y.')]) }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="{{ config('app.name', 'NasDan') }}">
        <meta property="og:locale" content="{{ \App\Support\Locale::ogLocale() }}">
        @if ($event->hero_image_url)
            <meta property="og:image" content="{{ $event->hero_image_url }}">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
        @endif
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $event->couple_names }}">
        <meta name="twitter:description" content="{{ __('invitation.meta_og_description', ['date' => $event->wedding_date->translatedFormat('j. F Y.')]) }}">
        @if ($event->hero_image_url)
            <meta name="twitter:image" content="{{ $event->hero_image_url }}">
        @endif

        @isset($guest)
            <meta name="robots" content="noindex, nofollow">
        @endisset

        @if (! empty($isPersonalLink) && isset($guest))
            <link rel="manifest" href="{{ route('invitation.manifest', [$event->slug, $guest->token]) }}">
            <meta name="mobile-web-app-capable" content="yes">
            <meta name="apple-mobile-web-app-capable" content="yes">
            <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
            <meta name="apple-mobile-web-app-title" content="{{ $event->couple_names }}">
            <link rel="apple-touch-icon" href="/icons/nd-logo-transparent.webp">
            <meta name="theme-color" content="{{ match ($event->theme->value) {
                'pearl-white' => '#FAFAF8',
                'dusty-rose' => '#F9F1EE',
                'paper-ink' => '#F3EDE3',
                'royal-wedding' => '#0f1a2e',
                'lavender-dream' => '#2d2438',
                'winter-magic' => '#1a2332',
                default => '#1a1208',
            } }}">
        @endif

        @if (! empty($isPreview))
            <meta name="robots" content="noindex, nofollow">
        @endif

        <link rel="canonical" href="{{ url('/e/'.$event->slug) }}">
    @else
        <title>{{ config('app.name', __('invitation.title')) }}</title>
    @endisset

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,700|playfair-display:400,500,600,700|lora:400,500,600|crimson-pro:400,500,600|montserrat:300,400,500,600,700|great-vibes:400" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased">
    {{ $slot }}

    @livewireScripts

    <script>
        try {
            localStorage.setItem('nd_invitation_url', JSON.stringify({
                url: window.location.href,
                expires: Date.now() + 86400000
            }));
        } catch (e) {}
    </script>

    @isset($guest)
        @if (! empty($isPersonalLink) && config('webpush.vapid.public_key'))
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

                window.subscribeToPush = async function (subscribeUrl) {
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

                        // Unsubscribe any stale subscription (e.g. from a different VAPID key)
                        const existing = await registration.pushManager.getSubscription();
                        if (existing) {
                            await existing.unsubscribe();
                        }

                        const subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(vapidKey),
                        });

                        const payload = subscription.toJSON();
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                        const response = await fetch(subscribeUrl, {
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
                            }),
                        });

                        if (!response.ok) {
                            return { ok: false, error: 'push_error_server' };
                        }

                        return { ok: true, error: null };
                    } catch (e) {
                        return { ok: false, error: 'push_error_unknown' };
                    }
                };
            </script>
        @endif
    @endisset
</body>
</html>
