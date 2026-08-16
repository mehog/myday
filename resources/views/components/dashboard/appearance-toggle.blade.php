<div
    class="relative"
    x-data="{
        open: false,
        set(mode) {
            $store.dashboardAppearance.set(mode);
            this.open = false;
        }
    }"
>
    <button
        type="button"
        class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-background hover:bg-accent"
        @click="open = !open"
        aria-label="{{ __('dashboard.appearance') }}"
    >
        <span x-show="$store.dashboardAppearance.resolved === 'light'"><x-dashboard.icon name="sun" class="h-4 w-4" /></span>
        <span x-show="$store.dashboardAppearance.resolved === 'dark'" x-cloak><x-dashboard.icon name="moon" class="h-4 w-4" /></span>
    </button>
    <div
        x-show="open"
        @click.outside="open = false"
        x-cloak
        class="absolute right-0 z-50 mt-2 w-40 overflow-hidden rounded-md border border-border bg-popover p-1 shadow-lg"
    >
        <button type="button" class="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent" @click="set('light')">
            <x-dashboard.icon name="sun" class="h-4 w-4" /> {{ __('dashboard.appearance_light') }}
        </button>
        <button type="button" class="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent" @click="set('dark')">
            <x-dashboard.icon name="moon" class="h-4 w-4" /> {{ __('dashboard.appearance_dark') }}
        </button>
        <button type="button" class="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent" @click="set('system')">
            <x-dashboard.icon name="monitor" class="h-4 w-4" /> {{ __('dashboard.appearance_system') }}
        </button>
    </div>
</div>
