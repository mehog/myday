<section class="landing-section px-6 py-20">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-14 landing-fade-in">
            <p class="landing-label text-xs uppercase text-[#c9a227] mb-3">
                {{ __('landing.steps_eyebrow') }}
            </p>
            <h2 class="landing-heading text-3xl sm:text-4xl text-[#1a1208] mb-4">
                {{ __('landing.steps_title') }}
            </h2>
            <p class="landing-body text-[#5c5246]">
                {{ __('landing.steps_subtitle') }}
            </p>
        </div>

        <div class="relative">
            <div class="hidden md:block absolute top-5 h-px bg-[#1a1208]/15" style="left: 12.5%; right: 12.5%;"></div>

            <div class="grid md:grid-cols-4 gap-8 md:gap-8">
                @foreach ([
                    ['num' => '01', 'title' => __('landing.step_1_title'), 'text' => __('landing.step_1_text')],
                    ['num' => '02', 'title' => __('landing.step_2_title'), 'text' => __('landing.step_2_text')],
                    ['num' => '03', 'title' => __('landing.step_3_title'), 'text' => __('landing.step_3_text')],
                    ['num' => '04', 'title' => __('landing.step_4_title'), 'text' => __('landing.step_4_text')],
                ] as $step)
                    <div class="landing-fade-in flex md:flex-col items-start gap-4 md:gap-0">
                        <div class="relative z-10 w-10 h-10 rounded-full bg-white border-2 border-[#c9a227] flex items-center justify-center landing-heading text-sm font-semibold text-[#c9a227] shrink-0 md:mb-5">
                            {{ $step['num'] }}
                        </div>
                        <div>
                            <h3 class="landing-heading text-lg text-[#1a1208] mb-1.5">{{ $step['title'] }}</h3>
                            <p class="landing-body text-sm text-[#5c5246] leading-relaxed">{{ $step['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
