<div>
    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-2 text-center">
        {{ __('onboarding.template_title') }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-6 text-center text-sm">
        {{ __('onboarding.template_subtitle') }}
    </p>
    @error('template') <p class="mb-3 text-sm text-red-500 text-center">{{ $message }}</p> @enderror

    <div class="space-y-3">
        @foreach ($templates as $templateOption)
            <button
                type="button"
                wire:click="selectTemplate('{{ $templateOption->value }}')"
                @class([
                    'w-full p-4 rounded-xl text-left border transition active:scale-[0.98] flex items-center gap-4',
                    'border-[#c9a227] bg-[#c9a227]/10' => $template === $templateOption->value,
                    'border-[#1a1208]/15 bg-white hover:border-[#1a1208]/30' => $template !== $templateOption->value,
                ])
            >
                <span class="flex-1">
                    <span class="block landing-heading font-semibold text-[#1a1208]">{{ $templateOption->label() }}</span>
                    <span class="block text-sm text-[#5c5246] mt-0.5">{{ __('onboarding.template_hint_'.$templateOption->value) }}</span>
                </span>
                <svg class="w-5 h-5 text-[#5c5246] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        @endforeach
    </div>
</div>
