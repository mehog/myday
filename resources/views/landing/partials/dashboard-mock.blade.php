@php
    $guests = [
        [
            'name' => __('landing.mock_guest_1_name'),
            'meta' => __('landing.mock_guest_1_meta'),
            'status' => __('landing.mock_status_attending'),
            'tone' => 'yes',
        ],
        [
            'name' => __('landing.mock_guest_2_name'),
            'meta' => __('landing.mock_guest_2_meta'),
            'status' => __('landing.mock_status_pending'),
            'tone' => 'pending',
        ],
        [
            'name' => __('landing.mock_guest_3_name'),
            'meta' => __('landing.mock_guest_3_meta'),
            'status' => __('landing.mock_status_attending'),
            'tone' => 'yes',
        ],
        [
            'name' => __('landing.mock_guest_4_name'),
            'meta' => __('landing.mock_guest_4_meta'),
            'status' => __('landing.mock_status_declined'),
            'tone' => 'no',
        ],
    ];
@endphp

<div class="landing-dash-mock" aria-hidden="true">
    <div class="landing-dash-head">
        <p class="landing-heading text-lg text-[#1a1208]">
            {{ __('landing.mock_groom') }} & {{ __('landing.mock_bride') }}
        </p>
        <span class="landing-dash-live">{{ __('landing.mock_live') }}</span>
    </div>

    <div class="landing-dash-stats">
        @foreach ([
            ['value' => '200', 'label' => __('landing.mock_stat_guests')],
            ['value' => '142', 'label' => __('landing.mock_stat_attending')],
            ['value' => '32', 'label' => __('landing.mock_stat_pending')],
            ['value' => '26', 'label' => __('landing.mock_stat_declined')],
        ] as $stat)
            <div class="landing-dash-stat">
                <div class="landing-dash-stat-value">{{ $stat['value'] }}</div>
                <div class="landing-dash-stat-label">{{ $stat['label'] }}</div>
            </div>
        @endforeach
    </div>

    @foreach ($guests as $guest)
        @php
            $parts = preg_split('/\s+/', trim($guest['name']));
            $initials = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1).mb_substr($parts[1] ?? '', 0, 1));
        @endphp
        <div class="landing-dash-row">
            <span class="landing-dash-avatar">{{ $initials }}</span>
            <div class="min-w-0">
                <p class="text-sm font-medium text-[#1a1208] truncate">{{ $guest['name'] }}</p>
                <p class="text-xs text-[#5c5246] truncate">{{ $guest['meta'] }}</p>
            </div>
            <span class="landing-dash-status landing-dash-status--{{ $guest['tone'] }}">{{ $guest['status'] }}</span>
        </div>
    @endforeach
</div>
