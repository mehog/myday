@php
    $controlClass = 'block h-10 w-full rounded-md border border-border bg-background px-3 text-sm disabled:opacity-60';
@endphp

<div class="space-y-6">
    @if ($flashMessage)
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">{{ $flashMessage }}</div>
    @endif
    @if ($flashError)
        <div class="rounded-lg border border-red-300/50 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100">{{ $flashError }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold">{{ __('locations.title') }}</h2>
            <p class="mt-1 text-sm text-muted-foreground">{{ __('locations.empty_description') }}</p>
        </div>
        @if (! $locked)
            <x-dashboard.button type="button" wire:click="openCreate">
                <x-dashboard.icon name="plus" class="h-4 w-4" />
                {{ __('dashboard.create') }}
            </x-dashboard.button>
        @endif
    </div>

    @if ($locked)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            {{ __('app.wedding_archived_readonly') }}
        </div>
    @endif

    <div class="rounded-xl border border-border bg-card shadow-sm">
        <div class="p-4 md:p-5">
            @if ($locations->isEmpty())
                <p class="font-medium">{{ __('locations.empty_heading') }}</p>
                <p class="mt-1 text-sm text-muted-foreground">{{ __('locations.empty_description') }}</p>
            @else
                <div class="space-y-3">
                    @foreach ($locations as $location)
                        <div class="flex flex-col gap-3 rounded-lg border border-border p-3 sm:flex-row sm:items-center sm:justify-between" wire:key="loc-{{ $location->id }}">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-medium">{{ $location->name }}</p>
                                    @if ($location->is_primary)
                                        <span class="rounded-full bg-primary/15 px-2 py-0.5 text-xs font-medium text-primary">{{ __('locations.field_is_primary') }}</span>
                                    @endif
                                </div>
                                @if ($location->label)
                                    <p class="text-xs text-muted-foreground">{{ $location->label }}</p>
                                @endif
                                @if ($location->address)
                                    <p class="mt-1 text-sm text-muted-foreground">{{ $location->address }}</p>
                                @endif
                            </div>
                            @if (! $locked)
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="rounded-md border border-border px-2 py-1 text-xs" wire:click="moveUp({{ $location->id }})">↑</button>
                                    <button type="button" class="rounded-md border border-border px-2 py-1 text-xs" wire:click="moveDown({{ $location->id }})">↓</button>
                                    <x-dashboard.button type="button" variant="secondary" class="!px-2 !py-1 text-xs" wire:click="openEdit({{ $location->id }})">{{ __('dashboard.edit') }}</x-dashboard.button>
                                    <x-dashboard.button type="button" variant="destructive" class="!px-2 !py-1 text-xs" wire:click="delete({{ $location->id }})" wire:confirm="{{ __('dashboard.delete') }}?">{{ __('dashboard.delete') }}</x-dashboard.button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <x-dashboard.modal :show="$showModal" :title="$editingId ? __('dashboard.edit') : __('dashboard.create')">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('locations.field_label') }}</label>
                <input type="text" wire:model="label" class="{{ $controlClass }}">
                <p class="mt-1 text-xs text-muted-foreground">{{ __('locations.field_label_helper') }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('locations.field_name') }}</label>
                <input type="text" wire:model="name" class="{{ $controlClass }}">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('locations.field_address') }}</label>
                <input type="text" wire:model="address" class="{{ $controlClass }}">
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model="is_primary" class="rounded border-border">
                {{ __('locations.field_is_primary') }}
            </label>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('locations.field_sort_order') }}</label>
                <input type="number" min="0" wire:model="sort_order" class="{{ $controlClass }}">
                @error('sort_order') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <details class="rounded-lg border border-border p-3">
                <summary class="cursor-pointer text-sm font-medium">{{ __('locations.section_coordinates') }}</summary>
                <p class="mt-2 text-xs text-muted-foreground">{{ __('locations.coordinates_description') }}</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">{{ __('locations.field_lat') }}</label>
                        <input type="number" step="0.0000001" wire:model="lat" class="{{ $controlClass }}">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">{{ __('locations.field_lng') }}</label>
                        <input type="number" step="0.0000001" wire:model="lng" class="{{ $controlClass }}">
                    </div>
                </div>
            </details>
            <div class="flex justify-end gap-2">
                <x-dashboard.button type="button" variant="secondary" wire:click="closeModal">{{ __('dashboard.cancel') }}</x-dashboard.button>
                <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.modal>
</div>
