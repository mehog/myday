<?php

namespace App\Services;

use App\GuestMessageType;
use App\Models\GuestMessage;
use App\Models\WeddingEvent;
use App\Support\MediaDisk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GuestMessageMediaGallery
{
    public const PER_PAGE = 5;

    public function countPhotos(WeddingEvent $wedding): int
    {
        return $this->messagesQuery($wedding, GuestMessageType::Photo)
            ->get(['file_paths'])
            ->sum(fn (GuestMessage $message): int => count($message->file_paths ?? []));
    }

    public function countVideos(WeddingEvent $wedding): int
    {
        return $this->messagesQuery($wedding, GuestMessageType::Video)
            ->get(['file_paths'])
            ->sum(fn (GuestMessage $message): int => count($message->file_paths ?? []));
    }

    public function hasPhotos(WeddingEvent $wedding): bool
    {
        return $this->messagesQuery($wedding, GuestMessageType::Photo)->exists();
    }

    public function hasVideos(WeddingEvent $wedding): bool
    {
        return $this->messagesQuery($wedding, GuestMessageType::Video)->exists();
    }

    public function messagesQuery(WeddingEvent $wedding, GuestMessageType $type): Builder
    {
        return GuestMessage::query()
            ->where('wedding_event_id', $wedding->id)
            ->where('type', $type)
            ->whereNotNull('file_paths')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * @return list<array{key: string, message_id: int, index: int, url: string, name: string, sender_name: string}>
     */
    public function flattenMessage(GuestMessage $message): array
    {
        $items = [];

        foreach (array_values($message->file_paths ?? []) as $index => $path) {
            if (! is_string($path) || $path === '') {
                continue;
            }

            $url = MediaDisk::url($path);

            if (! filled($url)) {
                continue;
            }

            $items[] = [
                'key' => $message->id.':'.$index,
                'message_id' => $message->id,
                'index' => $index,
                'url' => $url,
                'name' => basename($path),
                'sender_name' => $message->sender_name,
            ];
        }

        return $items;
    }

    /**
     * @return list<array{index: int, url: string, name: string}>
     */
    public function photosForLightbox(GuestMessage $message): array
    {
        return collect($message->file_paths ?? [])
            ->values()
            ->map(fn (string $path, int $index): array => [
                'index' => $index,
                'url' => MediaDisk::url($path),
                'name' => basename($path),
            ])
            ->filter(fn (array $photo): bool => filled($photo['url']))
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, GuestMessage>  $messages
     * @return list<array{key: string, message_id: int, index: int, url: string, name: string, sender_name: string}>
     */
    public function flattenMessages(Collection $messages): array
    {
        $items = [];

        foreach ($messages as $message) {
            array_push($items, ...$this->flattenMessage($message));
        }

        return $items;
    }
}
