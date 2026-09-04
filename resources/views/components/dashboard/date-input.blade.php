@props([
    'type' => 'date',
    'variant' => 'dashboard',
    'disabled' => false,
])

@php
    $type = in_array($type, ['date', 'datetime-local', 'time'], true) ? $type : 'date';
    $icon = $type === 'time' ? 'clock' : 'calendar';
    $isLanding = $variant === 'landing';
    $inputClasses = $isLanding
        ? 'landing-input landing-date-input dashboard-date-input w-full min-w-0 max-w-full pr-10'
        : 'dashboard-date-input block h-10 w-full min-w-0 max-w-full rounded-md border border-border bg-background px-3 pr-10 text-sm disabled:opacity-60';
@endphp

<div class="dashboard-date-field relative w-full min-w-0 max-w-full" data-variant="{{ $variant }}">
    <input
        type="{{ $type }}"
        @disabled($disabled)
        {{ $attributes->class($inputClasses) }}
    >
    <span class="dashboard-date-field__icon pointer-events-none absolute inset-y-0 right-0 flex w-10 items-center justify-center" aria-hidden="true">
        <x-dashboard.icon :name="$icon" class="h-4 w-4" />
    </span>
</div>
