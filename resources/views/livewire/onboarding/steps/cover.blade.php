<div>
    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-2 text-center">
        {{ __('onboarding.cover_title') }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-8 text-center text-sm">
        {{ __('onboarding.cover_subtitle') }}
    </p>

    <form wire:submit="nextStep" class="space-y-5">
        <div>
            <label for="hero_image" class="block text-sm text-[#5c5246] mb-2">{{ __('onboarding.cover_label') }}</label>
            <input id="hero_image" type="file" wire:model="hero_image" accept="image/*" class="landing-input w-full text-sm">
            <div wire:loading wire:target="hero_image" class="mt-2 text-sm text-[#5c5246]">{{ __('onboarding.cover_uploading') }}</div>
            @error('hero_image') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            @if ($hero_image && is_object($hero_image))
                <div class="mt-4 rounded-xl overflow-hidden border border-[#1a1208]/10">
                    <img src="{{ $hero_image->temporaryUrl() }}" alt="" class="w-full h-48 object-cover">
                </div>
            @endif
        </div>
        <button type="submit" class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition" wire:loading.attr="disabled" wire:target="hero_image">
            {{ __('onboarding.next') }}
        </button>
    </form>
</div>
