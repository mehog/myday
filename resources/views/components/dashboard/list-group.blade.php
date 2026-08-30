@props([
    'title' => null,
    'action' => null,
    'actionHref' => null,
])

<section {{ $attributes->class('dashboard-list-group') }}>
    @if ($title || $action || $actionHref || isset($header))
        <div class="mb-2 flex items-center justify-between gap-2 px-1">
            @if ($title)
                <h3 class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $title }}</h3>
            @elseif (isset($header))
                {{ $header }}
            @else
                <span></span>
            @endif
            @if ($actionHref)
                <a href="{{ $actionHref }}" class="text-xs font-medium text-primary hover:underline">{{ $action }}</a>
            @elseif ($action)
                <span class="text-xs font-medium text-primary">{{ $action }}</span>
            @endif
        </div>
    @endif
    <div class="dashboard-list-group-body overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
        {{ $slot }}
    </div>
</section>
