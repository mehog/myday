<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\WeddingEvent;
use App\Models\WeddingMenuOption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class Menus extends Component
{
    use RendersDashboard;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $label = '';

    public bool $is_visible = true;

    public string $sort_order = '0';

    public ?string $flashMessage = null;

    public ?string $flashError = null;

    public function render()
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        return $this->dashboardView('livewire.dashboard.menus', [
            'wedding' => $wedding,
            'menus' => $this->getMenus(),
            'locked' => $this->isLocked(),
            'editingRecord' => $this->editingRecord(),
        ], __('menu.title'), [
            ['label' => __('dashboard.nav.wedding'), 'url' => route('dashboard.wedding')],
            ['label' => __('menu.title'), 'url' => null],
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
     * @return Collection<int, WeddingMenuOption>
     */
    public function getMenus(): Collection
    {
        return $this->wedding()
            ?->menuOptions()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get() ?? collect();
    }

    protected function editingRecord(): ?WeddingMenuOption
    {
        if ($this->editingId === null) {
            return null;
        }

        return $this->wedding()?->menuOptions()->whereKey($this->editingId)->first();
    }

    public function openCreate(): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $this->resetForm();
        $this->sort_order = (string) (((int) $wedding->menuOptions()->max('sort_order')) + 1);
        $this->is_visible = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $menu = $wedding->menuOptions()->whereKey($id)->firstOrFail();
        $this->editingId = $menu->id;
        $this->label = (string) ($menu->label ?? '');
        $this->is_visible = (bool) $menu->is_visible;
        $this->sort_order = (string) $menu->sort_order;
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

        $record = $this->editingRecord();
        $isCustom = $record === null || $record->isCustom();

        $rules = [
            'is_visible' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];

        if ($isCustom) {
            $rules['label'] = ['required', 'string', 'max:255'];
        }

        $data = $this->validate($rules);

        if ($record) {
            $payload = [
                'is_visible' => (bool) $data['is_visible'],
                'sort_order' => (int) $data['sort_order'],
            ];

            if ($record->isCustom()) {
                $payload['label'] = $data['label'];
            }

            $record->update($payload);
        } else {
            $wedding->menuOptions()->create([
                'label' => $data['label'],
                'platform_key' => null,
                'is_visible' => (bool) ($data['is_visible'] ?? true),
                'sort_order' => (int) $data['sort_order'],
            ]);
        }

        $this->closeModal();
        $this->flashMessage = __('dashboard.saved');
    }

    public function delete(int $id): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        $this->ensureWritable($wedding);

        $menu = $wedding->menuOptions()->whereKey($id)->firstOrFail();

        if ($menu->isPlatform()) {
            $this->flashError = __('menu.cannot_delete_platform');

            return;
        }

        if ($this->menuOptionIsInUse($menu)) {
            $this->flashError = __('menu.cannot_delete_in_use');

            return;
        }

        $menu->delete();
        $this->flashError = null;
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

        $menus = $this->getMenus()->values();
        $index = $menus->search(fn (WeddingMenuOption $item) => $item->id === $id);

        if ($index === false) {
            return;
        }

        $swapIndex = $index + $direction;

        if ($swapIndex < 0 || $swapIndex >= $menus->count()) {
            return;
        }

        $current = $menus[$index];
        $other = $menus[$swapIndex];
        $currentSort = $current->sort_order;
        $current->update(['sort_order' => $other->sort_order]);
        $other->update(['sort_order' => $currentSort]);
    }

    protected function menuOptionIsInUse(WeddingMenuOption $record): bool
    {
        return DB::table('guests')
            ->where(function ($query) use ($record): void {
                $query->where('menu_option_id', $record->id)
                    ->orWhere('plus_one_menu_option_id', $record->id);
            })
            ->exists()
            || DB::table('guest_children')
                ->where('menu_option_id', $record->id)
                ->exists();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->label = '';
        $this->is_visible = true;
        $this->sort_order = '0';
        $this->resetValidation();
    }
}
