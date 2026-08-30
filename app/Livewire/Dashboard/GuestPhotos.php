<?php

namespace App\Livewire\Dashboard;

use App\GuestMessageType;
use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\WeddingEvent;
use App\Services\GuestMessageMediaGallery;
use Livewire\Component;

class GuestPhotos extends Component
{
    use RendersDashboard;

    /**
     * @var list<array{key: string, message_id: int, index: int, url: string, name: string, sender_name: string}>
     */
    public array $photos = [];

    public int $page = 1;

    public bool $hasMore = true;

    public bool $isLoading = false;

    public int $totalPhotoCount = 0;

    public function mount(GuestMessageMediaGallery $gallery): void
    {
        abort_unless(auth()->user()?->weddingEvent instanceof WeddingEvent, 403);

        $wedding = auth()->user()->weddingEvent;
        $this->totalPhotoCount = $gallery->countPhotos($wedding);
        $this->loadMore($gallery);
    }

    public function loadMore(GuestMessageMediaGallery $gallery): void
    {
        if ($this->isLoading || ! $this->hasMore) {
            return;
        }

        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding instanceof WeddingEvent) {
            $this->hasMore = false;

            return;
        }

        $this->isLoading = true;

        $messages = $gallery->messagesQuery($wedding, GuestMessageType::Photo)
            ->forPage($this->page, GuestMessageMediaGallery::PER_PAGE)
            ->get();

        if ($messages->isEmpty()) {
            $this->hasMore = false;
            $this->isLoading = false;

            return;
        }

        array_push($this->photos, ...$gallery->flattenMessages($messages));

        $this->page++;
        $this->hasMore = $messages->count() === GuestMessageMediaGallery::PER_PAGE;
        $this->isLoading = false;
    }

    public function render()
    {
        return $this->dashboardView('livewire.dashboard.guest-photos', [], __('app.guest_messages_all_photos_title'), [
            ['label' => __('dashboard.nav.messages'), 'url' => route('dashboard.messages')],
            ['label' => __('app.guest_messages_all_photos_title'), 'url' => null],
        ], backUrl: route('dashboard.messages'));
    }
}
