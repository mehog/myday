@php
    $controlClass = 'block w-full rounded-md border border-border bg-background px-3 py-2 text-sm disabled:opacity-60';
@endphp

<div class="space-y-6">
    @if ($flashMessage)
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">{{ $flashMessage }}</div>
    @endif

    @if (! $wedding)
        <x-dashboard.card>
            <p class="text-sm text-muted-foreground">{{ __('dashboard.no_wedding') }}</p>
        </x-dashboard.card>
    @else
        @if ($locked)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                {{ __('app.wedding_archived_readonly') }}
            </div>
        @endif

        <form wire:submit="save" class="space-y-6">
            <x-dashboard.card>
                <h3 class="mb-4 font-medium">{{ __('app.section_couple') }}</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">{{ __('app.groom_name') }}</label>
                        <input type="text" wire:model="groom_name" class="{{ $controlClass }} h-10" @disabled($locked)>
                        @error('groom_name') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">{{ __('app.bride_name') }}</label>
                        <input type="text" wire:model="bride_name" class="{{ $controlClass }} h-10" @disabled($locked)>
                        @error('bride_name') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">{{ __('app.wedding_datetime') }}</label>
                        <input type="datetime-local" wire:model="wedding_date" min="{{ now()->format('Y-m-d\TH:i') }}" class="{{ $controlClass }} h-10" @disabled($locked)>
                        @error('wedding_date') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">{{ __('app.invitation_link') }}</label>
                        <input type="text" value="{{ $wedding->public_url }}" readonly class="{{ $controlClass }} h-10 opacity-80">
                    </div>
                </div>
            </x-dashboard.card>

            <x-dashboard.card>
                <h3 class="mb-4 font-medium">{{ __('app.section_rsvp') }}</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">{{ __('app.rsvp_deadline') }}</label>
                        <input type="date" wire:model="rsvp_deadline" class="{{ $controlClass }} h-10" @disabled($locked)>
                        @error('rsvp_deadline') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">{{ __('app.invitation_locale') }}</label>
                        <select wire:model="invitation_locale" class="{{ $controlClass }} h-10" @disabled($locked)>
                            @foreach ($locales as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-muted-foreground">{{ __('app.invitation_locale_helper') }}</p>
                        @error('invitation_locale') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="accommodation_enabled" class="rounded border-border" @disabled($locked)>
                            {{ __('app.accommodation_enabled') }}
                        </label>
                        <p class="mt-1 text-xs text-muted-foreground">{{ __('app.accommodation_enabled_helper') }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">{{ __('app.guest_message') }}</label>
                        <textarea wire:model="send_message" rows="4" class="{{ $controlClass }}" placeholder="{{ __('app.guest_message_placeholder') }}" @disabled($locked)></textarea>
                        <p class="mt-1 text-xs text-muted-foreground">{{ __('app.guest_message_helper') }}</p>
                        @error('send_message') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                </div>
            </x-dashboard.card>

            @unless ($locked)
                <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
            @endunless
        </form>
    @endif
</div>
