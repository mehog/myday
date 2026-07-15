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
                [
                    'title' => __('landing.benefit_1_title'),
                    'text' => __('landing.benefit_1_text'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5.586a1 1 0 01.707.293l6.414 6.414a1 1 0 010 1.414l-5.586 5.586a1 1 0 01-1.414 0L6.293 10.293A1 1 0 016 9.586V4a1 1 0 011-1z"/>',
                ],
                [
                    'title' => __('landing.benefit_2_title'),
                    'text' => __('landing.benefit_2_text'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>',
                ],
                [
                    'title' => __('landing.benefit_3_title'),
                    'text' => __('landing.benefit_3_text'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
                ],
                [
                    'title' => __('landing.benefit_4_title'),
                    'text' => __('landing.benefit_4_text'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
                ],
            ] as $benefit)
                <div class="landing-card rounded-2xl border border-white/15 p-6 landing-fade-in">
                    <div class="w-8 h-8 rounded-full bg-[#c9a227]/20 flex items-center justify-center mb-4">
                        <svg class="w-4 h-4 text-[#c9a227]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $benefit['icon'] !!}
                        </svg>
                    </div>
                    <h3 class="landing-heading text-xl text-[#faf6ee] mb-2">{{ $benefit['title'] }}</h3>
                    <p class="landing-body text-sm text-[#d4c4a8] leading-relaxed">{{ $benefit['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
