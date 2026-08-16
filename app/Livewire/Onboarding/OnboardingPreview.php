<?php

namespace App\Livewire\Onboarding;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\LinkMode;
use App\Models\ScheduleItem;
use App\Models\WeddingLocation;
use App\PlanTier;
use App\Support\DraftWeddingEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.invitation')]
class OnboardingPreview extends Component
{
    public bool $isPreview = true;

    public bool $isTokenOnlyPreview = false;

    public bool $isPersonalLink = false;

    public bool $rsvpSubmitted = false;

    public bool $isEditing = false;

    public bool $invitationRevealed = false;

    public bool $missingDraft = false;

    public $guest = null;

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

    public function render()
    {
        if ($this->missingDraft) {
            return view('livewire.onboarding.onboarding-preview-missing')
                ->title(__('onboarding.preview_missing_title'))
                ->layout('layouts.onboarding');
        }

        $event = $this->buildEventFromDraft();
        $activeReveal = $event->reveal_animation;

        return view('livewire.onboarding.onboarding-preview', [
            'event' => $event,
            'activeTheme' => $event->theme,
            'activeTemplate' => $event->template,
            'activeReveal' => $activeReveal,
            'demoCreateUrl' => null,
            'showDemoCreateNudge' => false,
            'visibleMenuOptions' => collect(),
            'showRsvpNudge' => false,
            'guest' => null,
            'isPreview' => true,
            'isTokenOnlyPreview' => false,
            'isPersonalLink' => false,
        ])
            ->title($event->couple_names.' | '.__('invitation.title'))
            ->layout('layouts.invitation')
            ->layoutData([
                'event' => $event,
                'guest' => null,
                'isPreview' => true,
                'isTokenOnlyPreview' => false,
                'isPersonalLink' => false,
            ]);
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
