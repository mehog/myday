<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\PresentsDatabaseNotifications;
use App\Livewire\Dashboard\Concerns\RendersDashboard;
use Livewire\Component;

class Notifications extends Component
{
    use PresentsDatabaseNotifications;
    use RendersDashboard;

    public function render()
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $notifications = $user->notifications()
            ->orderByRaw('read_at is null desc')
            ->latest()
            ->limit(50)
            ->get();

        return $this->dashboardView('livewire.dashboard.notifications', [
            'unreadCount' => $user->unreadNotifications()->count(),
            'items' => $this->presentNotifications($notifications),
        ], __('dashboard.notifications_title'), [
            ['label' => __('dashboard.nav.more'), 'url' => route('dashboard.more')],
            ['label' => __('dashboard.notifications_title'), 'url' => null],
        ], backUrl: route('dashboard.more'));
    }

    public function openNotification(string $id): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $notification = $user->notifications()->whereKey($id)->first();

        if ($notification === null) {
            return;
        }

        $notification->markAsRead();

        $this->redirect($this->notificationActionUrl($notification));
    }

    public function markAllAsRead(): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $user->unreadNotifications->markAsRead();
    }
}
