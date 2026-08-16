<div>
    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-2 text-center">
        {{ __('onboarding.theme_title') }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-6 text-center text-sm">
        {{ __('onboarding.theme_subtitle') }}
    </p>
    @error('theme') <p class="mb-3 text-sm text-red-500 text-center">{{ $message }}</p> @enderror

    <div class="space-y-3">
        @foreach ($themes as $themeOption)
            @php $colors = $themeOption->placeCardColors(); @endphp
            <button
                type="button"
                wire:click="selectTheme('{{ $themeOption->value }}')"
                @class([
                    'w-full p-4 rounded-xl text-left border transition active:scale-[0.98] flex items-center gap-4',
                    'border-[#c9a227] bg-[#c9a227]/10' => $theme === $themeOption->value,
                    'border-[#1a1208]/15 bg-white hover:border-[#1a1208]/30' => $theme !== $themeOption->value,
                ])
            >
                <span
                    class="shrink-0 w-12 h-12 rounded-full border border-[#1a1208]/10 shadow-inner"
                    style="background: linear-gradient(135deg, {{ $colors['bg'] }} 40%, {{ $colors['accent'] }} 100%);"
                    aria-hidden="true"
                ></span>
                <span class="flex-1">
                    <span class="block landing-heading font-semibold text-[#1a1208]">{{ $themeOption->label() }}</span>
                </span>
                <svg class="w-5 h-5 text-[#5c5246] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        @endforeach
    </div>
    <p class="mt-5 text-center text-xs text-[#5c5246]/90">{{ __('onboarding.design_changeable_note') }}</p>
</div>
