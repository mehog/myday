@props(['theme'])

@php
    $themes = [
        'amber-gold' => [
            '--color-primary' => '#c9a227',
            '--color-primary-dark' => '#a8841a',
            '--color-accent' => '#f5e6c8',
            '--color-bg' => '#1a1208',
            '--color-bg-soft' => '#2a1f0f',
            '--color-text' => '#faf6ee',
            '--color-text-muted' => '#d4c4a8',
            '--font-heading' => "'Cormorant Garamond', serif",
            '--font-body' => "'Lora', serif",
            '--gradient-hero' => 'linear-gradient(180deg, rgba(26,18,8,0.3) 0%, rgba(26,18,8,0.95) 100%)',
        ],
        'royal-wedding' => [
            '--color-primary' => '#1e3a5f',
            '--color-primary-dark' => '#152a45',
            '--color-accent' => '#d4af37',
            '--color-bg' => '#0f1a2e',
            '--color-bg-soft' => '#1a2744',
            '--color-text' => '#f8f6f0',
            '--color-text-muted' => '#b8c5d6',
            '--font-heading' => "'Playfair Display', serif",
            '--font-body' => "'Lora', serif",
            '--gradient-hero' => 'linear-gradient(180deg, rgba(15,26,46,0.3) 0%, rgba(15,26,46,0.95) 100%)',
        ],
        'lavender-dream' => [
            '--color-primary' => '#9b7bb8',
            '--color-primary-dark' => '#7d5f9a',
            '--color-accent' => '#e8dff5',
            '--color-bg' => '#2d2438',
            '--color-bg-soft' => '#3d3249',
            '--color-text' => '#faf8fc',
            '--color-text-muted' => '#c9bdd8',
            '--font-heading' => "'Cormorant Garamond', serif",
            '--font-body' => "'Lora', serif",
            '--gradient-hero' => 'linear-gradient(180deg, rgba(45,36,56,0.3) 0%, rgba(45,36,56,0.95) 100%)',
        ],
        'winter-magic' => [
            '--color-primary' => '#7eb8da',
            '--color-primary-dark' => '#5a9bc4',
            '--color-accent' => '#e8f4fc',
            '--color-bg' => '#1a2332',
            '--color-bg-soft' => '#243044',
            '--color-text' => '#f0f8ff',
            '--color-text-muted' => '#a8c4d8',
            '--font-heading' => "'Playfair Display', serif",
            '--font-body' => "'Crimson Pro', serif",
            '--gradient-hero' => 'linear-gradient(180deg, rgba(26,35,50,0.3) 0%, rgba(26,35,50,0.95) 100%)',
        ],
        'pearl-white' => [
            '--color-primary' => '#8C7355',
            '--color-primary-dark' => '#6E5A42',
            '--color-accent' => '#E8E0D5',
            '--color-bg' => '#FAFAF8',
            '--color-bg-soft' => '#F0EDE8',
            '--color-text' => '#1C1917',
            '--color-text-muted' => '#78716C',
            '--font-heading' => "'Montserrat', sans-serif",
            '--font-body' => "'Lora', serif",
            '--gradient-hero' => 'linear-gradient(180deg, rgba(250,250,248,0.3) 0%, rgba(250,250,248,0.92) 100%)',
        ],
        'dusty-rose' => [
            '--color-primary' => '#B5706A',
            '--color-primary-dark' => '#9A5A55',
            '--color-accent' => '#E8C9C4',
            '--color-bg' => '#F9F1EE',
            '--color-bg-soft' => '#F2E6E1',
            '--color-text' => '#2D1B16',
            '--color-text-muted' => '#8B6E65',
            '--font-heading' => "'Cormorant Garamond', serif",
            '--font-body' => "'Lora', serif",
            '--gradient-hero' => 'linear-gradient(180deg, rgba(249,241,238,0.3) 0%, rgba(249,241,238,0.92) 100%)',
        ],
    ];

    $vars = $themes[$theme->value] ?? $themes['amber-gold'];
@endphp

<div
    {{ $attributes->merge(['class' => 'invitation-theme min-h-screen']) }}
    style="@foreach ($vars as $key => $value) {{ $key }}: {{ $value }}; @endforeach"
>
    {{ $slot }}
</div>
