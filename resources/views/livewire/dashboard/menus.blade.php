@php
    $controlClass = 'block h-10 w-full rounded-md border border-border bg-background px-3 text-sm disabled:opacity-60';
    $isCustomEdit = $editingRecord === null || $editingRecord->isCustom();
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
            <h2 class="hidden text-xl font-semibold lg:block">{{ __('menu.title') }}</h2>
            <p class="mt-1 text-sm text-muted-foreground">{{ __('menu.empty_description') }}</p>
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
            @if ($menus->isEmpty())
                <p class="font-medium">{{ __('menu.empty_heading') }}</p>
            @else
                <div class="space-y-3">
                    @foreach ($menus as $menu)
                        <div class="flex flex-col gap-3 rounded-lg border border-border p-3 sm:flex-row sm:items-center sm:justify-between" wire:key="menu-{{ $menu->id }}">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-medium">{{ $menu->displayLabel() }}</p>
                                    <span @class([
                                        'rounded-full px-2 py-0.5 text-xs font-medium',
                                        'bg-muted text-muted-foreground' => $menu->isPlatform(),
                                        'bg-primary/15 text-primary' => $menu->isCustom(),
                                    ])>
                                        {{ $menu->isPlatform() ? __('menu.platform_badge') : __('menu.custom_badge') }}
                                    </span>
                                    @unless ($menu->is_visible)
                                        <span class="text-xs text-muted-foreground">{{ __('menu.field_is_visible') }}: off</span>
                                    @endunless
                                </div>
                            </div>
                            @if (! $locked)
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="rounded-md border border-border px-2 py-1 text-xs" wire:click="moveUp({{ $menu->id }})">↑</button>
                                    <button type="button" class="rounded-md border border-border px-2 py-1 text-xs" wire:click="moveDown({{ $menu->id }})">↓</button>
                                    <x-dashboard.button type="button" variant="secondary" class="!px-2 !py-1 text-xs" wire:click="openEdit({{ $menu->id }})">{{ __('dashboard.edit') }}</x-dashboard.button>
                                    @if ($menu->isCustom())
                                        <x-dashboard.button type="button" variant="destructive" class="!px-2 !py-1 text-xs" wire:click="delete({{ $menu->id }})" wire:confirm="{{ __('dashboard.delete') }}?">{{ __('dashboard.delete') }}</x-dashboard.button>
                                    @endif
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
            @if ($isCustomEdit)
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ __('menu.field_label') }}</label>
                    <input type="text" wire:model="label" class="{{ $controlClass }}">
                    @error('label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @elseif ($editingRecord)
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ __('menu.field_platform') }}</label>
                    <input type="text" value="{{ $editingRecord->platform_key?->label() }}" disabled class="{{ $controlClass }} opacity-70">
                </div>
            @endif
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model="is_visible" class="rounded border-border">
                {{ __('menu.field_is_visible') }}
            </label>
            <p class="text-xs text-muted-foreground">{{ __('menu.field_is_visible_helper') }}</p>
            <div>
                <label class="mb-1 block text-sm font-medium">{{ __('menu.field_sort_order') }}</label>
                <input type="number" min="0" wire:model="sort_order" class="{{ $controlClass }}">
                @error('sort_order') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-2">
                <x-dashboard.button type="button" variant="secondary" wire:click="closeModal">{{ __('dashboard.cancel') }}</x-dashboard.button>
                <x-dashboard.button type="submit">{{ __('dashboard.save') }}</x-dashboard.button>
            </div>
        </form>
    </x-dashboard.modal>
</div>
