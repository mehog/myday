<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\ScheduleItem;
use App\Models\WeddingEvent;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class Schedule extends Component
{
    use RendersDashboard;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $time = '';

    public string $title = '';

    public string $description = '';

    public string $sort_order = '0';

    public ?string $flashMessage = null;

    public function render()
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        return $this->dashboardView('livewire.dashboard.schedule', [
            'items' => $this->getItems(),
            'locked' => $this->isLocked(),
        ], __('schedule.title'), [
            ['label' => __('dashboard.nav.wedding'), 'url' => route('dashboard.wedding')],
            ['label' => __('schedule.title'), 'url' => null],
        ], backUrl: route('dashboard.wedding'));
    }

    protected function wedding(): ?WeddingEvent
    {
        return auth()->user()?->weddingEvent;
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

    /**
     * @return Collection<int, ScheduleItem>
     */
    public function getItems(): Collection
    {
        return $this->wedding()
            ?->scheduleItems()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get() ?? collect();
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

        $item = $wedding->scheduleItems()->whereKey($id)->firstOrFail();
        $this->editingId = $item->id;
        $this->time = is_string($item->time)
            ? substr($item->time, 0, 5)
            : ($item->time?->format('H:i') ?? '');
        $this->title = (string) $item->title;
        $this->description = (string) ($item->description ?? '');
        $this->sort_order = (string) $item->sort_order;
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
            'time' => ['required', 'date_format:H:i'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $payload = [
            'time' => $data['time'],
            'title' => $data['title'],
            'description' => filled($data['description'] ?? null) ? $data['description'] : null,
            'sort_order' => (int) $data['sort_order'],
        ];

        if ($this->editingId) {
            $wedding->scheduleItems()->whereKey($this->editingId)->firstOrFail()->update($payload);
        } else {
            $wedding->scheduleItems()->create($payload);
        }

        $this->closeModal();
        $this->flashMessage = __('dashboard.saved');
    }

    public function delete(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);
        $wedding->scheduleItems()->whereKey($id)->delete();
        $this->flashMessage = __('dashboard.saved');
    }

    public function moveUp(int $id): void
    {
        $this->swapSort($id, -1);
    }

    public function moveDown(int $id): void
    {
        $this->swapSort($id, 1);
    }

    protected function swapSort(int $id, int $direction): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $items = $this->getItems()->values();
        $index = $items->search(fn (ScheduleItem $item) => $item->id === $id);

        if ($index === false) {
            return;
        }

        $swapIndex = $index + $direction;

        if ($swapIndex < 0 || $swapIndex >= $items->count()) {
            return;
        }

        $current = $items[$index];
        $other = $items[$swapIndex];
        $currentSort = $current->sort_order;
        $current->update(['sort_order' => $other->sort_order]);
        $other->update(['sort_order' => $currentSort]);
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->time = '';
        $this->title = '';
        $this->description = '';
        $this->sort_order = '0';
        $this->resetValidation();
    }
}
