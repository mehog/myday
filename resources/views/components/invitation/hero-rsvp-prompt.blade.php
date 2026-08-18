@props([
    'guest',
    'variant' => 'hero',
])

@php
    $parts = preg_split('/\s+/', trim($guest->name));
    $firstName = $parts[0] ?? $guest->name;
@endphp

<div @class([
    'hero-rsvp-prompt invitation-fade-in',
    'hero-rsvp-prompt--story' => $variant === 'story',
])>
    <p @class([
        'hero-rsvp-question invitation-body',
        'text-lg text-[var(--color-text)]' => $variant !== 'story',
        'story-subtitle' => $variant === 'story',
    ])>
        {{ __('invitation.hero_rsvp_question', ['name' => $firstName]) }}
    </p>
    <div class="hero-rsvp-actions">
        <button
            type="button"
            @click="$dispatch('go-to-rsvp'); $dispatch('hero-rsvp', { pending: 'yes' })"
            class="rsvp-btn rsvp-btn-yes flex-1 rounded-xl px-6 py-3.5 invitation-heading text-lg transition"
        >
            {{ __('invitation.yes_attending') }}
        </button>
        <button
            type="button"
            @click="$dispatch('go-to-rsvp'); $dispatch('hero-rsvp', { pending: 'no' })"
            class="rsvp-btn rsvp-btn-no flex-1 rounded-xl px-6 py-3.5 invitation-heading text-lg transition"
        >
            {{ __('invitation.no_attending') }}
        </button>
    </div>
</div>
