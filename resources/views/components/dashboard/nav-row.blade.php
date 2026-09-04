@props([
    'item',
    'tag' => 'a',
])

@php
    $active = \App\Support\DashboardNav::isActive($item);
    $classes = [
        'flex items-center gap-3 px-4 py-3.5 transition-colors',
        'bg-accent/60' => $active,
        'hover:bg-muted/60' => ! $active,
    ];
@endphp

@if ($tag === 'a')
    <a href="{{ route($item['route']) }}" @class($classes) {{ $attributes }}>
        @include('components.dashboard.partials.nav-row-content')
    </a>
@else
    <button type="button" @class(array_merge($classes, ['w-full text-left'])) {{ $attributes }}>
        @include('components.dashboard.partials.nav-row-content')
    </button>
@endif
