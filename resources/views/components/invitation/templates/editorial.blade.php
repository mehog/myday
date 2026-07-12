@php
    $heroUrl = $event->hero_image_url;
@endphp

{{-- Hero: split-screen --}}
<section class="invitation-section editorial-hero min-h-[85vh] lg:min-h-screen flex flex-col lg:flex-row">
    <div class="editorial-hero-photo relative h-[50vh] lg:h-auto lg:w-[55%] shrink-0 overflow-hidden">
        @if ($heroUrl)
            <img
                src="{{ $heroUrl }}"
                alt="{{ $event->couple_names }}"
                class="absolute inset-0 h-full w-full object-cover"
            >
        @else
            <div class="absolute inset-0 bg-[var(--color-bg-soft)]"></div>
        @endif
    </div>

    <div class="editorial-hero-panel flex flex-1 items-center justify-center bg-[var(--color-bg)] px-8 py-16 lg:px-14 lg:py-20">
        <div class="max-w-md text-center lg:text-left invitation-fade-in">
            <p class="text-xs sm:text-sm uppercase tracking-[0.35em] text-[var(--color-text-muted)] mb-6">
                {{ $event->isWeddingDay() ? __('invitation.today_is_the_day_eyebrow') : __('invitation.save_the_date') }}
            </p>
            <h1 class="invitation-heading text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-semibold text-[var(--color-text)] leading-tight mb-6">
                {{ $event->groom_name }}
                <span class="block text-[var(--color-primary)] my-2">&</span>
                {{ $event->bride_name }}
            </h1>
            <p class="text-lg sm:text-xl text-[var(--color-accent)] invitation-body">
                {{ $event->wedding_date->translatedFormat('l, j. F Y.') }}
            </p>
            @if ($event->location_name)
                <p class="mt-4 text-[var(--color-text-muted)] invitation-body">
                    {{ $event->location_name }}
                </p>
            @endif

            @if ($showRsvpNudge ?? false)
                @include('components.invitation.rsvp-nudge-link')
            @endif
        </div>
    </div>
</section>

{{-- Countdown: inline numbers --}}
<section
    class="invitation-section editorial-countdown py-20 px-6"
    @unless ($event->isWeddingDay())
        x-data="countdown('{{ $event->wedding_date->toIso8601String() }}', @js([
            'days' => __('invitation.days'),
            'hours' => __('invitation.hours'),
            'minutes' => __('invitation.minutes'),
            'seconds' => __('invitation.seconds'),
        ]))"
        x-init="start()"
    @endunless
>
    <div class="max-w-5xl mx-auto text-center invitation-fade-in">
        @if ($event->isWeddingDay())
            <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">
                {{ __('invitation.today_is_the_day_eyebrow') }}
            </p>
            <h2 class="invitation-heading text-3xl sm:text-4xl text-[var(--color-text)] mb-6">
                {{ __('invitation.today_is_the_day_title') }}
            </h2>
            <p class="invitation-body text-lg text-[var(--color-text-muted)] max-w-xl mx-auto">
                {{ __('invitation.today_is_the_day_subtitle') }}
            </p>
        @else
            <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">
                {{ __('invitation.counting_down') }}
            </p>
            <h2 class="invitation-heading text-3xl sm:text-4xl text-[var(--color-text)] mb-12">
                {{ __('invitation.until_i_do') }} <span class="text-[var(--color-primary)]">{{ __('invitation.i_do') }}</span>
            </h2>

            <div class="editorial-countdown-row flex flex-wrap items-start justify-center gap-8 sm:gap-12 lg:gap-16">
                <template x-for="(item, index) in units" :key="index">
                    <div class="editorial-countdown-unit text-center min-w-[4.5rem] sm:min-w-[5.5rem]">
                        <div class="invitation-heading text-5xl sm:text-6xl lg:text-7xl font-semibold text-[var(--color-primary)] leading-none" x-text="item.value"></div>
                        <div class="mt-3 text-xs uppercase tracking-[0.25em] text-[var(--color-text-muted)]" x-text="item.label"></div>
                    </div>
                </template>
            </div>
        @endif

        @if ($event->motto)
            <p class="invitation-body text-lg text-[var(--color-text-muted)] italic mt-12 max-w-xl mx-auto">
                {{ $event->motto }}
            </p>
        @endif
    </div>
