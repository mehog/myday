@props([
    'show' => false,
    'title' => null,
    'maxWidth' => 'max-w-lg',
])

@if ($show)
    @teleport('body')
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            wire:click.self="$dispatch('close-dashboard-modal')"
            x-data
            x-on:keydown.escape.window="$wire.dispatch('close-dashboard-modal')"
            role="dialog"
            aria-modal="true"
        >
            <div class="w-full {{ $maxWidth }} max-h-[90vh] overflow-y-auto rounded-xl border border-border bg-card text-card-foreground shadow-xl">
                <div class="flex items-start justify-between gap-3 border-b border-border px-4 py-3 md:px-5">
                    <h3 class="text-base font-semibold">{{ $title }}</h3>
                    <button
                        type="button"
                        class="rounded-md px-2 py-1 text-sm text-muted-foreground hover:bg-accent"
                        wire:click="$dispatch('close-dashboard-modal')"
                    >
                        {{ __('dashboard.cancel') }}
                    </button>
                </div>
                <div class="p-4 md:p-5">
                    {{ $slot }}
                </div>
                @isset($footer)
                    <div class="border-t border-border px-4 py-3 md:px-5">
                        {{ $footer }}
                    </div>
                @endisset
            </div>
        </div>
    @endteleport
@endif
