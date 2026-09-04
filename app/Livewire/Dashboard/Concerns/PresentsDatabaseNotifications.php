<?php

namespace App\Livewire\Dashboard\Concerns;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

trait PresentsDatabaseNotifications
{
    /**
     * @param  Collection<int, DatabaseNotification>  $notifications
     * @return list<array{id: string, title: string, body: string, url: string, unread: bool, created: string}>
     */
    protected function presentNotifications(Collection $notifications): array
    {
        return $notifications
            ->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->getKey(),
                'title' => $this->notificationStringFromData($notification->data['title'] ?? null),
                'body' => $this->notificationStringFromData($notification->data['body'] ?? null),
                'url' => $this->notificationActionUrl($notification),
                'unread' => $notification->read_at === null,
                'created' => $notification->created_at?->diffForHumans() ?? '',
            ])
            ->all();
    }

    protected function notificationActionUrl(DatabaseNotification $notification): string
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

    protected function notificationStringFromData(mixed $value): string
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
