@php
    $mockDate = now()->addMonths(4)->setTime(15, 0);
    $switchable = $switchable ?? false;
@endphp

<div
    @if ($switchable)
        x-data="{ style: 'classic' }"
        wire:ignore
    @endif
    class="w-full max-w-sm mx-auto"
>
    @if ($switchable)
        <div class="flex justify-center mb-5">
            <div class="landing-style-pills" role="tablist" aria-label="{{ __('landing.story_invite_styles_label') }}">
                @foreach ([
                    'classic' => __('app.template_classic'),
                    'story' => __('app.template_story'),
                    'editorial' => __('app.template_editorial'),
                ] as $style => $label)
                    <button
                        type="button"
                        role="tab"
                        class="landing-style-pill"
                        :class="style === '{{ $style }}' ? 'is-active' : ''"
                        @click="style = '{{ $style }}'"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <div
        class="landing-invite-mock {{ $switchable ? '' : 'landing-invite-mock--classic' }}"
        @if ($switchable)
            :class="'landing-invite-mock--' + style"
        @endif
        aria-hidden="true"
    >
        <p class="landing-invite-eyebrow">{{ __('landing.mock_getting_married') }}</p>
        <p class="landing-invite-names">
            {{ __('landing.mock_groom') }}
            <span class="landing-invite-amp">&</span>
            {{ __('landing.mock_bride') }}
        </p>
        <p class="landing-invite-date">{{ $mockDate->translatedFormat('j. F Y.') }}</p>

        <div
            class="landing-invite-count"
            x-data="countdown('{{ $mockDate->toIso8601String() }}', @js([
                'days' => __('invitation.days'),
                'hours' => __('invitation.hours'),
                'minutes' => __('invitation.minutes'),
            ]), 3)"
            x-init="start()"
            @unless ($switchable) wire:ignore @endunless
        >
            <template x-for="(item, index) in units" :key="index">
                <div class="landing-invite-count-unit">
                    <div class="landing-invite-count-value" x-text="item.value"></div>
                    <div class="landing-invite-count-label" x-text="item.label"></div>
                </div>
            </template>
        </div>

        <p class="landing-invite-question">
            {{ __('landing.mock_will_you_join', ['name' => __('landing.mock_guest')]) }}
        </p>
        <div class="landing-invite-actions">
            <span class="landing-invite-btn landing-invite-btn--yes">{{ __('landing.mock_yes') }}</span>
            <span class="landing-invite-btn landing-invite-btn--no">{{ __('landing.mock_no') }}</span>
        </div>
    </div>
</div>
