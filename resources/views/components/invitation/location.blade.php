@php
    $mapQuery = urlencode($event->location_address ?: $event->location_name);
    $mapSrc = $event->location_lat && $event->location_lng
        ? "https://maps.google.com/maps?q={$event->location_lat},{$event->location_lng}&z=15&output=embed"
        : "https://maps.google.com/maps?q={$mapQuery}&z=15&output=embed";
@endphp

<section class="invitation-section py-20 px-6">
    <div class="max-w-4xl mx-auto invitation-fade-in">
        <div class="text-center mb-10">
            <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">{{ __('invitation.find_us') }}</p>
            <h2 class="invitation-heading text-4xl text-[var(--color-text)]">{{ __('invitation.location') }}</h2>
        </div>

        <div class="rounded-2xl overflow-hidden border border-white/10 shadow-2xl mb-8">
            <iframe
                src="{{ $mapSrc }}"
                class="w-full h-72 sm:h-96 border-0"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="{{ __('invitation.map_title') }}"
            ></iframe>
        </div>

        <div class="text-center">
            @if ($event->location_name)
                <h3 class="invitation-heading text-2xl text-[var(--color-text)] mb-2">{{ $event->location_name }}</h3>
            @endif
            @if ($event->location_address)
                <p class="invitation-body text-[var(--color-text-muted)]">{{ $event->location_address }}</p>
            @endif
        </div>
    </div>
</section>
