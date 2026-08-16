@php
    $controlClass = 'block w-full rounded-md border border-border bg-background px-3 py-2 text-sm disabled:opacity-60';
@endphp

<div class="space-y-6">
    @if ($flashMessage)
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">{{ $flashMessage }}</div>
    @endif

    <h2 class="text-xl font-semibold">{{ __('dashboard.profile_title') }}</h2>

    <x-dashboard.card>
        <form wire:submit="save" class="max-w-lg space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('dashboard.profile_name') }}</label>
                <input type="text" wire:model="name" class="{{ $controlClass }} h-10">
                @error('name') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('dashboard.profile_email') }}</label>
                <input type="email" value="{{ $email }}" readonly class="{{ $controlClass }} h-10 opacity-80">
                <p class="mt-1 text-xs text-muted-foreground">{{ __('app.email_readonly') }}</p>
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

            <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
        </form>
    </x-dashboard.card>
</div>
