<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-google-analytics />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1a1208">

    @php
        $ogImage = file_exists(public_path('img/og-image.jpg'))
            ? asset('img/og-image.jpg')
            : asset('img/wedding-bckg.webp');
        $resolvedPageTitle = $pageTitle ?? __('landing.meta_title');
        $resolvedPageDescription = $pageDescription ?? __('landing.meta_description');
        $resolvedCanonicalUrl = $canonicalUrl ?? url('/');
    @endphp

    <title>{{ $resolvedPageTitle }} | {{ config('app.name', 'NasDan') }}</title>
    <meta name="description" content="{{ $resolvedPageDescription }}">
    <link rel="canonical" href="{{ $resolvedCanonicalUrl }}">

    <meta property="og:title" content="{{ $resolvedPageTitle }}">
    <meta property="og:description" content="{{ $resolvedPageDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $resolvedCanonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="{{ \App\Support\Locale::ogLocale() }}">
    <meta property="og:site_name" content="{{ config('app.name', 'NasDan') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $resolvedPageTitle }}">
    <meta name="twitter:description" content="{{ $resolvedPageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @if (file_exists(public_path('img/apple-touch-icon.png')))
        <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
    @endif

    <link rel="preload" as="image" href="{{ asset('img/wedding-bckg.webp') }}" type="image/webp">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
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
            class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 inline-flex items-center gap-2 px-6 py-3 rounded-full border border-[#c9a227] text-[#c9a227] bg-[#1a1208]/80 backdrop-blur-sm landing-heading text-sm whitespace-nowrap hover:bg-[#c9a227] hover:text-[#1a1208] transition"
        >
            &larr; {{ __('landing.back_to_invitation') }}
        </a>
    </div>

    <x-support-bubble />

    @livewireScripts
</body>
</html>
