@php
    $locations = $event->relationLoaded('locations')
        ? $event->locations->filter(fn ($location) => $location->hasMapContent())->values()
        : $event->locations()->get()->filter(fn ($location) => $location->hasMapContent())->values();
@endphp

@if ($locations->isNotEmpty())
<section class="invitation-section py-20 px-6">
    <div class="max-w-4xl mx-auto invitation-fade-in">
        <div class="text-center mb-10">
            <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">{{ __('invitation.find_us') }}</p>
            <h2 class="invitation-heading text-4xl text-[var(--color-text)]">{{ __('invitation.location') }}</h2>
        </div>

        <div class="space-y-12">
            @foreach ($locations as $location)
                <div>
                    <div class="rounded-2xl overflow-hidden border border-white/10 shadow-2xl mb-6">
                        <iframe
                            src="{{ $location->mapEmbedUrl() }}"
                            class="w-full h-72 sm:h-96 border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="{{ $location->displayName() ?: __('invitation.map_title') }}"
                        ></iframe>
                    </div>

                    <div class="text-center">
                        @if (filled($location->label))
                            <p class="text-sm uppercase tracking-[0.25em] text-[var(--color-text-muted)] mb-2">{{ $location->label }}</p>
                        @endif
                        @if ($location->name)
                            <h3 class="invitation-heading text-2xl text-[var(--color-text)] mb-2">{{ $location->name }}</h3>
                        @endif
                        @if ($location->address)
                            <p class="invitation-body text-[var(--color-text-muted)]">{{ $location->address }}</p>
                        @endif
                        @if ($location->directionsUrl())
                            <a
                                href="{{ $location->directionsUrl() }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 mt-4 text-sm uppercase tracking-[0.2em] text-[var(--color-primary)] hover:text-[var(--color-primary-dark)] transition"
                            >
                                {{ __('invitation.get_directions') }}
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
