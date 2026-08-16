<div>
    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-2 text-center">
        {{ __('onboarding.account_title') }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-8 text-center text-sm">
        {{ __('onboarding.account_subtitle') }}
    </p>

    <form wire:submit="nextStep" class="space-y-5">
        <div>
            <label for="your_name" class="block text-sm text-[#5c5246] mb-2">{{ __('onboarding.your_name') }} *</label>
            <input id="your_name" type="text" wire:model="your_name" class="landing-input w-full" autocomplete="name">
            @error('your_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="block text-sm text-[#5c5246] mb-2">{{ __('onboarding.email') }} *</label>
            <input id="email" type="email" wire:model="email" class="landing-input w-full" autocomplete="email">
            @error('email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="block text-sm text-[#5c5246] mb-2">{{ __('onboarding.password') }} *</label>
            <input id="password" type="password" wire:model="password" class="landing-input w-full" autocomplete="new-password">
            @error('password') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm text-[#5c5246] mb-2">{{ __('onboarding.password_confirmation') }} *</label>
            <input id="password_confirmation" type="password" wire:model="password_confirmation" class="landing-input w-full" autocomplete="new-password">
        </div>
        <button type="submit" class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition">
            {{ __('onboarding.next') }}
        </button>
    </form>
</div>
