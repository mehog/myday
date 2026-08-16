<div>
    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-2 text-center">
        {{ __('onboarding.names_title') }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-8 text-center text-sm">
        {{ __('onboarding.names_subtitle') }}
    </p>

    <form wire:submit="nextStep" class="space-y-5">
        <div>
            <label for="groom_name" class="block text-sm text-[#5c5246] mb-2">{{ __('onboarding.groom_name') }} *</label>
            <input id="groom_name" type="text" wire:model="groom_name" class="landing-input w-full" autocomplete="name">
            @error('groom_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="bride_name" class="block text-sm text-[#5c5246] mb-2">{{ __('onboarding.bride_name') }} *</label>
            <input id="bride_name" type="text" wire:model="bride_name" class="landing-input w-full" autocomplete="name">
            @error('bride_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition">
            {{ __('onboarding.next') }}
        </button>
    </form>
</div>
