@php
    use App\BudgetCalculationType;
    use App\BudgetCategory;
    use App\BudgetGuestMode;

    $wedding = $this->wedding;
    $items = $this->getItems();
    $totals = $this->getTotals();
    $guestCount = $this->getGuestCount();
    $breakdown = $this->getCategoryBreakdown();
    $suggestions = $this->getSuggestions();
    $locked = $this->isLocked();
    $target = $wedding?->budget_target;
    $vsTarget = null;

    if ($target !== null) {
        $diff = bcsub((string) $target, $totals['total'], 2);
        $cmp = bccomp($diff, '0', 2);
        $vsTarget = [
            'diff' => $diff,
            'abs' => ltrim($diff, '-'),
            'cmp' => $cmp,
        ];
    }

    $guestModeHelp = match ($guestMode) {
        BudgetGuestMode::Confirmed->value => __('budget.guest_mode_help_confirmed'),
        BudgetGuestMode::Invited->value => __('budget.guest_mode_help_invited'),
        default => __('budget.guest_mode_help_manual'),
    };

    $controlClass = 'fi-input block h-10 w-full rounded-lg border-gray-300 px-3 text-sm leading-none shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:opacity-60 dark:border-white/10 dark:bg-white/5 dark:text-white';

    $guestSettingsSummary = (BudgetGuestMode::tryFrom($guestMode)?->label() ?? __('budget.guest_mode'))
        .' · '.__('budget.guest_count').': '.($guestCountInput !== '' ? $guestCountInput : $guestCount);
@endphp

