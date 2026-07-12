@php
    $heroUrl = $event->hero_image_url;
@endphp

<section class="invitation-section hero-section relative min-h-[85vh] flex items-end justify-center overflow-hidden">
    @if ($heroUrl)
        <div
            class="absolute inset-0 bg-cover bg-center scale-105"
            style="background-image: url('{{ $heroUrl }}');"
        ></div>
    @else
        <div class="absolute inset-0 bg-[var(--color-bg-soft)]"></div>
    @endif

    <div class="absolute inset-0" style="background: var(--gradient-hero);"></div>

    <div class="relative z-10 w-full max-w-4xl mx-auto px-6 pb-16 pt-32 text-center invitation-fade-in">
        <p class="text-sm uppercase tracking-[0.35em] text-[var(--color-text-muted)] mb-4">
            {{ $event->isWeddingDay() ? __('invitation.today_is_the_day_eyebrow') : __('invitation.save_the_date') }}
        </p>
        <h1 class="invitation-heading text-5xl sm:text-6xl md:text-7xl font-semibold text-[var(--color-text)] mb-4">
            {{ $event->groom_name }}
            <span class="text-[var(--color-primary)]">&</span>
            {{ $event->bride_name }}
        </h1>
        <p class="text-xl sm:text-2xl text-[var(--color-accent)] invitation-body">
            {{ $event->wedding_date->translatedFormat('l, j. F Y.') }}
        </p>
        @if ($event->location_name)
            <p class="mt-3 text-[var(--color-text-muted)] invitation-body">
                {{ $event->location_name }}
            </p>
        @endif

        @if ($showRsvpNudge ?? false)
            @include('components.invitation.rsvp-nudge-link')
        @endif
    </div>
</section>
