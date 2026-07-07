@php
    $pageComponent = static::isSimple() ? 'filament-panels::page.simple' : 'filament-panels::page';
@endphp

<x-dynamic-component :component="$pageComponent">
    {{ $this->content }}

    <div class="mt-8">
        @livewire(\App\Filament\App\Widgets\UserPushDevicesWidget::class)
    </div>
</x-dynamic-component>
