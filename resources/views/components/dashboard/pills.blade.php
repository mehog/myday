@props([
    'name',
    'selected',
    'options' => [],
    'disabled' => false,
    'label' => null,
])

@php
    $resolved = [];

    foreach ($options as $value => $option) {
        if (is_array($option)) {
            $resolved[(string) $value] = [
                'label' => $option['label'] ?? (string) $value,
                'icon' => $option['icon'] ?? null,
            ];

            continue;
        }

        $resolved[(string) $value] = [
            'label' => (string) $option,
            'icon' => null,
        ];
    }
@endphp

<div
    {{ $attributes->class('dashboard-pills') }}
    role="tablist"
    @if ($label)
        aria-label="{{ $label }}"
    @endif
>
    @foreach ($resolved as $value => $option)
        @php
            $isActive = (string) $selected === (string) $value;
        @endphp
        <button
            type="button"
            role="tab"
            @class([
                'dashboard-pill',
                'is-active' => $isActive,
            ])
            aria-selected="{{ $isActive ? 'true' : 'false' }}"
            wire:click="$set('{{ $name }}', '{{ $value }}')"
            @disabled($disabled)
        >
            @if (filled($option['icon']))
                <x-dashboard.icon :name="$option['icon']" class="h-4 w-4" />
            @endif
            {{ $option['label'] }}
        </button>
    @endforeach
</div>
