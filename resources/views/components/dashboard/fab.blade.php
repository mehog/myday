@props([
    'href' => null,
    'label',
    'show' => true,
])

@if ($show)
    @if ($href)
        <a href="{{ $href }}" class="dashboard-fab" aria-label="{{ $label }}">
            <x-dashboard.icon name="plus" class="h-5 w-5" />
            <span>{{ $label }}</span>
        </a>
    @else
        <button type="button" class="dashboard-fab" {{ $attributes->merge(['aria-label' => $label]) }}>
            <x-dashboard.icon name="plus" class="h-5 w-5" />
            <span>{{ $label }}</span>
        </button>
    @endif
@endif
