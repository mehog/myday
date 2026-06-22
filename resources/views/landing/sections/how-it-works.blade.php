<section class="landing-section px-6 py-20">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-14 landing-fade-in">
            <h2 class="landing-heading text-3xl sm:text-4xl text-[#faf6ee] mb-4">
                {{ __('landing.steps_title') }}
            </h2>
            <p class="landing-body text-[#d4c4a8]">
                {{ __('landing.steps_subtitle') }}
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            @foreach ([
                ['num' => '01', 'title' => __('landing.step_1_title'), 'text' => __('landing.step_1_text')],
                ['num' => '02', 'title' => __('landing.step_2_title'), 'text' => __('landing.step_2_text')],
                ['num' => '03', 'title' => __('landing.step_3_title'), 'text' => __('landing.step_3_text')],
            ] as $step)
                <div class="landing-card rounded-2xl border border-white/15 p-6 text-center landing-fade-in">
                    <div class="landing-heading text-5xl font-light text-[#c9a227]/30 mb-4">{{ $step['num'] }}</div>
                    <h3 class="landing-heading text-xl text-[#faf6ee] mb-3">{{ $step['title'] }}</h3>
                    <p class="landing-body text-sm text-[#d4c4a8] leading-relaxed">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
