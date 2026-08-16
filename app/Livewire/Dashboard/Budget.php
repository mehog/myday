<?php

namespace App\Livewire\Dashboard;

use App\BudgetCalculationType;
use App\BudgetCategory;
use App\BudgetGuestMode;
use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\WeddingBudgetItem;
use App\Models\WeddingEvent;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Budget extends Component
{
    use RendersDashboard;

    public string $newName = '';

    public string $newCategory = '';

    public string $newCalculationType = '';

    public string $newAmount = '';

    public string $guestMode = '';

    public string $guestCountInput = '';

    public string $targetInput = '';

    public string $currency = '';

    public ?int $editingId = null;

    public string $editName = '';

    public string $editCategory = '';

    public string $editCalculationType = '';

    public string $editAmount = '';

    public bool $showSettings = false;

    public ?string $flashMessage = null;

    public function mount(): void
    {
        $wedding = $this->wedding();

        abort_unless($wedding instanceof WeddingEvent, 404);

        $this->newCategory = BudgetCategory::SalaIVecera->value;
        $this->newCalculationType = BudgetCalculationType::Fixed->value;
        $this->syncSettingsFromWedding($wedding);

        if ($wedding->budget_currency === null) {
            $this->showSettings = true;
        }
    }

    protected function notify(string $message): void
    {
        $this->flashMessage = $message;
    }

    public function render()
    {
        return $this->dashboardView('livewire.dashboard.budget', [], __('budget.page_title'), [
            ['label' => __('budget.nav_label'), 'url' => null],
        ]);
    }

    public function getWeddingProperty(): ?WeddingEvent
    {
        return auth()->user()?->weddingEvent;
    }

    protected function wedding(): ?WeddingEvent
    {
        return auth()->user()?->weddingEvent;
    }

    protected function syncSettingsFromWedding(WeddingEvent $wedding): void
    {
        $this->guestMode = ($wedding->budget_guest_mode ?? BudgetGuestMode::Confirmed)->value;
        $this->guestCountInput = (string) $wedding->budgetGuestCount();
        $this->targetInput = $wedding->budget_target !== null
            ? number_format((float) $wedding->budget_target, 2, '.', '')
            : '';
        $this->currency = $wedding->budgetCurrency();
    }

    public function isLocked(): bool
    {
        $wedding = $this->wedding();

        return $wedding instanceof WeddingEvent && $wedding->isCoupleMutationLocked();
    }

    protected function ensureWritable(WeddingEvent $wedding): void
    {
        abort_if($wedding->isCoupleMutationLocked(), 403);
    }

    protected function resolveItemName(?string $name, string $categoryValue): string
    {
        $trimmed = trim((string) $name);

        if ($trimmed !== '') {
            return $trimmed;
        }

        return BudgetCategory::from($categoryValue)->label();
    }

    /**
     * @return Collection<int, WeddingBudgetItem>
     */
    public function getItems(): Collection
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent) {
            return collect();
        }

        return $wedding->budgetItems()->get();
    }

    /**
     * @return array{total: string, paid: string, unpaid: string, per_person: string}
     */
    public function getTotals(): array
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent) {
            return [
                'total' => '0.00',
                'paid' => '0.00',
                'unpaid' => '0.00',
                'per_person' => '0.00',
            ];
        }

        $wedding->loadMissing('budgetItems');

        return $wedding->budgetTotals();
    }

    public function getGuestCount(): int
    {
        return $this->wedding()?->budgetGuestCount() ?? 0;
    }

    public function formatMoney(string|float|int $amount): string
    {
        $value = number_format((float) $amount, 2, ',', '.');

        return $value.' '.$this->currency;
    }

    /**
     * @return list<array{category: BudgetCategory, total: string, percent: float, color: string}>
     */
    public function getCategoryBreakdown(): array
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent) {
            return [];
        }

        $guestCount = $wedding->budgetGuestCount();
        $totals = [];

        foreach ($wedding->budgetItems as $item) {
            $key = $item->category->value;
            $totals[$key] = bcadd($totals[$key] ?? '0.00', $item->lineTotal($guestCount), 2);
        }

        $grand = '0.00';
        foreach ($totals as $amount) {
            $grand = bcadd($grand, $amount, 2);
        }

        if (bccomp($grand, '0', 2) === 0) {
            return [];
        }

        $breakdown = [];

        foreach ($totals as $key => $amount) {
            $category = BudgetCategory::from($key);
            $percent = round(((float) $amount / (float) $grand) * 100);

            $breakdown[] = [
                'category' => $category,
                'total' => $amount,
                'percent' => $percent,
                'color' => $category->chartColor(),
            ];
        }

        usort($breakdown, fn (array $a, array $b): int => bccomp($b['total'], $a['total'], 2));

        return $breakdown;
    }

    /**
     * @return list<array{name: string, category: string, calculation_type: string, amount: string}>
     */
    public function getSuggestions(): array
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent || $wedding->budgetItems()->exists()) {
            return [];
        }

        $suggestions = [];
        $primary = $wedding->primaryLocation();

        if ($primary !== null && filled($primary->displayName())) {
            $suggestions[] = [
                'name' => __('budget.suggestion_venue', ['name' => $primary->displayName()]),
                'category' => BudgetCategory::SalaIVecera->value,
                'calculation_type' => BudgetCalculationType::PerPerson->value,
                'amount' => '45.00',
            ];
        }

        if ($wedding->accommodation_enabled) {
            $suggestions[] = [
                'name' => __('budget.suggestion_accommodation'),
                'category' => BudgetCategory::Smjestaj->value,
                'calculation_type' => BudgetCalculationType::Fixed->value,
                'amount' => '0.00',
            ];
        }

        $suggestions[] = [
            'name' => __('budget.suggestion_invitations'),
            'category' => BudgetCategory::PozivniceITisak->value,
            'calculation_type' => BudgetCalculationType::Fixed->value,
            'amount' => '80.00',
        ];

        return $suggestions;
    }

    public function saveGuestSettings(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $data = $this->validate([
            'guestMode' => ['required', Rule::enum(BudgetGuestMode::class)],
            'guestCountInput' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ], [
            'guestCountInput.min' => __('budget.validation.guest_count_min'),
        ]);

        $mode = BudgetGuestMode::from($data['guestMode']);

        $wedding->update([
            'budget_guest_mode' => $mode,
            'budget_guest_count' => $mode === BudgetGuestMode::Manual
                ? (int) ($data['guestCountInput'] ?? 0)
                : $wedding->budget_guest_count,
        ]);

        $this->syncSettingsFromWedding($wedding->fresh());

        $this->notify(__('budget.guests_saved'));
    }

    public function saveCurrency(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $data = $this->validate([
            'currency' => ['required', Rule::in(['EUR', 'BAM'])],
        ]);

        $wedding->update([
            'budget_currency' => $data['currency'],
        ]);

        $this->syncSettingsFromWedding($wedding->fresh());

        $this->notify(__('budget.currency_saved'));
    }

    public function updatedGuestMode(string $value): void
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent) {
            return;
        }

        $mode = BudgetGuestMode::tryFrom($value);

        if ($mode === null) {
            return;
        }

        $this->guestCountInput = (string) match ($mode) {
            BudgetGuestMode::Manual => max(0, (int) ($wedding->budget_guest_count ?? $wedding->confirmedHeadcount())),
            BudgetGuestMode::Confirmed => $wedding->confirmedHeadcount(),
            BudgetGuestMode::Invited => $wedding->invitedHeadcount(),
        };
    }

    public function useAsEstimate(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $count = match (BudgetGuestMode::from($this->guestMode)) {
            BudgetGuestMode::Manual => max(0, (int) $this->guestCountInput),
            BudgetGuestMode::Confirmed => $wedding->confirmedHeadcount(),
            BudgetGuestMode::Invited => $wedding->invitedHeadcount(),
        };

        $wedding->update([
            'budget_guest_mode' => BudgetGuestMode::Manual,
            'budget_guest_count' => $count,
        ]);

        $this->syncSettingsFromWedding($wedding->fresh());

        $this->notify(__('budget.guests_saved'));
    }

    public function saveTarget(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $data = $this->validate([
            'targetInput' => ['nullable', 'numeric', 'min:0'],
        ]);

        $wedding->update([
            'budget_target' => filled($data['targetInput']) ? $data['targetInput'] : null,
        ]);

        $this->syncSettingsFromWedding($wedding->fresh());

        $this->notify(__('budget.target_saved'));
    }

    public function addItem(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $data = $this->validate([
            'newName' => ['nullable', 'string', 'max:255'],
            'newCategory' => ['required', Rule::enum(BudgetCategory::class)],
            'newCalculationType' => ['required', Rule::enum(BudgetCalculationType::class)],
            'newAmount' => ['required', 'numeric', 'min:0'],
        ], [
            'newAmount.required' => __('budget.validation.amount_required'),
            'newAmount.min' => __('budget.validation.amount_min'),
        ]);

        $maxSort = (int) $wedding->budgetItems()->max('sort_order');

        $wedding->budgetItems()->create([
            'name' => $this->resolveItemName($data['newName'] ?? null, $data['newCategory']),
            'category' => $data['newCategory'],
            'calculation_type' => $data['newCalculationType'],
            'amount' => $data['newAmount'],
            'is_paid' => false,
            'sort_order' => $maxSort + 1,
        ]);

        $this->newName = '';
        $this->newAmount = '';
        $this->newCategory = BudgetCategory::SalaIVecera->value;
        $this->newCalculationType = BudgetCalculationType::Fixed->value;

        $this->notify(__('budget.item_added'));
    }

    public function addSuggestion(int $index): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $suggestion = $this->getSuggestions()[$index] ?? null;
        abort_unless(is_array($suggestion), 404);

        $maxSort = (int) $wedding->budgetItems()->max('sort_order');

        $wedding->budgetItems()->create([
            'name' => $suggestion['name'],
            'category' => $suggestion['category'],
            'calculation_type' => $suggestion['calculation_type'],
            'amount' => $suggestion['amount'],
            'is_paid' => false,
            'sort_order' => $maxSort + 1,
        ]);

        $this->notify(__('budget.item_added'));
    }

    public function startEdit(int $itemId): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $item = $wedding->budgetItems()->whereKey($itemId)->firstOrFail();

        $this->editingId = $item->id;
        $this->editName = $item->name === $item->category->label() ? '' : $item->name;
        $this->editCategory = $item->category->value;
        $this->editCalculationType = $item->calculation_type->value;
        $this->editAmount = number_format((float) $item->amount, 2, '.', '');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editName = '';
        $this->editCategory = '';
        $this->editCalculationType = '';
        $this->editAmount = '';
    }

    public function saveEdit(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);
        abort_unless($this->editingId !== null, 404);

        $data = $this->validate([
            'editName' => ['nullable', 'string', 'max:255'],
            'editCategory' => ['required', Rule::enum(BudgetCategory::class)],
            'editCalculationType' => ['required', Rule::enum(BudgetCalculationType::class)],
            'editAmount' => ['required', 'numeric', 'min:0'],
        ], [
            'editAmount.required' => __('budget.validation.amount_required'),
            'editAmount.min' => __('budget.validation.amount_min'),
        ]);

        $item = $wedding->budgetItems()->whereKey($this->editingId)->firstOrFail();

        $item->update([
            'name' => $this->resolveItemName($data['editName'] ?? null, $data['editCategory']),
            'category' => $data['editCategory'],
            'calculation_type' => $data['editCalculationType'],
            'amount' => $data['editAmount'],
        ]);

        $this->cancelEdit();

        $this->notify(__('budget.item_updated'));
    }

    public function togglePaid(int $itemId): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $item = $wedding->budgetItems()->whereKey($itemId)->firstOrFail();
        $item->update(['is_paid' => ! $item->is_paid]);

        $this->notify(__('budget.item_paid_updated'));
    }

    public function deleteItem(int $itemId): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $wedding->budgetItems()->whereKey($itemId)->delete();

        if ($this->editingId === $itemId) {
            $this->cancelEdit();
        }

        $this->notify(__('budget.item_deleted'));
    }

    public function getDonutGradient(): string
    {
        $breakdown = $this->getCategoryBreakdown();

        if ($breakdown === []) {
            return 'conic-gradient(#e5e7eb 0deg 360deg)';
        }

        $stops = [];
        $cursor = 0.0;

        foreach ($breakdown as $row) {
            $start = $cursor;
            $cursor += ($row['percent'] / 100) * 360;
            $stops[] = sprintf('%s %.2fdeg %.2fdeg', $row['color'], $start, $cursor);
        }

        return 'conic-gradient('.implode(', ', $stops).')';
    }
}
