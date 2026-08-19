<?php

namespace App\Livewire\Onboarding;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\LinkMode;
use App\Models\Guest;
use App\Models\GuestChild;
use App\Models\ScheduleItem;
use App\Models\WeddingLocation;
use App\PlanTier;
use App\RsvpStatus;
use App\Support\DraftWeddingEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.invitation')]
class OnboardingPreview extends Component
{
    public bool $isPreview = true;

    public bool $isTokenOnlyPreview = true;

    public bool $isPersonalLink = false;

    public bool $rsvpSubmitted = false;

    public bool $isEditing = false;

    public bool $invitationRevealed = false;

    public bool $missingDraft = false;

    public string $plusOneName = '';

    /** @var list<string> */
    public array $childNames = [];

    /** @var list<string|int|null> */
    public array $childMenuOptionIds = [];

    public string $rsvpNote = '';

    public bool $needsAccommodation = false;

    public ?int $accommodationCount = null;

    public ?string $mockRsvpStatus = null;

    public function mount(): void
    {
        $draft = session(config('onboarding.draft_session_key'));

        if (! is_array($draft) || empty($draft['groom_name']) || empty($draft['bride_name']) || empty($draft['wedding_date'])) {
            $this->missingDraft = true;

            return;
        }

        $reveal = InvitationReveal::tryFrom((string) ($draft['reveal_animation'] ?? ''));
        $this->invitationRevealed = $reveal === null;
    }

    public function respond(string $status): void
    {
        $rsvpStatus = RsvpStatus::tryFrom($status);

        if ($rsvpStatus === null || $this->mockGuestFromDraft() === null) {
            return;
        }

        $this->mockRsvpStatus = $rsvpStatus->value;
        $this->rsvpSubmitted = true;
        $this->isEditing = false;
    }

    public function editRsvp(): void
    {
        $this->isEditing = true;
        $this->rsvpSubmitted = false;
    }

    public function addChildName(): void
    {
        if (count($this->childNames) >= GuestChild::MAX_PER_GUEST) {
            return;
        }

        $this->childNames[] = '';
        $this->childMenuOptionIds[] = null;
    }

    public function removeChildName(int $index): void
    {
        if (! array_key_exists($index, $this->childNames)) {
            return;
        }

        unset($this->childNames[$index], $this->childMenuOptionIds[$index]);
        $this->childNames = array_values($this->childNames);
        $this->childMenuOptionIds = array_values($this->childMenuOptionIds);
    }

    public function render()
    {
        if ($this->missingDraft) {
            return view('livewire.onboarding.onboarding-preview-missing')
                ->title(__('onboarding.preview_missing_title'))
                ->layout('layouts.onboarding');
        }

        $event = $this->buildEventFromDraft();
        $guest = $this->mockGuestFromDraft();
        $this->isTokenOnlyPreview = $guest === null;
        $this->isPersonalLink = $guest !== null;
        $showRsvpNudge = $guest !== null && ! $guest->hasResponded() && ! $this->rsvpSubmitted;
        $activeReveal = $event->reveal_animation;

        return view('livewire.onboarding.onboarding-preview', [
            'event' => $event,
            'activeTheme' => $event->theme,
            'activeTemplate' => $event->template,
            'activeReveal' => $activeReveal,
            'demoCreateUrl' => null,
            'showDemoCreateNudge' => false,
            'visibleMenuOptions' => collect(),
            'showRsvpNudge' => $showRsvpNudge,
            'guest' => $guest,
            'isPreview' => true,
            'isTokenOnlyPreview' => $this->isTokenOnlyPreview,
            'isPersonalLink' => $this->isPersonalLink,
        ])
            ->title($event->couple_names.' | '.__('invitation.title'))
            ->layout('layouts.invitation')
            ->layoutData([
                'event' => $event,
                'guest' => $guest,
                'isPreview' => true,
                'isTokenOnlyPreview' => $this->isTokenOnlyPreview,
                'isPersonalLink' => $this->isPersonalLink,
            ]);
    }

