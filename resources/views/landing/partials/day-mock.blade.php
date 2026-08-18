<div class="landing-day-mock" aria-hidden="true">
    <div class="landing-timeline">
        @foreach ([
            ['time' => __('landing.mock_schedule_1_time'), 'title' => __('landing.mock_schedule_1_title'), 'place' => __('landing.mock_schedule_1_place')],
            ['time' => __('landing.mock_schedule_2_time'), 'title' => __('landing.mock_schedule_2_title'), 'place' => __('landing.mock_schedule_2_place')],
            ['time' => __('landing.mock_schedule_3_time'), 'title' => __('landing.mock_schedule_3_title'), 'place' => __('landing.mock_schedule_3_place')],
        ] as $item)
            <div class="landing-timeline-item">
                <p class="landing-timeline-time">{{ $item['time'] }}</p>
                <div>
                    <p class="landing-heading text-base text-[#1a1208]">{{ $item['title'] }}</p>
                    <p class="landing-body text-sm text-[#5c5246]">{{ $item['place'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="landing-checkin-card">
        <p class="landing-label text-[0.65rem] uppercase text-[#c9a227] mb-2">{{ __('landing.mock_seating_kicker') }}</p>
        <p class="landing-heading text-lg text-[#1a1208] mb-1">{{ __('landing.mock_seating_title') }}</p>
        <p class="landing-body text-sm text-[#5c5246]">{{ __('landing.mock_seating_text') }}</p>
    </div>
</div>
