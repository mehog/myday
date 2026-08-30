<!DOCTYPE html>
<html lang="{{ \App\Support\Locale::htmlLang() }}">
<head>
    <x-google-analytics />
    <x-meta-pixel />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">

    <title>{{ __('onboarding.meta_title') }} | {{ config('app.name') }}</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=eb-garamond:400,500,600,600i,700,700i" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="landing-page antialiased min-h-screen">
    {{ $slot }}

    <x-invitation-preview-modal />

    @livewireScripts
</body>
</html>
