@php
    $controlClass = 'block w-full rounded-md border border-border bg-background px-3 py-2 text-sm disabled:opacity-60';
@endphp

<div class="space-y-6">
    @if ($flashMessage)
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">{{ $flashMessage }}</div>
    @endif

    <h2 class="hidden text-xl font-semibold lg:block">{{ __('dashboard.profile_title') }}</h2>

    <x-dashboard.card>
        <form wire:submit="save" class="max-w-lg space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('dashboard.profile_name') }}</label>
                <input type="text" wire:model="name" class="{{ $controlClass }} h-10">
                @error('name') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('dashboard.profile_email') }}</label>
                @if ($emailEditable)
                    <input type="email" wire:model="email" class="{{ $controlClass }} h-10">
                    @error('email') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-muted-foreground">{{ __('app.email_unverified_editable') }}</p>
                @else
                    <input type="email" value="{{ $email }}" readonly class="{{ $controlClass }} h-10 opacity-80">
                    <p class="mt-1 text-xs text-muted-foreground">{{ __('app.email_readonly') }}</p>
                @endif
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('dashboard.profile_locale') }}</label>
                <select wire:model="locale" class="{{ $controlClass }} h-10">
                    @foreach ($locales as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('locale') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('dashboard.profile_current_password') }}</label>
                <input type="password" wire:model="current_password" class="{{ $controlClass }} h-10" autocomplete="current-password">
                @error('current_password') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('dashboard.profile_new_password') }}</label>
                <input type="password" wire:model="password" class="{{ $controlClass }} h-10" autocomplete="new-password">
                @error('password') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('dashboard.profile_password_confirmation') }}</label>
                <input type="password" wire:model="password_confirmation" class="{{ $controlClass }} h-10" autocomplete="new-password">
            </div>

            <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
        </form>
    </x-dashboard.card>

    <x-dashboard.card>
        <h3 class="font-medium">{{ __('dashboard.profile_email_notifications_heading') }}</h3>
        <p class="mt-1 text-sm text-muted-foreground">{{ __('dashboard.profile_email_notifications_help') }}</p>
        <form wire:submit="save" class="mt-4">
            <label class="flex items-start gap-3 text-sm">
                <input
                    type="checkbox"
                    wire:model="email_notifications_enabled"
                    class="mt-0.5 h-4 w-4 rounded border-border"
                >
                <span>
                    <span class="font-medium">{{ __('dashboard.profile_email_notifications') }}</span>
                    <span class="mt-0.5 block text-muted-foreground">{{ __('dashboard.profile_email_notifications_hint') }}</span>
                </span>
            </label>
            <div class="mt-4">
                <x-dashboard.button type="submit" variant="secondary">{{ __('dashboard.save') }}</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.card>

    <x-dashboard.card>
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="font-medium">{{ __('app.push_devices_heading') }}</h3>
                <p class="mt-1 text-sm text-muted-foreground">{{ __('app.push_devices_description') }}</p>
            </div>
            <x-dashboard.button
                type="button"
                variant="secondary"
                x-data
                x-on:click="subscribeToPushAsUser().then((result) => { if (result.ok) { $wire.$refresh() } })"
            >
                {{ __('app.push_devices_add') }}
            </x-dashboard.button>
        </div>

        @if ($devices->isEmpty())
            <p class="mt-4 text-sm font-medium">{{ __('app.push_devices_empty_heading') }}</p>
            <p class="mt-1 text-sm text-muted-foreground">{{ __('app.push_devices_empty_desc') }}</p>
        @else
            <ul class="mt-4 divide-y divide-border">
                @foreach ($devices as $device)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3" wire:key="push-device-{{ $device->id }}">
                        <div>
                            <p class="text-sm font-medium">{{ $device->device_label ?: __('app.push_devices_unknown') }}</p>
                            <p class="text-xs text-muted-foreground">{{ $device->created_at?->diffForHumans() }}</p>
                        </div>
                        <x-dashboard.button
                            type="button"
                            variant="destructive"
                            class="!px-2 !py-1 text-xs"
                            wire:click="removeDevice({{ $device->id }})"
                            wire:confirm="{{ __('app.push_devices_remove_confirm_body') }}"
                        >
                            {{ __('app.push_devices_remove') }}
                        </x-dashboard.button>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-dashboard.card>
</div>
