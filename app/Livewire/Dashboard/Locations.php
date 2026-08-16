<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\WeddingEvent;
use App\Models\WeddingLocation;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class Locations extends Component
{
    use RendersDashboard;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $label = '';

    public string $name = '';

    public string $address = '';

    public bool $is_primary = false;

    public string $sort_order = '0';

    public string $lat = '';

    public string $lng = '';

    public ?string $flashMessage = null;

    public ?string $flashError = null;

    public function render()
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        return $this->dashboardView('livewire.dashboard.locations', [
            'wedding' => $wedding,
            'locations' => $this->getLocations(),
            'locked' => $this->isLocked(),
        ], __('locations.title'), [
            ['label' => __('dashboard.nav.wedding'), 'url' => route('dashboard.wedding')],
            ['label' => __('locations.title'), 'url' => null],
        ]);
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
     * @return Collection<int, WeddingLocation>
     */
    public function getLocations(): Collection
    {
        return $this->wedding()
            ?->locations()
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
        $this->is_primary = ! $wedding->locations()->exists();
        $this->sort_order = (string) (((int) $wedding->locations()->max('sort_order')) + 1);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $location = $wedding->locations()->whereKey($id)->firstOrFail();

        $this->editingId = $location->id;
        $this->label = (string) ($location->label ?? '');
        $this->name = (string) $location->name;
        $this->address = (string) ($location->address ?? '');
        $this->is_primary = (bool) $location->is_primary;
        $this->sort_order = (string) $location->sort_order;
        $this->lat = $location->lat !== null ? (string) $location->lat : '';
        $this->lng = $location->lng !== null ? (string) $location->lng : '';
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
            'label' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
        ]);

        $payload = [
            'label' => filled($data['label'] ?? null) ? $data['label'] : null,
            'name' => $data['name'],
            'address' => filled($data['address'] ?? null) ? $data['address'] : null,
            'is_primary' => (bool) $data['is_primary'],
            'sort_order' => (int) $data['sort_order'],
            'lat' => filled($this->lat) ? $this->lat : null,
            'lng' => filled($this->lng) ? $this->lng : null,
        ];

        if ($this->editingId) {
            $wedding->locations()->whereKey($this->editingId)->firstOrFail()->update($payload);
        } else {
            $wedding->locations()->create($payload);
        }

        $this->normalizePrimaryAndLegacy($wedding);
        $this->closeModal();
        $this->flashMessage = __('dashboard.saved');
    }

    public function delete(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $wedding->locations()->whereKey($id)->delete();
        $this->normalizePrimaryAndLegacy($wedding->fresh());
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

        $locations = $this->getLocations()->values();
        $index = $locations->search(fn (WeddingLocation $loc) => $loc->id === $id);

        if ($index === false) {
            return;
        }

        $swapIndex = $index + $direction;

        if ($swapIndex < 0 || $swapIndex >= $locations->count()) {
            return;
        }

        $current = $locations[$index];
        $other = $locations[$swapIndex];
        $currentSort = $current->sort_order;
        $current->update(['sort_order' => $other->sort_order]);
        $other->update(['sort_order' => $currentSort]);
    }

    protected function normalizePrimaryAndLegacy(WeddingEvent $event): void
    {
        $locations = $event->locations()->orderBy('sort_order')->orderBy('id')->get();

        if ($locations->isEmpty()) {
            $event->forceFill([
                'location_name' => null,
                'location_address' => null,
                'location_lat' => null,
                'location_lng' => null,
            ])->save();

            return;
        }

        $primary = $locations->firstWhere('is_primary', true) ?? $locations->first();

        foreach ($locations as $location) {
            $shouldBePrimary = $location->is($primary);

            if ((bool) $location->is_primary !== $shouldBePrimary) {
                $location->forceFill(['is_primary' => $shouldBePrimary])->save();
            }
        }

        $event->syncLegacyLocationFromPrimary();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->label = '';
        $this->name = '';
        $this->address = '';
        $this->is_primary = false;
        $this->sort_order = '0';
        $this->lat = '';
        $this->lng = '';
        $this->resetValidation();
    }
}