<x-filament-panels::page>
    @if ($locked)
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            {{ __('budget.locked_notice') }}
        </div>
    @endif

    {{-- Guest count form --}}
    <div class="mb-6">
        <x-filament::section
            :heading="__('budget.guest_settings_heading')"
            :description="$guestSettingsSummary"
            collapsible
            compact
        >
            <form wire:submit="saveGuestSettings" class="grid gap-4 lg:grid-cols-12 lg:items-start">
                <div class="lg:col-span-4">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('budget.guest_mode') }}</label>
                    <select
                        wire:model.live="guestMode"
                        @disabled($locked)
                        class="{{ $controlClass }}"
                    >
                        @foreach (BudgetGuestMode::options() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $guestModeHelp }}</p>
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('budget.guest_count') }}</label>
                    <input
                        type="number"
                        min="0"
                        wire:model="guestCountInput"
                        @disabled($locked || $guestMode !== BudgetGuestMode::Manual->value)
                        class="{{ $controlClass }}"
                    >
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ __('budget.guest_count_help') }}</p>
                </div>

                <div class="flex flex-wrap gap-2 lg:col-span-5 lg:pt-7">
                    @unless ($locked)
                        <button
                            type="submit"
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-primary-600 px-3 text-sm font-semibold text-white shadow-sm hover:bg-primary-500"
                        >
                            {{ __('budget.save_guests') }}
                        </button>

                        @if ($guestMode !== BudgetGuestMode::Manual->value)
                            <button
                                type="button"
                                wire:click="useAsEstimate"
                                class="inline-flex h-10 items-center justify-center rounded-lg bg-gray-100 px-3 text-sm font-semibold text-gray-800 hover:bg-gray-200 dark:bg-white/10 dark:text-white dark:hover:bg-white/20"
                            >
                                {{ __('budget.use_as_estimate') }}
                            </button>
                        @endif
                    @endunless
                </div>
            </form>
        </x-filament::section>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('budget.stat_total') }}</p>
            <p class="mt-2 text-2xl font-semibold text-primary-600 dark:text-primary-400">{{ $this->formatMoney($totals['total']) }}</p>
            @if ($vsTarget)
                <p @class([
                    'mt-2 text-xs font-medium',
                    'text-success-600 dark:text-success-400' => $vsTarget['cmp'] >= 0,
                    'text-danger-600 dark:text-danger-400' => $vsTarget['cmp'] < 0,
                ])>
                    @if ($vsTarget['cmp'] > 0)
                        {{ __('budget.stat_vs_target_under', ['amount' => $this->formatMoney($vsTarget['abs'])]) }}
                    @elseif ($vsTarget['cmp'] < 0)
                        {{ __('budget.stat_vs_target_over', ['amount' => $this->formatMoney($vsTarget['abs'])]) }}
                    @else
                        {{ __('budget.stat_vs_target_exact') }}
                    @endif
                </p>
            @endif
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('budget.stat_guests') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $guestCount }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('budget.stat_per_person') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $this->formatMoney($totals['per_person']) }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('budget.stat_paid') }} / {{ __('budget.stat_unpaid') }}</p>
            <p class="mt-2 text-2xl font-semibold text-success-600 dark:text-success-400">{{ $this->formatMoney($totals['paid']) }}</p>
            <p class="mt-1 text-sm font-medium text-warning-600 dark:text-warning-400">{{ $this->formatMoney($totals['unpaid']) }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        {{-- Items --}}
        <div class="xl:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-white/10">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ __('budget.items_heading') }}</h2>
                </div>

                @unless ($locked)
                    <form wire:submit="addItem" class="space-y-3 border-b border-gray-100 p-4 dark:border-white/10">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('budget.category') }}</label>
                                <select
                                    wire:model="newCategory"
                                    class="{{ $controlClass }}"
                                >
                                    @foreach (BudgetCategory::options() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('budget.calculation_type') }}</label>
                                <select
                                    wire:model="newCalculationType"
                                    class="{{ $controlClass }}"
                                >
                                    @foreach (BudgetCalculationType::options() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-12 sm:items-end">
                            <div class="sm:col-span-6">
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('budget.item_name_optional') }}</label>
                                <input
                                    type="text"
                                    wire:model="newName"
                                    placeholder="{{ __('budget.item_name_placeholder') }}"
                                    class="{{ $controlClass }}"
                                >
                                @error('newName') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-4">
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('budget.amount') }}</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    wire:model="newAmount"
                                    placeholder="{{ $currency }}"
                                    class="{{ $controlClass }}"
                                >
                                @error('newAmount') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <button
                                    type="submit"
                                    class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-primary-600 px-3 text-sm font-semibold text-white shadow-sm hover:bg-primary-500"
                                >
                                    + {{ __('budget.add') }}
                                </button>
                            </div>
                        </div>
                    </form>
                @endunless

                @if ($items->isEmpty())
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm font-medium text-gray-950 dark:text-white">{{ __('budget.empty_title') }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('budget.empty_body') }}</p>

                        @if (! $locked && count($suggestions) > 0)
                            <div class="mx-auto mt-6 max-w-md space-y-2 text-left">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('budget.suggestions_heading') }}</p>
                                @foreach ($suggestions as $index => $suggestion)
                                    <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 px-3 py-2 dark:border-white/10">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $suggestion['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ BudgetCategory::from($suggestion['category'])->label() }}</p>
                                        </div>
                                        <button
                                            type="button"
                                            wire:click="addSuggestion({{ $index }})"
                                            class="text-sm font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400"
                                        >
                                            {{ __('budget.add_suggestion') }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-white/10">
                            <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                <tr>
                                    <th class="px-5 py-3">{{ __('budget.item_name') }}</th>
                                    <th class="px-5 py-3">{{ __('budget.amount') }}</th>
                                    <th class="px-5 py-3">{{ __('budget.total') }}</th>
                                    <th class="px-5 py-3">{{ __('budget.paid') }}</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                                @foreach ($items as $item)
                                    @php
                                        $lineTotal = $item->lineTotal($guestCount);
                                        $categoryLabel = $item->category->label();
                                        $hasCustomName = $item->name !== $categoryLabel;
                                    @endphp
                                    <tr wire:key="budget-item-{{ $item->id }}">
                                        @if ($editingId === $item->id)
                                            <td colspan="5" class="px-5 py-4">
                                                <form wire:submit="saveEdit" class="space-y-3">
                                                    <div class="grid gap-3 sm:grid-cols-2">
                                                        <div>
                                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('budget.category') }}</label>
                                                            <select wire:model="editCategory" class="{{ $controlClass }}">
                                                                @foreach (BudgetCategory::options() as $value => $label)
                                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('budget.calculation_type') }}</label>
                                                            <select wire:model="editCalculationType" class="{{ $controlClass }}">
                                                                @foreach (BudgetCalculationType::options() as $value => $label)
                                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="grid gap-3 sm:grid-cols-12 sm:items-end">
                                                        <div class="sm:col-span-6">
                                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('budget.item_name_optional') }}</label>
                                                            <input type="text" wire:model="editName" placeholder="{{ __('budget.item_name_placeholder') }}" class="{{ $controlClass }}">
                                                        </div>
                                                        <div class="sm:col-span-4">
                                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('budget.amount') }}</label>
                                                            <input type="number" min="0" step="0.01" wire:model="editAmount" placeholder="{{ $currency }}" class="{{ $controlClass }}">
                                                        </div>
                                                        <div class="flex gap-2 sm:col-span-2">
                                                            <button type="submit" class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ __('budget.save') }}</button>
                                                            <button type="button" wire:click="cancelEdit" class="text-sm text-gray-500">{{ __('budget.cancel') }}</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </td>
                                        @else
                                            <td class="px-5 py-3 align-top">
                                                @if ($hasCustomName)
                                                    <p class="font-medium text-gray-950 dark:text-white">{{ $item->name }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $categoryLabel }}</p>
                                                @else
                                                    <p class="font-medium text-gray-950 dark:text-white">{{ $categoryLabel }}</p>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 align-top text-gray-700 dark:text-gray-200">
                                                @if ($item->calculation_type === BudgetCalculationType::PerPerson)
                                                    {{ __('budget.amount_x_guests', [
                                                        'amount' => $this->formatMoney($item->amount),
                                                        'count' => $guestCount,
                                                    ]) }}
                                                @else
                                                    {{ $this->formatMoney($item->amount) }}
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 align-top font-medium text-gray-950 dark:text-white">
                                                {{ $this->formatMoney($lineTotal) }}
                                            </td>
                                            <td class="px-5 py-3 align-top">
                                                @unless ($locked)
                                                    <button
                                                        type="button"
                                                        wire:click="togglePaid({{ $item->id }})"
                                                        @class([
                                                            'rounded-md px-2 py-1 text-xs font-semibold',
                                                            'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' => $item->is_paid,
                                                            'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' => ! $item->is_paid,
                                                        ])
                                                    >
                                                        {{ $item->is_paid ? '✓' : '—' }}
                                                    </button>
                                                @else
                                                    <span class="text-xs {{ $item->is_paid ? 'text-success-600' : 'text-gray-400' }}">
                                                        {{ $item->is_paid ? '✓' : '—' }}
                                                    </span>
                                                @endunless
                                            </td>
                                            <td class="px-5 py-3 align-top text-right">
                                                @unless ($locked)
                                                    <div class="flex justify-end gap-3">
                                                        <button type="button" wire:click="startEdit({{ $item->id }})" class="text-sm font-medium text-primary-600 dark:text-primary-400">
                                                            {{ __('budget.edit') }}
                                                        </button>
                                                        <button
                                                            type="button"
                                                            wire:click="deleteItem({{ $item->id }})"
                                                            wire:confirm="{{ __('budget.delete_confirm') }}"
                                                            class="text-sm font-medium text-danger-600 dark:text-danger-400"
                                                        >
                                                            {{ __('budget.delete') }}
                                                        </button>
                                                    </div>
                                                @endunless
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-gray-200 dark:border-white/10">
                                    <td colspan="2" class="px-5 py-3 text-sm font-semibold text-gray-950 dark:text-white">{{ __('budget.footer_total') }}</td>
                                    <td colspan="3" class="px-5 py-3 text-sm font-semibold text-primary-600 dark:text-primary-400">
                                        {{ $this->formatMoney($totals['total']) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Category breakdown --}}
        <div class="xl:col-span-1">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ __('budget.categories_heading') }}</h2>

                @if ($breakdown === [])
                    <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">{{ __('budget.categories_empty') }}</p>
                @else
                    <div class="mt-6 flex justify-center">
                        <div
                            class="relative h-40 w-40 rounded-full"
                            style="background: {{ $this->getDonutGradient() }}"
                        >
                            <div class="absolute inset-[22%] rounded-full bg-white dark:bg-gray-900"></div>
                        </div>
                    </div>

                    <ul class="mt-6 space-y-3">
                        @foreach ($breakdown as $row)
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-block h-3 w-3 shrink-0 rounded-full" style="background: {{ $row['color'] }}"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $row['category']->label() }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('budget.category_share', [
                                            'amount' => $this->formatMoney($row['total']),
                                            'percent' => $row['percent'],
                                        ]) }}
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
