<section class="landing-section px-6 py-20">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-14 landing-fade-in">
            <h2 class="landing-heading text-3xl sm:text-4xl text-[#faf6ee] mb-4">
                {{ __('landing.interaction_title') }}
            </h2>
            <p class="landing-body text-[#d4c4a8]">
                {{ __('landing.interaction_subtitle') }}
            </p>
        </div>

        <div class="grid lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)] gap-10 lg:gap-14 items-center">
            <div class="order-2 lg:order-1 landing-fade-in">
                <div class="rounded-2xl border border-[#c9a227]/25 bg-[#2a1f0f] p-6 max-w-sm mx-auto lg:mx-0 space-y-5">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#c9a227]/20 flex items-center justify-center landing-heading text-sm text-[#c9a227] shrink-0">
                            {{ mb_substr(__('landing.interaction_demo_name'), 0, 1) }}
                        </div>
                        <div>
                            <p class="landing-heading text-sm text-[#faf6ee] mb-1">{{ __('landing.interaction_demo_name') }}</p>
                            <p class="landing-body text-sm text-[#d4c4a8] leading-relaxed">&ldquo;{{ __('landing.interaction_demo_message') }}&rdquo;</p>
                        </div>
                    </div>

                    <div class="h-px bg-white/10"></div>

                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#c9a227]/20 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-[#c9a227]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="landing-heading text-sm text-[#faf6ee] mb-1.5">{{ __('landing.interaction_demo_voice_label') }}</p>
                            <div class="h-1.5 rounded-full bg-white/10">
                                <div class="h-1.5 w-2/3 rounded-full bg-[#c9a227]"></div>
                            </div>
                        </div>
                    </div>

                    <div class="h-px bg-white/10"></div>

                    <div class="grid grid-cols-3 gap-2">
                        <div class="aspect-square rounded-lg bg-[#1a1208] border border-white/10"></div>
                        <div class="aspect-square rounded-lg bg-[#1a1208] border border-white/10"></div>
                        <div class="aspect-square rounded-lg bg-[#1a1208] border border-white/10"></div>
                    </div>
                </div>
            </div>

            <div class="order-1 lg:order-2 space-y-8 landing-fade-in">
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
                ] as $feature)
                    <div class="flex gap-4">
                        <div class="w-9 h-9 rounded-full bg-[#c9a227]/20 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-[#c9a227]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $feature['icon'] !!}
                            </svg>
                        </div>
                        <div>
                            <h3 class="landing-heading text-lg text-[#faf6ee] mb-1">{{ $feature['title'] }}</h3>
                            <p class="landing-body text-sm text-[#d4c4a8] leading-relaxed">{{ $feature['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
