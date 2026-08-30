<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\PushNotificationLog;
use App\Models\WeddingEvent;
use Illuminate\Support\Collection;
use Livewire\Component;

class Pushes extends Component
{
    use RendersDashboard;

    public function render()
    {
        $wedding = $this->wedding();

        return $this->dashboardView('livewire.dashboard.pushes', [
            'wedding' => $wedding,
            'logs' => $this->getLogs(),
            'subscriberCount' => $this->subscriberCount(),
        ], __('dashboard.pushes_title'), [
            ['label' => __('dashboard.nav.pushes'), 'url' => null],
        ], backUrl: route('dashboard.more'));
    }

    protected function wedding(): ?WeddingEvent
    {
        return auth()->user()?->weddingEvent;
    }

    /**
     * @return Collection<int, PushNotificationLog>
     */
    public function getLogs(): Collection
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent) {
            return collect();
        }

        return PushNotificationLog::query()
            ->where('wedding_event_id', $wedding->id)
            ->latest()
            ->get();
    }

    public function subscriberCount(): int
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent) {
            return 0;
        }

        return $wedding->guests()
            ->whereHas('pushSubscriptions')
            ->count();
    }

    public function deleteLog(int $logId): void
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);

        PushNotificationLog::query()
            ->where('wedding_event_id', $wedding->id)
            ->whereKey($logId)
            ->firstOrFail()
            ->delete();
    }
}
