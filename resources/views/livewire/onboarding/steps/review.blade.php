<div>
    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-2 text-center">
        {{ __('onboarding.review_title') }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-6 text-center text-sm">
        {{ __('onboarding.review_subtitle') }}
    </p>

    <div class="space-y-4 mb-6">
        <div class="rounded-xl border border-[#1a1208]/10 bg-[#fafaf8] p-5">
            <h2 class="text-sm uppercase tracking-wider text-[#c9a227] mb-3">{{ __('onboarding.review_couple') }}</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-[#5c5246]">{{ __('onboarding.groom_name') }}</dt>
                    <dd class="text-[#1a1208] text-right">{{ $groom_name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-[#5c5246]">{{ __('onboarding.bride_name') }}</dt>
                    <dd class="text-[#1a1208] text-right">{{ $bride_name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-[#5c5246]">{{ __('onboarding.review_wedding_date') }}</dt>
                    <dd class="text-[#1a1208] text-right">{{ $wedding_date }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-[#5c5246]">{{ __('onboarding.review_theme') }}</dt>
                    <dd class="text-[#1a1208] text-right">{{ $selectedTheme?->label() }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-[#5c5246]">{{ __('onboarding.review_template') }}</dt>
                    <dd class="text-[#1a1208] text-right">{{ $selectedTemplate?->label() }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-[#5c5246]">{{ __('onboarding.review_reveal_animation') }}</dt>
                    <dd class="text-[#1a1208] text-right">{{ $selectedReveal?->label() ?? __('app.reveal_none') }}</dd>
                </div>
                @if ($location_name !== '' || $location_address !== '')
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#5c5246]">{{ __('onboarding.location_name') }}</dt>
                        <dd class="text-[#1a1208] text-right">{{ $location_name ?: $location_address }}</dd>
                    </div>
                @endif
                @if ($motto !== '')
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#5c5246]">{{ __('onboarding.motto') }}</dt>
                        <dd class="text-[#1a1208] text-right line-clamp-2">{{ $motto }}</dd>
                    </div>
                @endif
                @if ($music_url !== '')
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#5c5246]">{{ __('onboarding.review_song') }}</dt>
                        <dd class="text-[#1a1208] text-right">{{ __('onboarding.review_song_set') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="rounded-xl border border-[#1a1208]/10 bg-[#fafaf8] p-5">
            <h2 class="text-sm uppercase tracking-wider text-[#c9a227] mb-3">{{ __('onboarding.review_account') }}</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-[#5c5246]">{{ __('onboarding.your_name') }}</dt>
                    <dd class="text-[#1a1208] text-right">{{ $your_name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-[#5c5246]">{{ __('onboarding.email') }}</dt>
                    <dd class="text-[#1a1208] text-right">{{ $email }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="space-y-3">
        @if ($previewError)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="alert">
                {{ $previewError }}
            </div>
        @endif
        @if ($submitError)
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                {{ $submitError }}
            </div>
        @endif
        <button
            type="button"
            wire:click="openPreview"
            class="w-full landing-btn-secondary py-4 rounded-xl landing-heading text-lg transition"
        >
            {{ __('onboarding.preview_invitation') }}
        </button>
        <button
            type="button"
            wire:click="submit"
            wire:loading.attr="disabled"
            class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition disabled:opacity-50"
        >
            <span wire:loading.remove wire:target="submit">{{ __('onboarding.submit') }}</span>
            <span wire:loading wire:target="submit">{{ __('onboarding.submitting') }}</span>
        </button>
    </div>
</div>
