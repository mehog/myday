<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\WeddingEvent;
use App\Models\WeddingTask;
use App\Services\WeddingChecklistPresenter;
use App\WeddingTaskPeriod;
use App\WeddingTaskPriority;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class Checklist extends Component
{
    use RendersDashboard;

    #[Url]
    public string $tab = 'all';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $due_date = '';

    public string $notes = '';

    public ?string $flashMessage = null;

    public function mount(): void
    {
        if (! in_array($this->tab, ['all', 'predefined', 'mine', 'completed'], true)) {
            $this->tab = 'all';
        }
    }

    public function render(WeddingChecklistPresenter $presenter)
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        $allRows = $presenter->rows($wedding);
        $rows = $this->filteredRows($allRows);
        $summary = $presenter->summarize($allRows);
        $grouped = $presenter->groupByPeriod($rows)->map(function (Collection $periodRows, string $period) use ($presenter): array {
            return [
                'period' => $period,
                'rows' => $periodRows,
                'summary' => $presenter->periodSummary($periodRows),
            ];
        })->values();

        return $this->dashboardView('livewire.dashboard.checklist', [
            'wedding' => $wedding,
            'locked' => $this->isLocked(),
            'summary' => $summary,
            'grouped' => $grouped,
            'isEmpty' => $rows->isEmpty(),
        ], __('checklist.title'), [
            ['label' => __('checklist.title'), 'url' => null],
        ], backUrl: route('dashboard.more'));
    }

    public function isLocked(): bool
    {
        $wedding = $this->wedding();

        return $wedding instanceof WeddingEvent && $wedding->isCoupleMutationLocked();
    }

    public function toggle(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $task = $wedding->tasks()->whereKey($id)->firstOrFail();
        $task->update([
            'completed_at' => $task->completed_at ? null : now(),
        ]);
    }

    public function openCreate(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $task = $this->customTask($wedding, $id);
        $this->editingId = $task->id;
        $this->title = (string) $task->title;
        $this->due_date = $task->due_date?->format('Y-m-d') ?? '';
        $this->notes = (string) ($task->notes ?? '');
        $this->showModal = true;
    }

    #[On('close-dashboard-modal')]
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $payload = [
            'title' => $data['title'],
            'notes' => filled($data['notes'] ?? null) ? $data['notes'] : null,
            'due_date' => filled($data['due_date'] ?? null) ? $data['due_date'] : null,
            'period' => WeddingTaskPeriod::Custom,
            'priority' => WeddingTaskPriority::Normal,
        ];

        if ($this->editingId) {
            $this->customTask($wedding, $this->editingId)->update($payload);
        } else {
            $maxSort = (int) $wedding->tasks()->max('sort_order');
            $wedding->tasks()->create([
                ...$payload,
                'system_key' => null,
                'sort_order' => $maxSort + 10,
            ]);
        }

        $this->closeModal();
        $this->flashMessage = __('checklist.saved');
    }

    public function delete(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);
        $this->customTask($wedding, $id)->delete();
        $this->flashMessage = __('checklist.saved');
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    protected function filteredRows(Collection $rows): Collection
    {
        return $rows->filter(function (array $row): bool {
            $task = $row['task'];

            return match ($this->tab) {
                'predefined' => $task->isSystem() && ! $task->isCompleted(),
                'mine' => ! $task->isSystem() && ! $task->isCompleted(),
                'completed' => $task->isCompleted(),
                default => true,
            };
        })->values();
    }

    protected function wedding(): ?WeddingEvent
    {
        return auth()->user()?->weddingEvent;
    }

    protected function ensureWritable(WeddingEvent $wedding): void
    {
        abort_if($wedding->isCoupleMutationLocked(), 403);
    }

    protected function customTask(WeddingEvent $wedding, int $id): WeddingTask
    {
        $task = $wedding->tasks()->whereKey($id)->firstOrFail();
        abort_if($task->isSystem(), 403);

        return $task;
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->due_date = '';
        $this->notes = '';
        $this->resetValidation();
    }
}
