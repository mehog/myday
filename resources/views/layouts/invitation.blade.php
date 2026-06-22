<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @isset($event)
        <title>{{ $event->couple_names }} | {{ __('invitation.title') }}</title>
        <meta name="description" content="{{ __('invitation.meta_description', ['couple' => $event->couple_names]) }}">
        <meta property="og:title" content="{{ $event->couple_names }} | {{ __('invitation.title') }}">
        <meta property="og:description" content="{{ __('invitation.meta_og_description', ['date' => $event->wedding_date->translatedFormat('j. F Y.')]) }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        @if ($event->hero_image_url)
            <meta property="og:image" content="{{ $event->hero_image_url }}">
        @endif
        <meta name="twitter:card" content="summary_large_image">
    @else
        <title>{{ config('app.name', __('invitation.title')) }}</title>
    @endisset

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,700|playfair-display:400,500,600,700|lora:400,500,600|crimson-pro:400,500,600" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased">
    {{ $slot }}

    @livewireScripts
</body>
</html>
