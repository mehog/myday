@props([
    'variant' => 'default',
    'type' => 'button',
    'href' => null,
])

@php
    $classes = match ($variant) {
        'secondary' => 'inline-flex items-center justify-center gap-2 rounded-md border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-accent',
        'ghost' => 'inline-flex items-center justify-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-accent-foreground',
        'destructive' => 'inline-flex items-center justify-center gap-2 rounded-md bg-destructive px-3 py-2 text-sm font-medium text-destructive-foreground hover:opacity-90',
        'outline' => 'inline-flex items-center justify-center gap-2 rounded-md border border-primary/40 bg-transparent px-3 py-2 text-sm font-medium text-primary hover:bg-accent',
        default => 'inline-flex items-center justify-center gap-2 rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground hover:opacity-90',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
