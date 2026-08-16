@php
    $locked = $locked ?? false;
    $controlClass = 'block h-10 w-full rounded-md border border-border bg-background px-3 text-sm disabled:opacity-60';
@endphp

<div class="space-y-6">
    <form wire:submit="saveCurrency" class="space-y-3">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('budget.currency') }}</label>
            <select
                wire:model="currency"
                @disabled($locked)
                class="{{ $controlClass }}"
            >
                <option value="EUR">EUR</option>
                <option value="BAM">BAM</option>
            </select>
            @error('currency') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        @unless ($locked)
            <button
                type="submit"
                class="inline-flex h-10 items-center justify-center rounded-lg bg-primary px-3 text-sm font-semibold text-white shadow-sm hover:opacity-90"
            >
                {{ __('budget.save_currency') }}
            </button>
        @endunless
    </form>

    <div class="border-t border-gray-100 dark:border-white/10"></div>

    <form wire:submit="saveTarget" class="space-y-3">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('budget.target_label') }}</label>
            <input
                type="number"
                min="0"
                step="0.01"
                wire:model="targetInput"
                placeholder="{{ __('budget.target_placeholder') }}"
                @disabled($locked)
                class="{{ $controlClass }}"
            >
            @error('targetInput') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        @unless ($locked)
            <button
                type="submit"
                class="inline-flex h-10 items-center justify-center rounded-lg bg-primary px-3 text-sm font-semibold text-white shadow-sm hover:opacity-90"
            >
                {{ __('budget.save_target') }}
            </button>
        @endunless
    </form>
</div>