</section>

@if ($event->scheduleItems->isNotEmpty())
    {{-- Schedule: alternating timeline --}}
    <section class="invitation-section editorial-schedule py-20 px-6 bg-[var(--color-bg-soft)]/50">
        <div class="max-w-4xl mx-auto invitation-fade-in">
            <div class="text-center mb-14">
                <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">{{ __('invitation.plan_of_day') }}</p>
                <h2 class="invitation-heading text-4xl text-[var(--color-text)]">{{ __('invitation.schedule') }}</h2>
            </div>

            <div class="editorial-timeline relative">
                <div class="editorial-timeline-line absolute left-1/2 top-0 bottom-0 w-px -translate-x-1/2 bg-[color-mix(in_srgb,var(--color-primary)_35%,transparent)] hidden md:block" aria-hidden="true"></div>

                <div class="space-y-10 md:space-y-0">
                    @foreach ($event->scheduleItems as $index => $item)
                        @php
                            $isEven = $index % 2 === 0;
                        @endphp
                        <div @class([
                            'editorial-timeline-item relative md:grid md:grid-cols-2 md:gap-10 md:py-8',
                        ])>
                            <div @class([
                                'md:row-start-1',
                                'md:col-start-1 md:text-right' => $isEven,
                                'md:col-start-2 md:text-left' => ! $isEven,
                            ])>
                                <span class="invitation-heading text-2xl text-[var(--color-primary)]">
                                    {{ \Illuminate\Support\Carbon::parse($item->time)->format('H:i') }}
                                </span>
                            </div>

                            <div @class([
                                'editorial-timeline-dot absolute left-1/2 top-6 hidden md:block h-3 w-3 -translate-x-1/2 rounded-full bg-[var(--color-primary)] ring-4 ring-[var(--color-bg)]',
                            ]) aria-hidden="true"></div>

                            <div @class([
                                'mt-2 md:mt-0 md:row-start-1',
                                'md:col-start-2 md:pr-12 md:text-left' => $isEven,
                                'md:col-start-1 md:pl-12 md:text-right' => ! $isEven,
                            ])>
                                <h3 class="invitation-heading text-2xl text-[var(--color-text)] mb-1">{{ $item->title }}</h3>
                                @if ($item->description)
                                    <p class="invitation-body text-[var(--color-text-muted)]">{{ $item->description }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif

@if ($event->location_name || $event->location_address)
    @php
        $mapQuery = urlencode($event->location_address ?: $event->location_name);
        $mapSrc = $event->location_lat && $event->location_lng
            ? "https://maps.google.com/maps?q={$event->location_lat},{$event->location_lng}&z=15&output=embed"
            : "https://maps.google.com/maps?q={$mapQuery}&z=15&output=embed";
        $directionsUrl = $event->location_lat && $event->location_lng
            ? "https://www.google.com/maps/dir/?api=1&destination={$event->location_lat},{$event->location_lng}"
            : 'https://www.google.com/maps/search/?api=1&query='.$mapQuery;
    @endphp

    {{-- Location: side-by-side --}}
    <section class="invitation-section editorial-location py-20 px-6">
        <div class="max-w-6xl mx-auto invitation-fade-in">
            <div class="text-center mb-10 lg:hidden">
                <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">{{ __('invitation.find_us') }}</p>
                <h2 class="invitation-heading text-4xl text-[var(--color-text)]">{{ __('invitation.location') }}</h2>
            </div>

            <div class="editorial-location-grid grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-10 items-stretch">
                <div class="lg:col-span-3 rounded-2xl overflow-hidden border border-[color-mix(in_srgb,var(--color-text)_10%,transparent)] shadow-xl min-h-[18rem]">
                    <iframe
                        src="{{ $mapSrc }}"
                        class="w-full h-72 sm:h-96 lg:h-full min-h-[18rem] border-0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="{{ __('invitation.map_title') }}"
                    ></iframe>
                </div>

                <div class="lg:col-span-2 flex flex-col justify-center">
                    <div class="hidden lg:block mb-6">
                        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">{{ __('invitation.find_us') }}</p>
                        <h2 class="invitation-heading text-4xl text-[var(--color-text)]">{{ __('invitation.location') }}</h2>
                    </div>

                    @if ($event->location_name)
                        <h3 class="invitation-heading text-2xl sm:text-3xl text-[var(--color-text)] mb-3">{{ $event->location_name }}</h3>
                    @endif
                    @if ($event->location_address)
                        <p class="invitation-body text-[var(--color-text-muted)] mb-6">{{ $event->location_address }}</p>
                    @endif

                    <a
                        href="{{ $directionsUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 self-start text-sm uppercase tracking-[0.2em] text-[var(--color-primary)] hover:text-[var(--color-primary-dark)] transition"
                    >
                        {{ __('invitation.get_directions') }}
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endif

@if ($event->eventPhotos->isNotEmpty())
    {{-- Gallery: masonry-inspired grid --}}
    <section
        class="invitation-section editorial-gallery py-20 px-6 bg-[var(--color-bg-soft)]/50"
        x-data="{ lightbox: null, lightboxTitle: null }"
        @keydown.escape.window="lightbox = null; lightboxTitle = null"
    >
        <div class="max-w-5xl mx-auto invitation-fade-in">
            <div class="text-center mb-10">
                <h2 class="invitation-heading text-4xl text-[var(--color-text)] mb-3">{{ __('invitation.gallery') }}</h2>
                <p class="invitation-body text-[var(--color-text-muted)] max-w-lg mx-auto">{{ __('invitation.gallery_description') }}</p>
            </div>

            <div class="editorial-gallery-grid grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 auto-rows-[10rem] sm:auto-rows-[12rem]">
                @foreach ($event->eventPhotos as $index => $photo)
                    <div @class([
                        'flex flex-col gap-2',
                        'md:row-span-2' => ($index + 1) % 4 === 0,
                    ])>
                        <button
                            type="button"
                            class="group relative h-full min-h-[10rem] overflow-hidden rounded-xl border border-[color-mix(in_srgb,var(--color-text)_10%,transparent)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]"
                            @click="lightbox = @js($photo->url); lightboxTitle = @js($photo->title)"
                        >
                            <img
                                src="{{ $photo->url }}"
                                alt="{{ $photo->title ?? __('invitation.photo_alt') }}"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                                loading="lazy"
                            >
                        </button>
                        @if ($photo->title)
                            <p class="invitation-body text-sm text-center text-[var(--color-text-muted)] px-1">
                                {{ $photo->title }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div
            x-show="lightbox"
            x-transition.opacity
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4"
            @click="lightbox = null; lightboxTitle = null"
            style="display: none;"
        >
            <div class="max-w-full text-center" @click.stop>
                <img
                    :src="lightbox"
                    :alt="lightboxTitle ?? @js(__('invitation.gallery_preview'))"
                    class="max-h-[85vh] max-w-full rounded-lg shadow-2xl mx-auto"
                >
                <p
                    x-show="lightboxTitle"
                    x-text="lightboxTitle"
                    class="invitation-body text-sm text-[var(--color-text-muted)] mt-4 px-4"
                ></p>
            </div>
        </div>
    </section>
@endif

@if ($event->youtube_embed_url)
    @include('components.invitation.music-player', ['event' => $event])
@endif

@include('components.invitation.rsvp', [
    'event' => $event,
    'guest' => $guest,
    'isPersonalLink' => $isPersonalLink,
])
