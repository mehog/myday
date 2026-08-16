<div>
    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-2 text-center">
        {{ __('onboarding.guests_title') }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-6 text-center text-sm">
        {{ __('onboarding.guests_subtitle') }}
    </p>

    <form wire:submit="nextStep" class="space-y-4">
        @foreach ($guests as $index => $guest)
            <div class="p-4 rounded-xl border border-[#1a1208]/10 bg-[#fafaf8] space-y-3" wire:key="guest-{{ $index }}">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs uppercase tracking-wider text-[#c9a227]">{{ __('onboarding.guest_item') }} {{ $index + 1 }}</p>
                    @if (count($guests) > 1)
                        <button type="button" wire:click="removeGuest({{ $index }})" class="text-xs text-red-500 hover:underline">
                            {{ __('onboarding.remove') }}
                        </button>
                    @endif
                </div>
                <div>
                    <label class="block text-sm text-[#5c5246] mb-1">{{ __('onboarding.guest_name') }}</label>
                    <input type="text" wire:model="guests.{{ $index }}.name" class="landing-input w-full">
                    @error('guests.'.$index.'.name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-[#5c5246] mb-1">{{ __('onboarding.guest_email') }}</label>
                    <input type="email" wire:model="guests.{{ $index }}.email" class="landing-input w-full">
                    @error('guests.'.$index.'.email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-[#5c5246]">
                    <input type="checkbox" wire:model="guests.{{ $index }}.plus_one_allowed" class="rounded border-[#1a1208]/20 text-[#c9a227] focus:ring-[#c9a227]">
                    {{ __('onboarding.guest_plus_one') }}
                </label>
            </div>
        @endforeach

        <button type="button" wire:click="addGuest" class="w-full landing-btn-secondary py-3 rounded-xl text-sm">
            {{ __('onboarding.guests_add') }}
        </button>

        <button type="submit" class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition">
            {{ __('onboarding.next') }}
        </button>
    </form>
</div>
