<section class="landing-section px-6 pt-20 pb-28 sm:pb-32 bg-[#fafaf8]">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14 landing-fade-in">
            <p class="landing-label text-xs uppercase text-[#c9a227] mb-3">
                {{ __('landing.interaction_eyebrow') }}
            </p>
            <h2 class="landing-heading text-3xl sm:text-4xl text-[#1a1208] mb-4">
                {{ __('landing.interaction_title') }}
            </h2>
            <p class="landing-body text-[#5c5246] max-w-2xl mx-auto">
                {{ __('landing.interaction_subtitle') }}
            </p>
        </div>

        <div class="grid lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)] gap-10 lg:gap-14 items-center">
            <div class="landing-fade-in space-y-8 order-2 lg:order-1">
                @foreach ([
                    [
                        'title' => __('landing.interaction_1_title'),
                        'text' => __('landing.interaction_1_text'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                    ],
                    [
                        'title' => __('landing.interaction_2_title'),
                        'text' => __('landing.interaction_2_text'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>',
                    ],
                    [
                        'title' => __('landing.interaction_3_title'),
                        'text' => __('landing.interaction_3_text'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>',
                    ],
                    [
                        'title' => __('landing.interaction_4_title'),
                        'text' => __('landing.interaction_4_text'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>',
                    ],
                ] as $feature)
                    <div class="flex gap-4">
                        <div class="w-9 h-9 rounded-full bg-[#c9a227]/20 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-[#c9a227]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                {!! $feature['icon'] !!}
                            </svg>
                        </div>
                        <div>
                            <h3 class="landing-heading text-lg text-[#1a1208] mb-1">{{ $feature['title'] }}</h3>
                            <p class="landing-body text-sm text-[#5c5246] leading-relaxed">{{ $feature['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="landing-fade-in relative order-1 lg:order-2">
                <div class="landing-browser-frame">
                    <div class="landing-browser-chrome" aria-hidden="true">
                        <span></span><span></span><span></span>
                    </div>
                    <img
                        src="{{ asset(\App\Support\LandingAsset::path('feature-messages-desktop.webp')) }}"
                        alt="{{ __('landing.interaction_inbox_alt') }}"
                        width="1600"
                        height="1000"
                        class="w-full h-auto"
                        loading="lazy"
                        decoding="async"
                    >
                </div>
                <div class="landing-phone-frame absolute -bottom-6 -left-2 sm:left-4 w-[38%] max-w-[180px] shadow-2xl">
                    <img
                        src="{{ asset(\App\Support\LandingAsset::path('feature-guest-upload-mobile.webp')) }}"
                        alt="{{ __('landing.interaction_upload_alt') }}"
                        width="390"
                        height="844"
                        class="w-full h-auto"
                        loading="lazy"
                        decoding="async"
                    >
                </div>
            </div>
        </div>
    </div>
</section>
