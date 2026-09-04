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
        <h2 class="hidden text-xl font-semibold lg:block">{{ __('photos.title') }}</h2>
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
            @if ($photos->isEmpty())
                <p class="font-medium">{{ __('photos.empty_heading') }}</p>
                <p class="mt-1 text-sm text-muted-foreground">{{ __('photos.empty_description') }}</p>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($photos as $photo)
                        <div class="overflow-hidden rounded-lg border border-border" wire:key="photo-{{ $photo->id }}">
                            @if ($url = $this->photoUrl($photo))
                                <img src="{{ $url }}" alt="{{ $photo->title ?? '' }}" class="aspect-video w-full object-cover">
                            @endif
                            <div class="space-y-2 p-3">
                                <p class="text-sm font-medium">{{ $photo->title ?: '—' }}</p>
                                @if (! $locked)
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" class="rounded-md border border-border px-2 py-1 text-xs" wire:click="moveUp({{ $photo->id }})">↑</button>
                                        <button type="button" class="rounded-md border border-border px-2 py-1 text-xs" wire:click="moveDown({{ $photo->id }})">↓</button>
                                        <x-dashboard.button type="button" variant="secondary" class="!px-2 !py-1 text-xs" wire:click="openEdit({{ $photo->id }})">{{ __('dashboard.edit') }}</x-dashboard.button>
                                        <x-dashboard.button type="button" variant="destructive" class="!px-2 !py-1 text-xs" wire:click="delete({{ $photo->id }})" wire:confirm="{{ __('dashboard.delete') }}?">{{ __('dashboard.delete') }}</x-dashboard.button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <x-dashboard.fab wire:click="openCreate" :label="__('dashboard.create')" :show="! $locked" />

    <x-dashboard.modal :show="$showModal" :title="$editingId ? __('dashboard.edit') : __('dashboard.create')">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('photos.field_photo') }}</label>
                <input type="file" wire:model="photo" accept="image/*" class="block w-full text-sm">
                @error('photo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <div wire:loading wire:target="photo" class="mt-1 text-xs text-muted-foreground">…</div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('photos.field_title') }}</label>
                <input type="text" wire:model="title" class="{{ $controlClass }}" placeholder="{{ __('photos.field_title_placeholder') }}">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('photos.field_sort_order') }}</label>
                <input type="number" min="0" wire:model="sort_order" class="{{ $controlClass }}">
            </div>
            <div class="flex justify-end gap-2">
                <x-dashboard.button type="button" variant="secondary" wire:click="closeModal">{{ __('dashboard.cancel') }}</x-dashboard.button>
                <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.modal>
</div>
