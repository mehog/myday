<?php

namespace App\Livewire;

use App\Jobs\RecordLinkVisit;
use App\LinkType;
use App\Models\Guest;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.invitation')]
class InvitationPage extends Component
{
    public WeddingEvent $event;

    public ?Guest $guest = null;

    public string $anonymousName = '';

    public bool $rsvpSubmitted = false;

    public bool $isPreview = false;

    public function mount(string $slug, ?string $token = null): void
    {
        $this->event = WeddingEvent::query()
            ->where('slug', $slug)
            ->with(['scheduleItems', 'eventPhotos'])
            ->firstOrFail();

        if (! $this->event->canBeViewedBy(auth()->user())) {
            abort(404);
        }

        $this->isPreview = ! $this->event->is_active;

        if ($this->event->requiresToken() && $token === null) {
            abort(403, __('invitation.token_required'));
        }

        if ($token !== null) {
            $this->guest = $this->event->guests()
                ->where('token', $token)
                ->firstOrFail();
        }

        if (! $this->isPreview) {
            $request = request();

            RecordLinkVisit::dispatch(
                weddingEventId: $this->event->id,
                guestId: $this->guest?->id,
                linkType: $this->guest ? LinkType::Personal : LinkType::Public,
                ip: $request->ip(),
                userAgent: $request->userAgent(),
                referer: $request->header('referer'),
            );
        }
    }

    public function respond(string $status): void
    {
        $rsvpStatus = RsvpStatus::from($status);

        if ($this->guest) {
            $this->guest->update([
                'rsvp_status' => $rsvpStatus,
                'rsvp_responded_at' => now(),
            ]);
            $this->guest->refresh();
        } else {
            $this->validate([
                'anonymousName' => ['required', 'string', 'max:255'],
            ], [
                'anonymousName.required' => __('invitation.name_required'),
            ]);

            $this->guest = $this->event->guests()->create([
                'name' => $this->anonymousName,
                'rsvp_status' => $rsvpStatus,
                'rsvp_responded_at' => now(),
            ]);
        }

        $this->rsvpSubmitted = true;
    }

    public function render()
    {
        return view('livewire.invitation-page')
            ->title($this->event->couple_names.' | '.__('invitation.title'))
            ->layoutData([
                'event' => $this->event,
                'guest' => $this->guest,
                'isPreview' => $this->isPreview,
            ]);
    }
}
