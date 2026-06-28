<?php

namespace App\Livewire;

use App\Models\Guest;
use App\Models\PushNotificationLog;
use App\Models\WeddingEvent;
use App\PushNotificationStatus;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.invitation')]
class GuestPushNotificationsPage extends Component
{
    public WeddingEvent $event;

    public Guest $guest;

    public function mount(string $slug, string $token): void
    {
        $this->event = WeddingEvent::query()
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $this->event->canBeViewedBy(auth()->user())) {
            abort(404);
        }

        if (! $this->event->is_active) {
            abort(403);
        }

        $this->guest = $this->event->guests()
            ->where('token', $token)
            ->firstOrFail();
    }

    /** @return Collection<int, PushNotificationLog> */
    public function getNotificationsProperty(): Collection
    {
        return $this->event->pushNotificationLogs()
            ->where('status', PushNotificationStatus::Sent)
            ->latest('sent_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.guest-push-notifications-page', [
            'notifications' => $this->notifications,
        ])
            ->title(__('invitation.push_notifications_page_title').' | '.$this->event->couple_names)
            ->layoutData([
                'event' => $this->event,
                'guest' => $this->guest,
                'isPersonalLink' => true,
            ]);
    }
}
