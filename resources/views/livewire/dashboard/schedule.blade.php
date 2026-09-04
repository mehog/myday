@php
    $controlClass = 'block h-10 w-full rounded-md border border-border bg-background px-3 text-sm disabled:opacity-60';
@endphp

<div class="space-y-6">
    @if ($flashMessage)
        <div class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100">{{ $flashMessage }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="hidden text-xl font-semibold lg:block">{{ __('schedule.title') }}</h2>
        @if (! $locked)
            <div class="hidden lg:flex">
                <x-dashboard.button type="button" wire:click="openCreate">
                    <x-dashboard.icon name="plus" class="h-4 w-4" />
                    {{ __('dashboard.create') }}
                </x-dashboard.button>
            </div>
        @endif
    </div>

    @if ($locked)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            {{ __('app.wedding_archived_readonly') }}
        </div>
    @endif

    <div class="rounded-xl border border-border bg-card shadow-sm">
        <div class="p-4 md:p-5">
            @forelse ($items as $item)
                <div class="flex flex-col gap-3 border-b border-border py-3 last:border-0 sm:flex-row sm:items-center sm:justify-between" wire:key="sched-{{ $item->id }}">
                    <div>
                        <p class="text-sm font-medium tabular-nums text-muted-foreground">{{ is_string($item->time) ? substr($item->time, 0, 5) : $item->time?->format('H:i') }}</p>
                        <p class="font-medium">{{ $item->title }}</p>
                        @if ($item->description)
                            <p class="text-sm text-muted-foreground">{{ $item->description }}</p>
                        @endif
                    </div>
                    @if (! $locked)
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="rounded-md border border-border px-2 py-1 text-xs" wire:click="moveUp({{ $item->id }})">↑</button>
                            <button type="button" class="rounded-md border border-border px-2 py-1 text-xs" wire:click="moveDown({{ $item->id }})">↓</button>
                            <x-dashboard.button type="button" variant="secondary" class="!px-2 !py-1 text-xs" wire:click="openEdit({{ $item->id }})">{{ __('dashboard.edit') }}</x-dashboard.button>
                            <x-dashboard.button type="button" variant="destructive" class="!px-2 !py-1 text-xs" wire:click="delete({{ $item->id }})" wire:confirm="{{ __('dashboard.delete') }}?">{{ __('dashboard.delete') }}</x-dashboard.button>
                        </div>
                    @endif
                </div>
            @empty
                <p class="font-medium">{{ __('schedule.empty_heading') }}</p>
                <p class="mt-1 text-sm text-muted-foreground">{{ __('schedule.empty_description') }}</p>
            @endforelse
        </div>
    </div>

    <x-dashboard.fab wire:click="openCreate" :label="__('dashboard.create')" :show="! $locked" />

    <x-dashboard.modal :show="$showModal" :title="$editingId ? __('dashboard.edit') : __('dashboard.create')">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('schedule.field_time') }}</label>
                <x-dashboard.date-input type="time" wire:model="time" />
                @error('time') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('schedule.field_title') }}</label>
                <input type="text" wire:model="title" class="{{ $controlClass }}">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('schedule.field_description') }}</label>
                <textarea wire:model="description" rows="3" class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm"></textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('schedule.field_sort_order') }}</label>
                <input type="number" min="0" wire:model="sort_order" class="{{ $controlClass }}">
            </div>
            <div class="flex justify-end gap-2">
                <x-dashboard.button type="button" variant="secondary" wire:click="closeModal">{{ __('dashboard.cancel') }}</x-dashboard.button>
                <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.modal>
</div>
