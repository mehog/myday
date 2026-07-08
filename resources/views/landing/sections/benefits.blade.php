<section class="landing-section px-6 py-20 bg-[#2a1f0f]/50">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-14 landing-fade-in">
            <h2 class="landing-heading text-3xl sm:text-4xl text-[#faf6ee] mb-4">
                {{ __('landing.benefits_title') }}
            </h2>
            <p class="landing-body text-[#d4c4a8]">
                {{ __('landing.benefits_subtitle') }}
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-2 gap-6">
            @foreach ([
                ['title' => __('landing.benefit_1_title'), 'text' => __('landing.benefit_1_text')],
                ['title' => __('landing.benefit_2_title'), 'text' => __('landing.benefit_2_text')],
                ['title' => __('landing.benefit_3_title'), 'text' => __('landing.benefit_3_text')],
                ['title' => __('landing.benefit_4_title'), 'text' => __('landing.benefit_4_text')],
            ] as $benefit)
                <div class="landing-card rounded-2xl border border-white/15 p-6 landing-fade-in">
                    <div class="w-8 h-8 rounded-full bg-[#c9a227]/20 flex items-center justify-center mb-4">
                        <svg class="w-4 h-4 text-[#c9a227]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="landing-heading text-xl text-[#faf6ee] mb-2">{{ $benefit['title'] }}</h3>
                    <p class="landing-body text-sm text-[#d4c4a8] leading-relaxed">{{ $benefit['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
