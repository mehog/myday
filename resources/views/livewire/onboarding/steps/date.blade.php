<div>
    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-2 text-center">
        {{ __('onboarding.date_title') }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-8 text-center text-sm">
        {{ __('onboarding.date_subtitle') }}
    </p>

    <form wire:submit="nextStep" class="space-y-5">
        <div class="min-w-0">
            <label for="wedding_date" class="block text-sm text-[#5c5246] mb-2">{{ __('onboarding.wedding_date') }} *</label>
            <input id="wedding_date" type="date" wire:model="wedding_date" class="landing-input w-full min-w-0 max-w-full">
            @error('wedding_date') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition">
            {{ __('onboarding.next') }}
        </button>
    </form>
</div>
