<!DOCTYPE html>
<html lang="{{ \App\Support\Locale::htmlLang() }}">
<head>
    <x-google-analytics />
    <x-meta-pixel />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="robots" content="noindex, nofollow">

    @php
        $ogImage = file_exists(public_path('img/og-image.jpg'))
            ? asset('img/og-image.jpg')
            : asset('img/wedding-bckg.webp');
        $resolvedPageTitle = $pageTitle ?? __('start.meta_title');
        $resolvedPageDescription = $pageDescription ?? __('start.meta_description');
        $resolvedCanonicalUrl = $canonicalUrl ?? url('/start');
    @endphp

    <title>{{ $resolvedPageTitle }} | {{ config('app.name') }}</title>
    <meta name="description" content="{{ $resolvedPageDescription }}">
    <link rel="canonical" href="{{ $resolvedCanonicalUrl }}">

    @foreach (\App\Support\Locale::supported() as $hreflangLocale)
        <link
            rel="alternate"
            hreflang="{{ \App\Support\Locale::htmlLang($hreflangLocale) }}"
            href="{{ \App\Support\LocaleUrl::withLocale($resolvedCanonicalUrl, $hreflangLocale) }}"
        >
    @endforeach
    <link
        rel="alternate"
        hreflang="x-default"
        href="{{ \App\Support\LocaleUrl::withLocale($resolvedCanonicalUrl, \App\Support\Locale::default()) }}"
    >

    @isset($jsonLd)
        @foreach ((array) $jsonLd as $schema)
            <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        @endforeach
    @endisset

    <meta property="og:title" content="{{ $resolvedPageTitle }}">
    <meta property="og:description" content="{{ $resolvedPageDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $resolvedCanonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="{{ \App\Support\Locale::ogLocale() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $resolvedPageTitle }}">
    <meta name="twitter:description" content="{{ $resolvedPageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @if (file_exists(public_path('img/apple-touch-icon.png')))
        <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,600i,700,700i" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="landing-page antialiased">
    @isset($slot)
        {{ $slot }}
    @else
        @yield('content')
    @endisset

    <div x-data="invitationReturn()" x-cloak>
        <a
            x-show="url"
            :href="url"
            class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 inline-flex items-center gap-2 px-6 py-3 rounded-full border border-[#c9a227] text-[#c9a227] bg-white/90 backdrop-blur-sm shadow-md landing-heading text-sm whitespace-nowrap hover:bg-[#c9a227] hover:text-[#1a1208] transition"
        >
            &larr; {{ __('start.back_to_invitation') }}
        </a>
    </div>

    <x-support-bubble />

    <x-invitation-preview-modal />

    @livewireScripts
</body>
</html>
