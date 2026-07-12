@props([
    'variant' => 'hero',
    'label' => null,
])

@php
    $label = $label ?? __('invitation.rsvp_nudge_cta');
@endphp

<a
    href="#rsvp"
    {{ $attributes->merge([
        'class' => match ($variant) {
            'sticky' => 'rsvp-btn rsvp-btn-yes shrink-0 px-5 py-2.5 rounded-xl invitation-heading text-sm transition',
            'story' => 'rsvp-btn rsvp-btn-yes inline-block mt-6 px-8 py-4 rounded-xl invitation-heading text-lg transition',
            default => 'rsvp-btn rsvp-btn-yes inline-block mt-8 px-8 py-4 rounded-xl invitation-heading text-lg transition',
        },
    ]) }}
    @click.prevent="
        $dispatch('go-to-rsvp');
        document.getElementById('rsvp')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    "
>
    {{ $label }}
</a>
