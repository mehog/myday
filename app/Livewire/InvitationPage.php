<?php

namespace App\Livewire;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\Jobs\RecordLinkVisit;
use App\LinkType;
use App\Models\Guest;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use App\Support\Locale;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.invitation')]
class InvitationPage extends Component
{
    public WeddingEvent $event;

    public ?Guest $guest = null;

    public string $anonymousName = '';

    public string $plusOneName = '';

    public string $rsvpNote = '';

    public bool $rsvpSubmitted = false;

    public bool $isEditing = false;

    public bool $isPreview = false;

    public bool $isPersonalLink = false;

    public string $previewTheme = '';

    public string $previewTemplate = '';

    public string $previewReveal = '';

    public bool $showDemoSwitcher = true;

    public bool $invitationRevealed = false;

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

        if ($this->event->is_demo) {
            $this->previewTheme = $this->event->theme->value;
            $this->previewTemplate = $this->event->template->value;
            $this->previewReveal = $this->event->reveal_animation?->value ?? '';

            $stored = session("demo_preview.{$this->event->id}", []);
            if ($stored) {
                $this->previewTheme = $stored['theme'] ?? $this->previewTheme;
                $this->previewTemplate = $stored['template'] ?? $this->previewTemplate;
                $this->previewReveal = $stored['reveal'] ?? $this->previewReveal;
            }
        }

        if ($this->event->requiresToken() && $token === null) {
            abort(403, __('invitation.token_required'));
        }

        if ($token !== null) {
            $this->guest = $this->event->guests()
                ->where('token', $token)
                ->firstOrFail();

            $this->isPersonalLink = true;
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
            )->afterResponse();
        }
    }

    public function respond(string $status): void
    {
        $rsvpStatus = RsvpStatus::from($status);

        $this->validate([
            'rsvpNote' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->guest) {
            $updateData = [
                'rsvp_status' => $rsvpStatus,
                'rsvp_responded_at' => now(),
                'rsvp_manual_override' => false,
                'rsvp_note' => filled($this->rsvpNote) ? trim($this->rsvpNote) : null,
            ];

            if ($rsvpStatus === RsvpStatus::Yes && $this->guest->plus_one_allowed) {
                $updateData['plus_one_name'] = filled($this->plusOneName)
                    ? trim($this->plusOneName)
                    : null;
            } else {
                $updateData['plus_one_name'] = null;
            }

            $this->guest->update($updateData);
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
                'rsvp_note' => filled($this->rsvpNote) ? trim($this->rsvpNote) : null,
            ]);
        }

        $this->rsvpSubmitted = true;
        $this->isEditing = false;

        if ($rsvpStatus === RsvpStatus::Yes && $this->isPersonalLink && $this->guest?->token) {
            $this->dispatch('rsvp-accepted');
        }
    }

    public function editRsvp(): void
    {
        $this->isEditing = true;
        $this->plusOneName = $this->guest?->plus_one_name ?? '';
        $this->rsvpNote = $this->guest?->rsvp_note ?? '';
        $this->rsvpSubmitted = false;
    }

    public function switchLocale(string $locale): void
    {
        Locale::set($locale);
    }

    public function updatedPreviewTheme(): void
    {
        $this->savePreviewSession();
    }

    public function updatedPreviewTemplate(string $value): void
    {
        $this->savePreviewSession();

        if ($value === InvitationTemplate::Story->value) {
            $this->js('window.location.reload()');
        }
    }

    public function updatedPreviewReveal(): void
    {
        $this->savePreviewSession();
        $this->js('window.location.reload()');
    }

    protected function savePreviewSession(): void
    {
        if (! $this->event->is_demo) {
            return;
        }

        session()->put("demo_preview.{$this->event->id}", [
            'theme' => $this->previewTheme,
            'template' => $this->previewTemplate,
            'reveal' => $this->previewReveal,
        ]);
    }

    public function render()
    {
        $activeTheme = $this->event->is_demo && $this->previewTheme !== ''
            ? InvitationTheme::from($this->previewTheme)
            : $this->event->theme;

        $activeTemplate = $this->event->is_demo && $this->previewTemplate !== ''
            ? InvitationTemplate::from($this->previewTemplate)
            : $this->event->template;

        $activeReveal = $this->event->is_demo
            ? ($this->previewReveal !== '' ? InvitationReveal::from($this->previewReveal) : null)
            : $this->event->reveal_animation;

        return view('livewire.invitation-page', [
            'activeTheme' => $activeTheme,
            'activeTemplate' => $activeTemplate,
            'activeReveal' => $activeReveal,
            'themes' => InvitationTheme::cases(),
            'templates' => InvitationTemplate::cases(),
            'reveals' => InvitationReveal::cases(),
        ])
            ->title($this->event->couple_names.' | '.__('invitation.title'))
            ->layoutData([
                'event' => $this->event,
                'guest' => $this->guest,
                'isPreview' => $this->isPreview,
                'isPersonalLink' => $this->isPersonalLink,
            ]);
    }
}
