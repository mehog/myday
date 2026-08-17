<?php

namespace App\Livewire\Dashboard;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Livewire\Component;

class NotificationsBell extends Component
{
    public function render()
    {
        $user = auth()->user();

        $notifications = $user === null
            ? collect()
            : $user->notifications()
                ->orderByRaw('read_at is null desc')
                ->latest()
                ->limit(20)
                ->get();

        return view('livewire.dashboard.notifications-bell', [
            'unreadCount' => $user?->unreadNotifications()->count() ?? 0,
            'items' => $this->present($notifications),
        ]);
    }

    /**
     * @param  Collection<int, DatabaseNotification>  $notifications
     * @return list<array{id: string, title: string, body: string, url: string, unread: bool, created: string}>
     */
    protected function present(Collection $notifications): array
    {
        return $notifications
            ->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->getKey(),
                'title' => $this->stringFromData($notification->data['title'] ?? null),
                'body' => $this->stringFromData($notification->data['body'] ?? null),
                'url' => $this->actionUrl($notification),
                'unread' => $notification->read_at === null,
                'created' => $notification->created_at?->diffForHumans() ?? '',
            ])
            ->all();
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

        $this->redirect($this->actionUrl($notification));
    }

    public function markAllAsRead(): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $user->unreadNotifications->markAsRead();
    }

    protected function actionUrl(DatabaseNotification $notification): string
    {
        $actions = $notification->data['actions'] ?? [];

        if (is_array($actions)) {
            foreach ($actions as $action) {
                if (is_array($action) && is_string($action['url'] ?? null) && $action['url'] !== '') {
                    return $action['url'];
                }
            }
        }

        return route('dashboard.messages');
    }

    protected function stringFromData(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $locale = app()->getLocale();

            if (isset($value[$locale]) && is_string($value[$locale])) {
                return $value[$locale];
            }

            $first = reset($value);

            return is_string($first) ? $first : '';
        }

        return '';
    }
}
