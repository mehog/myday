@props([
    'title' => null,
    'subtitle' => null,
])

{{-- Desktop / tablet page header; phones use the topbar title instead --}}
<div {{ $attributes->class('mb-1 hidden lg:block') }}>
    @if ($title)
        <h2 class="text-xl font-semibold tracking-tight">{{ $title }}</h2>
    @endif
    @if ($subtitle)
        <p class="mt-1 text-sm text-muted-foreground">{{ $subtitle }}</p>
    @endif
    @isset($actions)
        <div class="mt-3 flex flex-wrap gap-2">
            {{ $actions }}
        </div>
    @endisset
    {{ $slot }}
</div>
