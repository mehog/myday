<section class="landing-section">
    <div class="px-6 py-20">
        <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <div class="landing-fade-in">
                <p class="landing-label text-xs uppercase text-[#c9a227] mb-3">
                    {{ __('start.story_invite_eyebrow') }}
                </p>
                <x-landing.headline
                    :lead="__('start.story_invite_title_lead')"
                    :emphasis="__('start.story_invite_title_emphasis')"
                    class="text-3xl sm:text-4xl text-[#1a1208] mb-4"
                />
                <p class="landing-body text-[#5c5246] mb-8 leading-relaxed">
                    {{ __('start.story_invite_text') }}
                </p>
                <a
                    href="#demo"
                    class="landing-btn-secondary inline-flex px-6 py-3 rounded-full landing-heading text-base transition"
                >
                    {{ __('start.story_invite_cta') }}
                </a>
            </div>
            <div class="landing-fade-in">
                @include('landing.partials.invitation-mock', ['switchable' => true])
            </div>
        </div>
    </div>

    <div class="px-6 py-20 bg-[#fafaf8]">
        <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <div class="landing-fade-in lg:order-2">
                <p class="landing-label text-xs uppercase text-[#c9a227] mb-3">
                    {{ __('start.story_rsvp_eyebrow') }}
                </p>
                <x-landing.headline
                    :lead="__('start.story_rsvp_title_lead')"
                    :emphasis="__('start.story_rsvp_title_emphasis')"
                    class="text-3xl sm:text-4xl text-[#1a1208] mb-4"
                />
                <p class="landing-body text-[#5c5246] mb-8 leading-relaxed">
                    {{ __('start.story_rsvp_text') }}
                </p>
                <ul class="space-y-3">
                    @foreach ([
                        __('start.story_rsvp_point_1'),
                        __('start.story_rsvp_point_2'),
                        __('start.story_rsvp_point_3'),
                        __('start.story_rsvp_point_4'),
                    ] as $point)
                        <li class="flex gap-3 items-start">
                            <span class="mt-1 w-1.5 h-1.5 rounded-full bg-[#c9a227] shrink-0"></span>
                            <span class="landing-body text-sm text-[#5c5246] leading-relaxed">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="landing-fade-in lg:order-1">
                @include('landing.partials.dashboard-mock')
            </div>
        </div>
    </div>

    <div class="px-6 py-20">
        <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <div class="landing-fade-in">
                <p class="landing-label text-xs uppercase text-[#c9a227] mb-3">
                    {{ __('start.story_day_eyebrow') }}
                </p>
                <x-landing.headline
                    :lead="__('start.story_day_title_lead')"
                    :emphasis="__('start.story_day_title_emphasis')"
                    class="text-3xl sm:text-4xl text-[#1a1208] mb-4"
                />
                <p class="landing-body text-[#5c5246] leading-relaxed">
                    {{ __('start.story_day_text') }}
                </p>
            </div>
            <div class="landing-fade-in">
                @include('landing.partials.day-mock')
            </div>
        </div>
    </div>

    <div class="px-6 py-20 bg-[#fafaf8]">
        <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <div class="landing-fade-in lg:order-2">
                <p class="landing-label text-xs uppercase text-[#c9a227] mb-3">
                    {{ __('start.story_after_eyebrow') }}
                </p>
                <x-landing.headline
                    :lead="__('start.story_after_title_lead')"
                    :emphasis="__('start.story_after_title_emphasis')"
                    class="text-3xl sm:text-4xl text-[#1a1208] mb-4"
                />
                <p class="landing-body text-[#5c5246] mb-8 leading-relaxed">
                    {{ __('start.story_after_text') }}
                </p>
                <ul class="space-y-3 mb-10">
                    @foreach ([
                        __('start.story_after_point_1'),
                        __('start.story_after_point_2'),
                        __('start.story_after_point_3'),
                    ] as $point)
                        <li class="flex gap-3 items-start">
                            <span class="mt-1 w-1.5 h-1.5 rounded-full bg-[#c9a227] shrink-0"></span>
                            <span class="landing-body text-sm text-[#5c5246] leading-relaxed">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="landing-journey">
                    @foreach ([
                        ['title' => __('start.journey_before'), 'text' => __('start.journey_before_text')],
                        ['title' => __('start.journey_during'), 'text' => __('start.journey_during_text')],
                        ['title' => __('start.journey_after'), 'text' => __('start.journey_after_text')],
                    ] as $step)
                        <div>
                            <p class="landing-label text-[0.65rem] uppercase text-[#c9a227] mb-1">{{ $step['title'] }}</p>
                            <p class="landing-body text-sm text-[#5c5246]">{{ $step['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="landing-fade-in lg:order-1">
                @include('landing.partials.memories-mock')
            </div>
        </div>
    </div>
</section>
