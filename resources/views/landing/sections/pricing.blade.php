<section id="cijene" class="landing-section px-6 py-20 bg-[#2a1f0f]/50 scroll-mt-20">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-14 landing-fade-in">
            <h2 class="landing-heading text-3xl sm:text-4xl text-[#faf6ee] mb-4">
                {{ __('landing.pricing_title') }}
            </h2>
            <p class="landing-body text-[#d4c4a8]">
                {{ __('landing.pricing_subtitle') }}
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 mb-14">
            @foreach ([
                [
                    'name' => __('landing.pricing_plan_basic_name'),
                    'guests' => __('landing.pricing_plan_basic_guests'),
                    'price' => __('landing.pricing_plan_basic_price'),
                    'highlighted' => false,
                ],
                [
                    'name' => __('landing.pricing_plan_plus_name'),
                    'guests' => __('landing.pricing_plan_plus_guests'),
                    'price' => __('landing.pricing_plan_plus_price'),
                    'highlighted' => true,
                ],
                [
                    'name' => __('landing.pricing_plan_premium_name'),
                    'guests' => __('landing.pricing_plan_premium_guests'),
                    'price' => __('landing.pricing_plan_premium_price'),
                    'highlighted' => false,
                ],
            ] as $plan)
                <div @class([
                    'landing-card rounded-2xl p-6 sm:p-8 landing-fade-in relative flex flex-col',
                    'border-2 border-[#c9a227]/60 bg-[#c9a227]/5' => $plan['highlighted'],
                    'border border-white/15' => ! $plan['highlighted'],
                ])>
                    @if ($plan['highlighted'])
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-[#c9a227] text-[#1a1208] text-xs font-medium uppercase tracking-wider">
                            {{ __('landing.pricing_plan_popular') }}
                        </span>
                    @endif

                    <div class="text-center mb-6">
                        <h3 class="landing-heading text-2xl text-[#faf6ee] mb-2">{{ $plan['name'] }}</h3>
                        <p class="landing-body text-sm text-[#d4c4a8] mb-4">{{ $plan['guests'] }}</p>
                        <p class="landing-heading text-4xl sm:text-5xl text-[#c9a227]">{{ $plan['price'] }}</p>
                    </div>

                    <a
                        href="#naruči"
                        @class([
                            'mt-auto w-full py-4 rounded-xl landing-heading text-lg transition text-center',
                            'landing-btn-primary' => $plan['highlighted'],
                            'landing-btn-secondary' => ! $plan['highlighted'],
                        ])
                    >
                        {{ __('landing.pricing_cta') }}
                    </a>
                </div>
            @endforeach
        </div>

        <div class="landing-card rounded-2xl border border-white/15 p-6 sm:p-8 landing-fade-in">
            <h3 class="landing-heading text-xl text-[#faf6ee] mb-6 text-center">
                {{ __('landing.pricing_features_title') }}
            </h3>

            <ul class="grid sm:grid-cols-2 gap-x-8 gap-y-4">
                @foreach (range(1, 14) as $i)
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 shrink-0 w-5 h-5 rounded-full bg-[#c9a227]/20 flex items-center justify-center">
                            <svg class="w-3 h-3 text-[#c9a227]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <span class="landing-body text-sm text-[#d4c4a8] leading-relaxed">
                            {{ __('landing.pricing_feature_' . $i) }}
                        </span>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8 text-center">
                <a
                    href="{{ route('onboarding', ['locale' => app()->getLocale()]) }}"
                    class="landing-btn-secondary inline-block px-8 py-4 rounded-xl landing-heading text-lg transition"
                >
                    {{ __('landing.hero_cta_create') }}
                </a>
            </div>
        </div>
    </div>
</section>
