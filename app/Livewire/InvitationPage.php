<?php

namespace App\Livewire;

use App\Jobs\RecordLinkVisit;
use App\LinkType;
use App\Models\Guest;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.invitation')]
class InvitationPage extends Component
{
    public WeddingEvent $event;

    public ?Guest $guest = null;

    public string $anonymousName = '';

    public bool $rsvpSubmitted = false;

    public function mount(string $slug, ?string $token = null): void
    {
        app()->setLocale('bs');
        Carbon::setLocale('bs');

        $this->event = WeddingEvent::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['scheduleItems', 'eventPhotos'])
            ->firstOrFail();

        if ($this->event->requiresToken() && $token === null) {
            abort(403, __('invitation.token_required'));
        }

        if ($token !== null) {
            $this->guest = $this->event->guests()
                ->where('token', $token)
                ->firstOrFail();
        }

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
            ]);
    }
}
