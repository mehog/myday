@php
    $heroUrl = $event->hero_image_url;
    $hasSchedule = $event->scheduleItems->isNotEmpty();
    $hasLocation = (bool) ($event->location_name || $event->location_address);
    $hasGallery = $event->eventPhotos->isNotEmpty();
    $hasMusic = (bool) $event->youtube_embed_url;

    $slideCount = 3
        + ($hasSchedule ? 1 : 0)
        + ($hasLocation ? 1 : 0)
        + ($hasGallery ? 1 : 0)
        + ($hasMusic ? 1 : 0);

    $durations = [10000, 5000];

    if ($hasSchedule) {
        $durations[] = 8000;
    }

    if ($hasLocation) {
        $durations[] = 6000;
    }

    if ($hasGallery) {
        $durations[] = 8000;
    }

    if ($hasMusic) {
        $durations[] = 10000;
    }

    $durations[] = -1;

    if ($hasLocation) {
        $mapQuery = urlencode($event->location_address ?: $event->location_name);
        $mapSrc = $event->location_lat && $event->location_lng
            ? "https://maps.google.com/maps?q={$event->location_lat},{$event->location_lng}&z=15&output=embed"
            : "https://maps.google.com/maps?q={$mapQuery}&z=15&output=embed";
        $directionsUrl = $event->location_lat && $event->location_lng
            ? "https://www.google.com/maps/dir/?api=1&destination={$event->location_lat},{$event->location_lng}"
            : 'https://www.google.com/maps/search/?api=1&query='.$mapQuery;
    }
@endphp

<div
    class="story-wrap"
    @story-modal-open.window="modalOpen = true; clearAdvTimer()"
    @story-modal-close.window="modalOpen = false; animKey++; startAdvTimer()"
    x-data="{
        current: 0,
        total: {{ $slideCount }},
        held: false,
        modalOpen: false,
        holdTimer: null,
        advTimer: null,
        animKey: 0,
        durations: @js($durations),
        TAP_THRESHOLD: 150,
        swipeStartX: 0,
        swipeStartY: 0,
        SWIPE_THRESHOLD: 50,

        init() {
            const el = this.$el;
            const INTERACTIVE = 'button, a, input, textarea, select';

            el.addEventListener('touchstart', (e) => {
                if (e.target.closest(INTERACTIVE)) {
                    return;
                }

                this.swipeStartX = e.touches[0].clientX;
                this.swipeStartY = e.touches[0].clientY;
                this.holdTimer = setTimeout(() => {
                    this.held = true;
                    this.clearAdvTimer();
                }, this.TAP_THRESHOLD);
            }, { passive: true });

            el.addEventListener('touchmove', (e) => {
                if (!this.holdTimer && !this.held) {
                    return;
                }

                const dx = e.touches[0].clientX - this.swipeStartX;
                const dy = e.touches[0].clientY - this.swipeStartY;

                if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 8) {
                    e.preventDefault();
                }
            }, { passive: false });

            el.addEventListener('touchend', (e) => {
                const wasHeld = this.held;
                const endX = e.changedTouches[0].clientX;
                const deltaX = endX - this.swipeStartX;
                this.held = false;
                clearTimeout(this.holdTimer);
                this.holdTimer = null;

                if (Math.abs(deltaX) >= this.SWIPE_THRESHOLD) {
                    if (deltaX < 0) {
                        this.next();
                    } else {
                        this.goTo(this.current - 1);
                    }
                } else if (wasHeld) {
                    this.animKey++;
                    this.startAdvTimer();
                }
            }, { passive: true });

            el.addEventListener('touchcancel', () => {
                this.held = false;
                clearTimeout(this.holdTimer);
                this.holdTimer = null;
                this.startAdvTimer();
            }, { passive: true });

            el.addEventListener('mousedown', (e) => {
                if (e.target.closest(INTERACTIVE)) {
                    return;
                }

                this.swipeStartX = e.clientX;
                this.holdTimer = setTimeout(() => {
                    this.held = true;
                    this.clearAdvTimer();
                }, this.TAP_THRESHOLD);
            });

            el.addEventListener('mouseup', (e) => {
                const wasHeld = this.held;
                const deltaX = e.clientX - this.swipeStartX;
                this.held = false;
                clearTimeout(this.holdTimer);
                this.holdTimer = null;

                if (Math.abs(deltaX) >= this.SWIPE_THRESHOLD) {
                    if (deltaX < 0) {
                        this.next();
                    } else {
                        this.goTo(this.current - 1);
                    }
                } else if (wasHeld) {
                    this.animKey++;
                    this.startAdvTimer();
                }
            });

            el.addEventListener('mouseleave', () => {
                if (this.held || this.holdTimer) {
                    this.held = false;
                    clearTimeout(this.holdTimer);
                    this.holdTimer = null;
                    this.startAdvTimer();
                }
            });

            this.startAdvTimer();
        },

        goTo(index) {
            if (index < 0 || index >= this.total) {
                return;
            }

            this.current = index;
            this.animKey++;
            this.clearAdvTimer();
            this.startAdvTimer();
        },

        next() {
            if (this.current < this.total - 1) {
                this.goTo(this.current + 1);
            }
        },

        startAdvTimer() {
            this.clearAdvTimer();

            const duration = this.durations[this.current];

            if (this.held || this.modalOpen || duration < 0) {
                return;
            }

            this.advTimer = setTimeout(() => this.next(), duration);
        },

        clearAdvTimer() {
            if (this.advTimer) {
                clearTimeout(this.advTimer);
                this.advTimer = null;
            }
        },
    }"
