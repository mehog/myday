@props([
    'type' => 'date',
    'variant' => 'dashboard',
    'disabled' => false,
])

@php
    $type = in_array($type, ['date', 'datetime-local', 'time'], true) ? $type : 'date';
    $icon = $type === 'time' ? 'clock' : 'calendar';
    $isLanding = $variant === 'landing';
    $wrapperClasses = $isLanding
        ? 'dashboard-date-field relative w-full min-w-0 max-w-full'
        : 'dashboard-date-field relative h-10 w-full min-w-0 max-w-full overflow-hidden rounded-md border border-border bg-background';
    $inputClasses = $isLanding
        ? 'dashboard-date-input landing-date-input w-full min-w-0 max-w-full'
        : 'dashboard-date-input flex h-full w-full min-w-0 max-w-full items-center border-0 bg-transparent px-3 pr-10 text-sm leading-none disabled:opacity-60';
@endphp

<div class="{{ $wrapperClasses }}" data-variant="{{ $variant }}">
    <input
        type="{{ $type }}"
        @disabled($disabled)
        {{ $attributes->class($inputClasses) }}
    >
    <span class="dashboard-date-field__icon pointer-events-none absolute inset-y-0 right-0 flex w-10 items-center justify-center" aria-hidden="true">
        <x-dashboard.icon :name="$icon" class="h-4 w-4" />
    </span>
</div>
