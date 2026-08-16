@props(['label', 'value', 'description' => null, 'href' => null, 'tone' => null])

@php
    $toneClass = match ($tone) {
        'success' => 'text-emerald-600 dark:text-emerald-400',
        'warning' => 'text-amber-600 dark:text-amber-400',
        default => 'text-foreground',
    };
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'rounded-xl border border-border bg-card p-4 shadow-sm transition hover:border-primary/40'.($href ? ' block' : '')]) }}>
    <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">{{ $label }}</p>
    <p class="mt-2 text-2xl font-semibold tabular-nums {{ $toneClass }}">{{ $value }}</p>
    @if ($description)
        <p class="mt-1 text-sm text-muted-foreground">{{ $description }}</p>
    @endif
</{{ $tag }}>
