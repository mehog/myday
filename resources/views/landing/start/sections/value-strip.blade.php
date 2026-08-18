<section class="landing-section px-6 py-16 sm:py-20">
    <div class="max-w-6xl mx-auto landing-value-strip landing-fade-in">
        @foreach ([
            ['title' => __('start.value_1_title'), 'text' => __('start.value_1_text')],
            ['title' => __('start.value_2_title'), 'text' => __('start.value_2_text')],
            ['title' => __('start.value_3_title'), 'text' => __('start.value_3_text')],
            ['title' => __('start.value_4_title'), 'text' => __('start.value_4_text')],
        ] as $item)
            <div>
                <span class="landing-value-kicker landing-label">{{ $item['title'] }}</span>
                <p class="landing-heading text-2xl text-[#1a1208] leading-snug">{{ $item['text'] }}</p>
            </div>
        @endforeach
    </div>
</section>
