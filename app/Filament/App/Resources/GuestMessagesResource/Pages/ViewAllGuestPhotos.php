<?php

namespace App\Filament\App\Resources\GuestMessagesResource\Pages;

use App\Filament\App\Resources\GuestMessagesResource;
use App\GuestMessageType;
use App\Models\GuestMessage;
use App\Support\MediaDisk;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class ViewAllGuestPhotos extends Page
{
    protected static string $resource = GuestMessagesResource::class;

    protected string $view = 'filament.app.resources.guest-messages.all-photo-gallery';

    public const PER_PAGE = 5;

    /**
     * @var list<array{key: string, message_id: int, index: int, url: string, name: string, sender_name: string}>
     */
    public array $photos = [];

    public int $page = 1;

    public bool $hasMore = true;

    public bool $isLoading = false;

    public int $totalPhotoCount = 0;

    public function mount(): void
    {
        abort_unless(auth()->user()?->weddingEvent !== null, 403);

        $this->totalPhotoCount = $this->countPhotos();
        $this->loadMore();
    }

    public function getTitle(): string
    {
        return __('app.guest_messages_all_photos_title');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPhotos')
                ->label(__('app.guest_messages_download_photos'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(route('guest-messages.photos.download'))
                ->visible(fn (): bool => $this->totalPhotoCount > 0),
            Action::make('back')
                ->label(__('app.guest_messages_back'))
                ->icon('heroicon-o-arrow-left')
                ->url(GuestMessagesResource::getUrl())
                ->color('gray'),
        ];
    }

    public function loadMore(): void
    {
        if ($this->isLoading || ! $this->hasMore) {
            return;
        }

        $weddingEventId = auth()->user()?->weddingEvent?->id;

        if (! $weddingEventId) {
            $this->hasMore = false;

            return;
        }

        $this->isLoading = true;

        $messages = $this->photoMessagesQuery($weddingEventId)
            ->forPage($this->page, self::PER_PAGE)
            ->get();

        if ($messages->isEmpty()) {
            $this->hasMore = false;
            $this->isLoading = false;

            return;
        }

        foreach ($messages as $message) {
            foreach (array_values($message->file_paths ?? []) as $index => $path) {
                if (! is_string($path) || $path === '') {
                    continue;
                }

                $url = MediaDisk::url($path);

                if (! filled($url)) {
                    continue;
                }

                $this->photos[] = [
                    'key' => $message->id.':'.$index,
                    'message_id' => $message->id,
                    'index' => $index,
                    'url' => $url,
                    'name' => basename($path),
                    'sender_name' => $message->sender_name,
                ];
            }
        }

        $this->page++;
        $this->hasMore = $messages->count() === self::PER_PAGE;
        $this->isLoading = false;
    }

    protected function countPhotos(): int
    {
        $weddingEventId = auth()->user()?->weddingEvent?->id;

        if (! $weddingEventId) {
            return 0;
        }

        return $this->photoMessagesQuery($weddingEventId)
            ->get(['file_paths'])
            ->sum(fn (GuestMessage $message): int => count($message->file_paths ?? []));
    }

    protected function photoMessagesQuery(int $weddingEventId): Builder
    {
        return GuestMessage::query()
            ->where('wedding_event_id', $weddingEventId)
            ->where('type', GuestMessageType::Photo)
            ->whereNotNull('file_paths')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }
}
