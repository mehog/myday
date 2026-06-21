<div>
    @if ($submitted)
        <div class="text-center py-8">
            <div class="w-16 h-16 rounded-full bg-[#c9a227]/20 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-[#c9a227]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="landing-heading text-2xl text-[#faf6ee] mb-2">{{ __('landing.form_success_title') }}</h3>
            <p class="landing-body text-[#d4c4a8]">{{ __('landing.form_success_text') }}</p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-5">
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-sm text-[#d4c4a8] mb-2">{{ __('landing.form_name') }} *</label>
                    <input
                        id="name"
                        type="text"
                        wire:model="name"
                        class="landing-input w-full"
                    >
                    @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm text-[#d4c4a8] mb-2">{{ __('landing.form_email') }} *</label>
                    <input
                        id="email"
                        type="email"
                        wire:model="email"
                        class="landing-input w-full"
                    >
                    @error('email') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="phone" class="block text-sm text-[#d4c4a8] mb-2">{{ __('landing.form_phone') }}</label>
                <input id="phone" type="tel" wire:model="phone" class="landing-input w-full">
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="groomName" class="block text-sm text-[#d4c4a8] mb-2">{{ __('landing.form_groom') }}</label>
                    <input id="groomName" type="text" wire:model="groomName" class="landing-input w-full">
                </div>
                <div>
                    <label for="brideName" class="block text-sm text-[#d4c4a8] mb-2">{{ __('landing.form_bride') }}</label>
                    <input id="brideName" type="text" wire:model="brideName" class="landing-input w-full">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="weddingDate" class="block text-sm text-[#d4c4a8] mb-2">{{ __('landing.form_date') }}</label>
                    <input id="weddingDate" type="date" wire:model="weddingDate" class="landing-input w-full">
                </div>
                <div>
                    <label for="theme" class="block text-sm text-[#d4c4a8] mb-2">{{ __('landing.form_theme') }}</label>
                    <select id="theme" wire:model="theme" class="landing-input w-full">
                        <option value="">{{ __('landing.form_theme_placeholder') }}</option>
                        @foreach ($themes as $themeOption)
                            <option value="{{ $themeOption->value }}">{{ $themeOption->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm text-[#d4c4a8] mb-2">{{ __('landing.form_notes') }}</label>
                <textarea
                    id="notes"
                    wire:model="notes"
                    rows="4"
                    class="landing-input w-full resize-none"
                    placeholder="{{ __('landing.form_notes_placeholder') }}"
                ></textarea>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="submit">{{ __('landing.form_submit') }}</span>
                <span wire:loading wire:target="submit">{{ __('landing.form_saving') }}</span>
            </button>
        </form>
    @endif
</div>
