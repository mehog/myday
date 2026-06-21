<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('landing.meta_title') }} | {{ config('app.name', 'NasDan') }}</title>
    <meta name="description" content="{{ __('landing.meta_description') }}">
    <meta property="og:title" content="{{ __('landing.meta_title') }}">
    <meta property="og:description" content="{{ __('landing.meta_description') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta name="twitter:card" content="summary_large_image">

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
