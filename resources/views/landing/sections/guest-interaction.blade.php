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

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
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
                <div class="landing-card rounded-2xl border border-white/15 p-6 landing-fade-in">
                    <div class="w-8 h-8 rounded-full bg-[#c9a227]/20 flex items-center justify-center mb-4">
                        <svg class="w-4 h-4 text-[#c9a227]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $feature['icon'] !!}
                        </svg>
                    </div>
                    <h3 class="landing-heading text-xl text-[#faf6ee] mb-2">{{ $feature['title'] }}</h3>
                    <p class="landing-body text-sm text-[#d4c4a8] leading-relaxed">{{ $feature['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
