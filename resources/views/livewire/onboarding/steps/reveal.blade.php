<div>
    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-2 text-center">
        {{ __('onboarding.reveal_title') }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-6 text-center text-sm">
        {{ __('onboarding.reveal_subtitle') }}
    </p>

    <div class="space-y-3">
        <button
            type="button"
            wire:click="selectReveal('none')"
            @class([
                'w-full p-4 rounded-xl text-left border transition active:scale-[0.98] flex items-center gap-4',
                'border-[#c9a227] bg-[#c9a227]/10' => $reveal_animation === '',
                'border-[#1a1208]/15 bg-white hover:border-[#1a1208]/30' => $reveal_animation !== '',
            ])
        >
            <span class="flex-1 landing-heading font-semibold text-[#1a1208]">{{ __('app.reveal_none') }}</span>
            <svg class="w-5 h-5 text-[#5c5246] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        @foreach ($reveals as $revealOption)
            <button
                type="button"
                wire:click="selectReveal('{{ $revealOption->value }}')"
                @class([
                    'w-full p-4 rounded-xl text-left border transition active:scale-[0.98] flex items-center gap-4',
                    'border-[#c9a227] bg-[#c9a227]/10' => $reveal_animation === $revealOption->value,
                    'border-[#1a1208]/15 bg-white hover:border-[#1a1208]/30' => $reveal_animation !== $revealOption->value,
                ])
            >
                <span class="flex-1 landing-heading font-semibold text-[#1a1208]">{{ $revealOption->label() }}</span>
                <svg class="w-5 h-5 text-[#5c5246] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        @endforeach
    </div>
</div>