>
    <div class="story-progress" aria-hidden="true">
        @for ($i = 0; $i < $slideCount; $i++)
            <button
                type="button"
                class="story-progress-segment"
                aria-label="Slide {{ $i + 1 }}"
                @pointerdown.stop
                @pointerup.stop
                @click.stop="goTo({{ $i }})"
            >
                <span
                    class="story-progress-fill"
                    :key="'story-fill-{{ $i }}-' + (current === {{ $i }} ? animKey : 0)"
                    :class="{
                        'is-complete': current > {{ $i }},
                        'is-active': current === {{ $i }},
                        'is-held': current === {{ $i }} && held,
                    }"
                    :style="durations[{{ $i }}] > 0
                        ? '--story-dur: ' + durations[{{ $i }}] + 'ms'
                        : '--story-dur: 0ms'"
                ></span>
            </button>
        @endfor
    </div>

    <button
        type="button"
        class="story-nav story-nav-prev"
        x-show="current > 0"
        @pointerdown.stop
        @pointerup.stop
        @click.stop="goTo(current - 1)"
        aria-label="Previous slide"
    >
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>

    <button
        type="button"
        class="story-nav story-nav-next"
        x-show="current < total - 1"
        @pointerdown.stop
        @pointerup.stop
        @click.stop="goTo(current + 1)"
        aria-label="Next slide"
    >
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    <div class="story-viewport">
        <div
            class="story-track"
            :class="{ 'story-track-held': held }"
            :style="{ transform: 'translateX(calc(' + (-current) + ' * 100vw))' }"
        >
            {{-- Slide 1: Hero --}}
            <section class="story-slide story-slide-hero">
                @if ($heroUrl)
                    <img
                        src="{{ $heroUrl }}"
                        alt="{{ $event->couple_names }}"
                        class="story-slide-bg"
                    >
                @else
                    <div class="story-slide-bg story-slide-bg-fallback"></div>
                @endif

                <div class="story-slide-overlay"></div>

                <div class="story-slide-content story-slide-content-bottom">
                    <p class="story-eyebrow">{{ __('invitation.save_the_date') }}</p>
                    <h1 class="story-title invitation-heading">
                        {{ $event->groom_name }}
                        <span class="story-amp">&</span>
                        {{ $event->bride_name }}
                    </h1>
                    <p class="story-subtitle invitation-body">
                        {{ $event->wedding_date->translatedFormat('l, j. F Y.') }}
                    </p>
                    @if ($event->location_name)
                        <p class="story-meta invitation-body">{{ $event->location_name }}</p>
                    @endif
                </div>
            </section>

            {{-- Slide 2: Countdown --}}
            <section
                class="story-slide story-slide-countdown"
                x-data="countdown('{{ $event->wedding_date->toIso8601String() }}', @js([
                    'days' => __('invitation.days'),
                    'hours' => __('invitation.hours'),
                    'minutes' => __('invitation.minutes'),
                    'seconds' => __('invitation.seconds'),
                ]))"
                x-init="start()"
            >
                <div class="story-slide-content story-slide-content-center">
                    <p class="story-eyebrow story-eyebrow-muted">{{ __('invitation.counting_down') }}</p>
                    <h2 class="story-section-title invitation-heading">
                        {{ __('invitation.until_i_do') }}
                        <span class="text-[var(--color-primary)]">{{ __('invitation.i_do') }}</span>
                    </h2>

                    <div class="story-countdown-grid">
                        <template x-for="(item, index) in units" :key="index">
                            <div class="story-countdown-unit">
                                <div class="story-countdown-value invitation-heading" x-text="item.value"></div>
                                <div class="story-countdown-label" x-text="item.label"></div>
                            </div>
                        </template>
                    </div>

                    @if ($event->motto)
                        <p class="story-motto invitation-body">{{ $event->motto }}</p>
                    @endif
                </div>
            </section>

            @if ($hasSchedule)
                <section class="story-slide story-slide-schedule">
                    <div class="story-slide-content story-slide-content-center">
                        <p class="story-eyebrow story-eyebrow-muted">{{ __('invitation.plan_of_day') }}</p>
                        <h2 class="story-section-title invitation-heading mb-10">{{ __('invitation.schedule') }}</h2>

                        <div class="story-schedule-list">
                            @foreach ($event->scheduleItems as $item)
                                <div class="story-schedule-item">
                                    <span class="story-schedule-time invitation-heading">
                                        {{ \Illuminate\Support\Carbon::parse($item->time)->format('H:i') }}
                                    </span>
                                    <div class="story-schedule-body">
                                        <h3 class="story-schedule-title invitation-heading">{{ $item->title }}</h3>
                                        @if ($item->description)
                                            <p class="story-schedule-desc invitation-body">{{ $item->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            @if ($hasLocation)
                <section class="story-slide story-slide-location">
                    <iframe
                        src="{{ $mapSrc }}"
                        class="story-slide-bg story-location-map"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="{{ __('invitation.map_title') }}"
                    ></iframe>

                    <div class="story-slide-overlay story-slide-overlay-strong"></div>

                    <div class="story-location-card">
                        <p class="story-eyebrow">{{ __('invitation.find_us') }}</p>
                        @if ($event->location_name)
                            <h2 class="story-location-name invitation-heading">{{ $event->location_name }}</h2>
                        @endif
                        @if ($event->location_address)
                            <p class="story-location-address invitation-body">{{ $event->location_address }}</p>
                        @endif
                        <a
                            href="{{ $directionsUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="story-directions-btn"
                            @pointerdown.stop
                            @pointerup.stop
                        >
                            {{ __('invitation.get_directions') }}
                        </a>
                    </div>
                </section>
            @endif

            @if ($hasGallery)
                <section
                    class="story-slide story-slide-gallery"
                    x-data="{ lightbox: null, lightboxTitle: null }"
                    x-init="$watch('lightbox', v => v ? $dispatch('story-modal-open') : $dispatch('story-modal-close'))"
                    @keydown.escape.window="lightbox = null; lightboxTitle = null"
                >
                    <div class="story-slide-content story-slide-content-top">
                        <p class="story-eyebrow story-eyebrow-muted">{{ __('invitation.gallery') }}</p>
                        <p class="story-gallery-desc invitation-body">{{ __('invitation.gallery_description') }}</p>
                    </div>

                    <div class="story-gallery-grid">
                        @foreach ($event->eventPhotos as $photo)
                            <button
                                type="button"
                                class="story-gallery-item"
                                @pointerdown.stop
                                @pointerup.stop
                                @click.stop="lightbox = @js($photo->url); lightboxTitle = @js($photo->title)"
                            >
                                <img
                                    src="{{ $photo->url }}"
                                    alt="{{ $photo->title ?? __('invitation.photo_alt') }}"
                                    class="story-gallery-img"
                                    loading="lazy"
                                >
                                @if ($photo->title)
                                    <span class="story-gallery-caption invitation-body">{{ $photo->title }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    <div
                        x-teleport="body"
                        x-show="lightbox"
                        x-transition.opacity
                        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4"
                        @pointerdown.stop
                        @pointerup.stop
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

            @if ($hasMusic)
                <section
                    class="story-slide story-slide-music"
                    @pointerdown.stop
                    @pointerup.stop
                >
                    @include('components.invitation.music-player', ['event' => $event])
                </section>
            @endif

            <section
                class="story-slide story-slide-rsvp"
                @pointerdown.stop
                @pointerup.stop
            >
                @include('components.invitation.rsvp', [
                    'event' => $event,
                    'guest' => $guest,
                    'isPersonalLink' => $isPersonalLink,
                ])
            </section>
        </div>
    </div>

    <div
        class="story-footer"
        x-show="current === total - 1"
        x-transition.opacity
        @pointerdown.stop
        @pointerup.stop
        style="display: none;"
    >
        <a href="{{ route('home') }}" class="shrink-0">
            <img
                src="{{ asset('icons/nd-logo-transparent.webp') }}"
                alt="{{ config('app.name', 'NasDan') }}"
                class="max-w-[50px] w-full h-auto"
                style="max-width: 50px;"
            >
        </a>
        <x-locale-picker
            class="justify-end"
            selectClass="text-sm py-1.5 px-3 min-w-[9rem] cursor-pointer rounded-xl border border-[color-mix(in_srgb,var(--color-primary)_40%,transparent)] bg-[var(--color-bg-soft)] text-[var(--color-text)]"
            labelClass="text-sm text-[var(--color-text-muted)]"
        />
    </div>
</div>
