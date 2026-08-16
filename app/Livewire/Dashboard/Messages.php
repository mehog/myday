<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\GuestMessage;
use App\Models\WeddingEvent;
use Illuminate\Support\Collection;
use Livewire\Component;

class Messages extends Component
{
    use RendersDashboard;

    public function mount(): void
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent) {
            return;
        }

        $wedding->guestMessages()
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);
    }

    public function render()
    {
        return $this->dashboardView('livewire.dashboard.messages', [
            'wedding' => $this->wedding(),
            'messages' => $this->getMessages(),
        ], __('dashboard.messages_title'), [
            ['label' => __('dashboard.nav.messages'), 'url' => null],
        ]);
    }

    protected function wedding(): ?WeddingEvent
    {
        return auth()->user()?->weddingEvent;
    }

    /**
     * @return Collection<int, GuestMessage>
     */
    public function getMessages(): Collection
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent) {
            return collect();
        }

        return $wedding->guestMessages()
            ->with(['guest'])
            ->latest()
            ->get();
    }
}
