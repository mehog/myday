@php
    $featureRows = [
        [
            'eyebrow' => __('landing.feature_invite_eyebrow'),
            'title' => __('landing.feature_invite_title'),
            'text' => __('landing.feature_invite_text'),
            'points' => [
                __('landing.feature_invite_point_1'),
                __('landing.feature_invite_point_2'),
                __('landing.feature_invite_point_3'),
                __('landing.feature_invite_point_4'),
            ],
            'image' => 'img/landing/hero-invitation-mobile.webp',
            'alt' => __('landing.feature_invite_alt'),
            'frame' => 'phone',
            'width' => 390,
            'height' => 844,
            'reverse' => false,
            'tint' => false,
        ],
        [
            'eyebrow' => __('landing.feature_rsvp_eyebrow'),
            'title' => __('landing.feature_rsvp_title'),
            'text' => __('landing.feature_rsvp_text'),
            'points' => [
                __('landing.feature_rsvp_point_1'),
                __('landing.feature_rsvp_point_2'),
                __('landing.feature_rsvp_point_3'),
                __('landing.feature_rsvp_point_4'),
            ],
            'image' => 'img/landing/feature-rsvp-guests-desktop.webp',
            'alt' => __('landing.feature_rsvp_alt'),
            'frame' => 'browser',
            'width' => 1600,
            'height' => 1000,
            'reverse' => true,
            'tint' => true,
        ],
        [
            'eyebrow' => __('landing.feature_seating_eyebrow'),
            'title' => __('landing.feature_seating_title'),
            'text' => __('landing.feature_seating_text'),
            'points' => [
                __('landing.feature_seating_point_1'),
                __('landing.feature_seating_point_2'),
                __('landing.feature_seating_point_3'),
            ],
            'image' => 'img/landing/feature-seating-plan-desktop.webp',
            'alt' => __('landing.feature_seating_alt'),
            'frame' => 'browser',
            'width' => 1600,
            'height' => 1000,
            'reverse' => false,
            'tint' => false,
        ],
        [
            'eyebrow' => __('landing.feature_updates_eyebrow'),
            'title' => __('landing.feature_updates_title'),
            'text' => __('landing.feature_updates_text'),
            'points' => [
                __('landing.feature_updates_point_1'),
                __('landing.feature_updates_point_2'),
                __('landing.feature_updates_point_3'),
            ],
            'image' => 'img/landing/feature-updates-insights-desktop.webp',
            'alt' => __('landing.feature_updates_alt'),
            'frame' => 'browser',
            'width' => 1600,
            'height' => 1000,
            'reverse' => true,
            'tint' => true,
        ],
    ];
@endphp

<section id="features" class="landing-section scroll-mt-20">
    @foreach ($featureRows as $feature)
        <div class="px-6 py-20 {{ $feature['tint'] ? 'bg-[#2a1f0f]/50' : '' }}">
            <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
                <div class="landing-fade-in {{ $feature['reverse'] ? 'lg:order-2' : '' }}">
                    <p class="landing-label text-xs uppercase text-[#c9a227] mb-3">
                        {{ $feature['eyebrow'] }}
                    </p>
                    <h2 class="landing-heading text-3xl sm:text-4xl text-[#faf6ee] mb-4">
                        {{ $feature['title'] }}
                    </h2>
                    <p class="landing-body text-[#d4c4a8] mb-8 leading-relaxed">
                        {{ $feature['text'] }}
                    </p>
                    <ul class="space-y-3">
                        @foreach ($feature['points'] as $point)
                            <li class="flex gap-3 items-start">
                                <span class="mt-1 w-5 h-5 rounded-full bg-[#c9a227]/20 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3 text-[#c9a227]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                                <span class="landing-body text-sm text-[#d4c4a8] leading-relaxed">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="landing-fade-in {{ $feature['reverse'] ? 'lg:order-1' : '' }}">
                    @if ($feature['frame'] === 'phone')
                        <div class="landing-phone-frame mx-auto max-w-[280px] sm:max-w-[320px]">
                            <img
                                src="{{ asset($feature['image']) }}"
                                alt="{{ $feature['alt'] }}"
                                width="{{ $feature['width'] }}"
                                height="{{ $feature['height'] }}"
                                class="w-full h-auto"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>
                    @else
                        <div class="landing-browser-frame">
                            <div class="landing-browser-chrome" aria-hidden="true">
                                <span></span><span></span><span></span>
                            </div>
                            <img
                                src="{{ asset($feature['image']) }}"
                                alt="{{ $feature['alt'] }}"
                                width="{{ $feature['width'] }}"
                                height="{{ $feature['height'] }}"
                                class="w-full h-auto"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</section>
