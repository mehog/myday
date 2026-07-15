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

        <div class="relative">
            <div class="hidden md:block absolute top-5 h-px bg-white/15" style="left: 16.5%; right: 16.5%;"></div>

            <div class="grid md:grid-cols-3 gap-8 md:gap-10">
                @foreach ([
                    ['num' => '01', 'title' => __('landing.step_1_title'), 'text' => __('landing.step_1_text')],
                    ['num' => '02', 'title' => __('landing.step_2_title'), 'text' => __('landing.step_2_text')],
                    ['num' => '03', 'title' => __('landing.step_3_title'), 'text' => __('landing.step_3_text')],
                ] as $step)
                    <div class="landing-fade-in flex md:flex-col items-start gap-4 md:gap-0">
                        <div class="relative z-10 w-10 h-10 rounded-full bg-[#1a1208] border-2 border-[#c9a227] flex items-center justify-center landing-heading text-sm font-semibold text-[#c9a227] shrink-0 md:mb-5">
                            {{ $step['num'] }}
                        </div>
                        <div>
                            <h3 class="landing-heading text-lg text-[#faf6ee] mb-1.5">{{ $step['title'] }}</h3>
                            <p class="landing-body text-sm text-[#d4c4a8] leading-relaxed">{{ $step['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
