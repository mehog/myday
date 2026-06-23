<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1a1208">

    @php
        $ogImage = file_exists(public_path('img/og-image.jpg'))
            ? asset('img/og-image.jpg')
            : asset('img/wedding-bckg.webp');
    @endphp

    <title>{{ __('landing.meta_title') }} | {{ config('app.name', 'NasDan') }}</title>
    <meta name="description" content="{{ __('landing.meta_description') }}">
    <link rel="canonical" href="{{ url('/') }}">

    <meta property="og:title" content="{{ __('landing.meta_title') }}">
    <meta property="og:description" content="{{ __('landing.meta_description') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="{{ app()->getLocale() === 'bs' ? 'bs_BA' : 'en_US' }}">
    <meta property="og:site_name" content="{{ config('app.name', 'NasDan') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('landing.meta_title') }}">
    <meta name="twitter:description" content="{{ __('landing.meta_description') }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @if (file_exists(public_path('img/apple-touch-icon.png')))
        <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
    @endif

    <link rel="preload" as="image" href="{{ asset('img/wedding-bckg.webp') }}" type="image/webp">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,700|lora:400,500,600" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="landing-page antialiased">
    {{ $slot }}

    @livewireScripts
</body>
</html>
