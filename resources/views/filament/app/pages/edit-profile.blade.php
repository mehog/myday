<x-filament-panels::page>
    <div class="mx-auto w-full max-w-4xl space-y-8">
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
            {{ $this->content }}
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
            @livewire(\App\Filament\App\Widgets\UserPushDevicesWidget::class)
        </section>
    </div>
</x-filament-panels::page>
