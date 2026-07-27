<section class="landing-section px-6 py-16 sm:py-20 bg-[#2a1f0f]/50">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12 landing-fade-in">
            <h2 class="landing-heading text-3xl sm:text-4xl text-[#faf6ee] mb-4">
                {{ __('landing.benefits_title') }}
            </h2>
            <p class="landing-body text-[#d4c4a8] max-w-2xl mx-auto">
                {{ __('landing.benefits_subtitle') }}
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ([
                [
                    'title' => __('landing.benefit_1_title'),
                    'text' => __('landing.benefit_1_text'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5.586a1 1 0 01.707.293l6.414 6.414a1 1 0 010 1.414l-5.586 5.586a1 1 0 01-1.414 0L6.293 10.293A1 1 0 016 9.586V4a1 1 0 011-1z"/>',
                ],
                [
                    'title' => __('landing.benefit_3_title'),
                    'text' => __('landing.benefit_3_text'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                ],
                [
                    'title' => __('landing.benefit_memories_title'),
                    'text' => __('landing.benefit_memories_text'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
                ],
                [
                    'title' => __('landing.benefit_4_title'),
                    'text' => __('landing.benefit_4_text'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
                ],
            ] as $benefit)
                <div class="landing-fade-in">
                    <div class="w-9 h-9 rounded-full bg-[#c9a227]/20 flex items-center justify-center mb-4">
                        <svg class="w-4 h-4 text-[#c9a227]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $benefit['icon'] !!}
                        </svg>
                    </div>
                    <h3 class="landing-heading text-lg text-[#faf6ee] mb-2">{{ $benefit['title'] }}</h3>
                    <p class="landing-body text-sm text-[#d4c4a8] leading-relaxed">{{ $benefit['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
