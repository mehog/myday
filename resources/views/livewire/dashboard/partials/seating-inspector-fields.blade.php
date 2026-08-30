<div>
    <label class="mb-1 block text-xs font-medium text-muted-foreground">
        {{ __('seating.table_label') }}
    </label>
    <input
        type="text"
        class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm shadow-sm focus:border-primary focus:ring-1 focus:ring-primary"
        x-model="inspectorLabel"
        x-on:change="updateLabel()"
        x-on:blur="updateLabel()"
    />
</div>

<div>
    <label class="mb-2 block text-xs font-medium text-muted-foreground">
        {{ __('seating.chairs') }}
    </label>
    <div class="flex items-center gap-3">
        <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-background text-muted-foreground hover:bg-accent"
            x-on:click="removeChair()"
            aria-label="{{ __('seating.remove_chair') }}"
        >
            <x-dashboard.icon name="minus" class="h-4 w-4" />
        </button>
        <span class="min-w-[2rem] text-center text-lg font-semibold" x-text="inspectorChairCount"></span>
        <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-background text-muted-foreground hover:bg-accent"
            x-on:click="addChair()"
            aria-label="{{ __('seating.add_chair') }}"
        >
            <x-dashboard.icon name="plus" class="h-4 w-4" />
        </button>
    </div>
</div>

<template x-if="selectedTable && selectedTable.type !== 'round'">
    <div>
        <label class="mb-2 block text-xs font-medium text-muted-foreground">
            {{ __('seating.rotation') }}
        </label>
        <div class="flex items-center gap-2">
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-background text-muted-foreground hover:bg-accent"
                x-on:click="rotateTable(-15)"
                aria-label="{{ __('seating.rotate_left') }}"
            >
                <x-dashboard.icon name="rotate-left" class="h-4 w-4" />
            </button>
            <span class="min-w-[3rem] text-center text-sm font-semibold" x-text="inspectorRotation + '°'"></span>
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-background text-muted-foreground hover:bg-accent"
                x-on:click="rotateTable(15)"
                aria-label="{{ __('seating.rotate_right') }}"
            >
                <x-dashboard.icon name="rotate-right" class="h-4 w-4" />
            </button>
            <x-dashboard.button
                type="button"
                variant="secondary"
                class="!px-2 !py-1 text-xs"
                x-on:click="rotateTable(-inspectorRotation)"
            >
                {{ __('seating.reset_rotation') }}
            </x-dashboard.button>
        </div>
    </div>
</template>

<div>
    <x-dashboard.button
        type="button"
        variant="destructive"
        class="w-full"
        x-on:click="deleteTable()"
    >
        {{ __('seating.delete_table') }}
    </x-dashboard.button>
</div>