    private function mockGuestFromDraft(): ?Guest
    {
        $draft = session(config('onboarding.draft_session_key'));

        if (! is_array($draft) || ! is_array($draft['guests'] ?? null)) {
            return null;
        }

        $first = collect($draft['guests'])
            ->first(fn (mixed $guest): bool => is_array($guest) && filled($guest['name'] ?? null));

        if (! is_array($first)) {
            return null;
        }

        $guest = new Guest;
        $guest->forceFill([
            'name' => (string) $first['name'],
            'email' => filled($first['email'] ?? null) ? (string) $first['email'] : null,
            'plus_one_allowed' => (bool) ($first['plus_one_allowed'] ?? false),
            'rsvp_status' => $this->mockRsvpStatus !== null
                ? RsvpStatus::from($this->mockRsvpStatus)
                : null,
        ]);
        $guest->exists = false;
        $guest->id = null;
        $guest->setRelation('children', collect());

        return $guest;
    }

    private function buildEventFromDraft(): DraftWeddingEvent
    {
        $draft = session(config('onboarding.draft_session_key'));

        if (! is_array($draft)) {
            $this->missingDraft = true;

            return new DraftWeddingEvent;
        }

        $theme = InvitationTheme::tryFrom((string) ($draft['theme'] ?? '')) ?? InvitationTheme::AmberGold;
        $template = InvitationTemplate::tryFrom((string) ($draft['template'] ?? '')) ?? InvitationTemplate::Classic;
        $reveal = InvitationReveal::tryFrom((string) ($draft['reveal_animation'] ?? ''));

        $event = new DraftWeddingEvent;
        $event->draftHeroUrl = is_string($draft['hero_temp_url'] ?? null) ? $draft['hero_temp_url'] : null;

        $event->forceFill([
            'groom_name' => (string) $draft['groom_name'],
            'bride_name' => (string) $draft['bride_name'],
            'slug' => 'onboarding-preview',
            'wedding_date' => Carbon::parse((string) $draft['wedding_date'])->startOfDay(),
            'theme' => $theme,
            'template' => $template,
            'reveal_animation' => $reveal,
            'link_mode' => LinkMode::TokenOnly,
            'plan_tier' => PlanTier::Free,
            'guest_limit' => PlanTier::Free->guestLimit(),
            'is_active' => false,
            'is_demo' => false,
            'music_url' => filled($draft['music_url'] ?? null) ? (string) $draft['music_url'] : null,
            'motto' => filled($draft['motto'] ?? null) ? (string) $draft['motto'] : null,
            'location_name' => filled($draft['location_name'] ?? null) ? (string) $draft['location_name'] : null,
            'location_address' => filled($draft['location_address'] ?? null) ? (string) $draft['location_address'] : null,
            'invitation_locale' => (string) ($draft['invitation_locale'] ?? app()->getLocale()),
            'hero_image' => null,
        ]);

        $event->exists = false;
        $event->id = 0;

        $scheduleItems = Collection::make($draft['schedule_items'] ?? [])
            ->values()
            ->map(function (array $item, int $index): ScheduleItem {
                $schedule = new ScheduleItem;
                $schedule->forceFill([
                    'time' => $item['time'],
                    'title' => $item['title'],
                    'description' => $item['description'] !== '' ? $item['description'] : null,
                    'sort_order' => $index,
                ]);

                return $schedule;
            });

        $event->setRelation('scheduleItems', $scheduleItems);
        $event->setRelation('eventPhotos', collect());
        $event->setRelation('menuOptions', collect());

        $locations = collect();
        if (filled($draft['location_name'] ?? null) || filled($draft['location_address'] ?? null)) {
            $location = new WeddingLocation;
            $location->forceFill([
                'label' => __('onboarding.location_primary_label'),
                'name' => filled($draft['location_name'] ?? null) ? (string) $draft['location_name'] : null,
                'address' => filled($draft['location_address'] ?? null) ? (string) $draft['location_address'] : null,
                'is_primary' => true,
                'sort_order' => 0,
            ]);
            $locations->push($location);
        }

        $event->setRelation('locations', $locations);

        return $event;
    }
}
