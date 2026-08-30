@props([
    'show' => false,
    'title' => null,
    'maxWidth' => 'max-w-lg',
])

@if ($show)
    @teleport('body')
        <div
            class="dashboard-modal-overlay fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 lg:items-center lg:p-4"
            wire:click.self="$dispatch('close-dashboard-modal')"
            x-data
            x-on:keydown.escape.window="$wire.dispatch('close-dashboard-modal')"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="dashboard-modal-panel flex w-full {{ $maxWidth }} max-h-[90dvh] flex-col overflow-hidden rounded-t-2xl border border-border bg-card text-card-foreground shadow-xl lg:max-h-[90vh] lg:rounded-xl"
            >
                <div class="flex shrink-0 justify-center pt-2 lg:hidden" aria-hidden="true">
                    <span class="h-1 w-10 rounded-full bg-muted-foreground/35"></span>
                </div>
                <div class="flex shrink-0 items-start justify-between gap-3 border-b border-border px-4 py-3 md:px-5">
                    <h3 class="text-base font-semibold">{{ $title }}</h3>
                    <button
                        type="button"
                        class="rounded-md px-2 py-1 text-sm text-muted-foreground hover:bg-accent"
                        wire:click="$dispatch('close-dashboard-modal')"
                    >
                        {{ __('dashboard.cancel') }}
                    </button>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto p-4 md:p-5">
                    {{ $slot }}
                </div>
                @isset($footer)
                    <div class="dashboard-modal-footer shrink-0 border-t border-border px-4 py-3 md:px-5">
                        {{ $footer }}
                    </div>
                @endisset
            </div>
        </div>
    @endteleport
@endif
