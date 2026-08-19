<div>
    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-2 text-center">
        {{ __('onboarding.motto_title') }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-5 text-center text-sm">
        {{ __('onboarding.motto_subtitle') }}
    </p>

    <div class="flex flex-wrap gap-2 mb-5 justify-center">
        @foreach ($mottoPresets as $preset)
            <button
                type="button"
                wire:click="selectMotto(@js($preset))"
                @class([
                    'text-xs px-3 py-1.5 rounded-full border transition text-left max-w-full',
                    'border-[#c9a227] bg-[#c9a227]/10 text-[#1a1208]' => $motto === $preset,
                    'border-[#1a1208]/15 bg-white hover:border-[#c9a227] text-[#5c5246]' => $motto !== $preset,
                ])
            >
                {{ $preset }}
            </button>
        @endforeach
    </div>

    <form wire:submit="nextStep" class="space-y-5">
        <div>
            <label for="motto" class="block text-sm text-[#5c5246] mb-2">{{ __('onboarding.motto') }} *</label>
            <textarea id="motto" wire:model.live="motto" rows="3" maxlength="300" required class="landing-input w-full" placeholder="{{ __('onboarding.motto_placeholder') }}"></textarea>
            @error('motto') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition">
            {{ __('onboarding.next') }}
        </button>
    </form>
</div>
