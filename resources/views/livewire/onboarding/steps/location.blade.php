<div>
    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-2 text-center">
        {{ __('onboarding.location_title') }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-8 text-center text-sm">
        {{ __('onboarding.location_subtitle') }}
    </p>

    <form wire:submit="nextStep" class="space-y-5">
        <div>
            <label for="location_name" class="block text-sm text-[#5c5246] mb-2">{{ __('onboarding.location_name') }} *</label>
            <input id="location_name" type="text" wire:model="location_name" required class="landing-input w-full" placeholder="{{ __('onboarding.location_name_placeholder') }}">
            @error('location_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="location_address" class="block text-sm text-[#5c5246] mb-2">{{ __('onboarding.location_address') }} *</label>
            <input id="location_address" type="text" wire:model="location_address" required class="landing-input w-full" placeholder="{{ __('onboarding.location_address_placeholder') }}">
            @error('location_address') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition">
            {{ __('onboarding.next') }}
        </button>
    </form>
</div>
