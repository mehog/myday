<section id="features" class="landing-section px-6 py-16 sm:py-20 bg-[#fafaf8] scroll-mt-20">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12 landing-fade-in">
            <x-landing.headline
                :lead="__('landing.benefits_title_lead')"
                :emphasis="__('landing.benefits_title_emphasis')"
                class="text-3xl sm:text-4xl text-[#1a1208] mb-4"
            />
            <p class="landing-body text-[#5c5246] max-w-2xl mx-auto">
                {{ __('landing.benefits_subtitle') }}
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach ([
                ['title' => __('landing.pillar_1_title'), 'text' => __('landing.pillar_1_text')],
                ['title' => __('landing.pillar_2_title'), 'text' => __('landing.pillar_2_text')],
                ['title' => __('landing.pillar_3_title'), 'text' => __('landing.pillar_3_text')],
                ['title' => __('landing.pillar_4_title'), 'text' => __('landing.pillar_4_text')],
            ] as $pillar)
                <div class="landing-fade-in">
                    <h3 class="landing-heading text-xl text-[#1a1208] mb-2">{{ $pillar['title'] }}</h3>
                    <p class="landing-body text-sm text-[#5c5246] leading-relaxed">{{ $pillar['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
