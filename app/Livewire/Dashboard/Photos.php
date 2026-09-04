<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\EventPhoto;
use App\Models\WeddingEvent;
use App\Support\MediaDisk;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Photos extends Component
{
    use RendersDashboard;
    use WithFileUploads;

    public bool $showModal = false;

    public ?int $editingId = null;

    public $photo = null;

    public string $title = '';

    public string $sort_order = '0';

    public ?string $flashMessage = null;

    public ?string $flashError = null;

    public function render()
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        return $this->dashboardView('livewire.dashboard.photos', [
            'photos' => $this->getPhotos(),
            'locked' => $this->isLocked(),
        ], __('photos.title'), [
            ['label' => __('dashboard.nav.wedding'), 'url' => route('dashboard.wedding')],
            ['label' => __('photos.title'), 'url' => null],
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
     * @return Collection<int, EventPhoto>
     */
    public function getPhotos(): Collection
    {
        return $this->wedding()
            ?->eventPhotos()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get() ?? collect();
    }

    public function photoUrl(EventPhoto $photo): ?string
    {
        return MediaDisk::url($photo->path);
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

        $photo = $wedding->eventPhotos()->whereKey($id)->firstOrFail();
        $this->editingId = $photo->id;
        $this->title = (string) ($photo->title ?? '');
        $this->sort_order = (string) $photo->sort_order;
        $this->photo = null;
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

        $rules = [
            'title' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];

        if ($this->editingId === null) {
            $rules['photo'] = ['required', 'image', 'max:10240'];
        } else {
            $rules['photo'] = ['nullable', 'image', 'max:10240'];
        }

        $data = $this->validate($rules);

        $payload = [
            'title' => filled($data['title'] ?? null) ? $data['title'] : null,
            'sort_order' => (int) $data['sort_order'],
        ];

        if ($this->photo) {
            $payload['path'] = $this->photo->store('event-photos', config('filesystems.media_disk'));
        }

        if ($this->editingId) {
            $wedding->eventPhotos()->whereKey($this->editingId)->firstOrFail()->update($payload);
        } else {
            $wedding->eventPhotos()->create($payload);
        }

        $this->closeModal();
        $this->flashMessage = __('dashboard.saved');
    }

    public function delete(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);
        $wedding->eventPhotos()->whereKey($id)->delete();
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

        $photos = $this->getPhotos()->values();
        $index = $photos->search(fn (EventPhoto $item) => $item->id === $id);

        if ($index === false) {
            return;
        }

        $swapIndex = $index + $direction;

        if ($swapIndex < 0 || $swapIndex >= $photos->count()) {
            return;
        }

        $current = $photos[$index];
        $other = $photos[$swapIndex];
        $currentSort = $current->sort_order;
        $current->update(['sort_order' => $other->sort_order]);
        $other->update(['sort_order' => $currentSort]);
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->photo = null;
        $this->title = '';
        $this->sort_order = '0';
        $this->resetValidation();
    }
}
