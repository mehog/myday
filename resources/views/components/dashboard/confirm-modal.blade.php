@props([
    'show' => false,
    'title',
    'description' => null,
    'confirmLabel',
    'confirmVariant' => 'destructive',
])

<x-dashboard.modal :show="$show" :title="$title" max-width="max-w-sm">
    @if ($description)
        <p class="text-sm text-muted-foreground">{{ $description }}</p>
    @endif

    <x-slot:footer>
        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <x-dashboard.button
                type="button"
                variant="secondary"
                class="w-full sm:w-auto"
                wire:click="$dispatch('close-dashboard-modal')"
            >
                {{ __('dashboard.cancel') }}
            </x-dashboard.button>
            <x-dashboard.button
                type="button"
                :variant="$confirmVariant"
                class="w-full sm:w-auto"
                {{ $attributes }}
            >
                {{ $confirmLabel }}
            </x-dashboard.button>
        </div>
    </x-slot:footer>
</x-dashboard.modal>
