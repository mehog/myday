<?php

namespace App\Livewire;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\Jobs\RecordLinkVisit;
use App\Jobs\SendCoupleRsvpNotificationJob;
use App\LinkType;
use App\Models\Guest;
use App\Models\GuestChild;
use App\Models\WeddingEvent;
use App\Models\WeddingMenuOption;
use App\RsvpStatus;
use App\Services\SyncGuestChildren;
use App\Support\DemoInvitationUrl;
use App\Support\Locale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.invitation')]
class InvitationPage extends Component
{
    public WeddingEvent $event;

    public ?Guest $guest = null;

    public string $anonymousName = '';

    public string $plusOneName = '';

    /** @var list<string> */
    public array $childNames = [];

    /** @var list<string|int|null> */
    public array $childMenuOptionIds = [];

    public string $rsvpNote = '';

    public ?string $menuOptionId = null;

    public ?string $plusOneMenuOptionId = null;

    public bool $needsAccommodation = false;

    public ?int $accommodationCount = null;

    public bool $rsvpSubmitted = false;

    public bool $isEditing = false;

    public bool $isPreview = false;

    public bool $isTokenOnlyPreview = false;

    public bool $isPersonalLink = false;

    public string $previewTheme = '';

    public string $previewTemplate = '';

    public string $previewReveal = '';

    public bool $invitationRevealed = false;

    public function mount(string $slug, ?string $token = null): void
    {
        $this->event = WeddingEvent::query()
            ->where('slug', $slug)
            ->with(['scheduleItems', 'eventPhotos', 'menuOptions', 'locations'])
            ->firstOrFail();

        if (! $this->event->canBeViewedBy(auth()->user())) {
            abort(404);
        }

        $this->isPreview = ! $this->event->is_active;

        if ($this->event->is_demo) {
            $this->previewTheme = $this->event->theme->value;
            $this->previewTemplate = $this->event->template->value;
            $this->previewReveal = $this->normalizeRevealValue($this->event->reveal_animation?->value ?? '');

            $stored = session("demo_preview.{$this->event->id}", []);
            if ($stored) {
                $this->previewTheme = $stored['theme'] ?? $this->previewTheme;
                $this->previewTemplate = $stored['template'] ?? $this->previewTemplate;
                $this->previewReveal = $this->normalizeRevealValue($stored['reveal'] ?? $this->previewReveal);
            }

            $this->applyDemoQueryParams();
            $this->savePreviewSession();
        }

        if ($this->event->requiresToken() && $token === null) {
            if (! $this->event->canPreviewPublicLink(auth()->user())) {
                abort(403, __('invitation.token_required'));
            }

            $this->isTokenOnlyPreview = true;
        }

        if ($token !== null) {
            $this->guest = $this->event->guests()
                ->with(['children'])
                ->where('token', $token)
                ->firstOrFail();

            $this->isPersonalLink = true;
        }

        if (! $this->isPreview && ! $this->isTokenOnlyPreview) {
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
        if ($this->isTokenOnlyPreview || ($this->event->requiresToken() && ! $this->isPersonalLink)) {
            return;
        }

        if (! $this->event->acceptsRsvps()) {
            return;
        }

        if (! $this->guest && $this->event->hasEnded()) {
            return;
        }

        $rsvpStatus = RsvpStatus::from($status);

        $this->validate([
            'rsvpNote' => ['nullable', 'string', 'max:500'],
            'childNames' => ['nullable', 'array', 'max:'.GuestChild::MAX_PER_GUEST],
            'childNames.*' => ['nullable', 'string', 'max:255'],
        ]);

        if ($rsvpStatus === RsvpStatus::Yes) {
            $this->validateYesPreferences();
        }

        if (! $this->guest) {
            $this->validate([
                'anonymousName' => ['required', 'string', 'max:255'],
            ], [
                'anonymousName.required' => __('invitation.name_required'),
            ]);

            if (! $this->event->canAddGuests()) {
                $this->addError('anonymousName', __('pricing.guest_limit_rsvp'));

                return;
            }
        }

        DB::transaction(function () use ($rsvpStatus): void {
            if ($this->guest) {
                $this->persistExistingGuestResponse($rsvpStatus);
            } else {
                $this->persistAnonymousGuestResponse($rsvpStatus);
            }
        });

        $this->rsvpSubmitted = true;
        $this->isEditing = false;

        if ($this->guest !== null) {
            SendCoupleRsvpNotificationJob::dispatch($this->guest->id)->afterResponse();
        }

        if ($rsvpStatus === RsvpStatus::Yes && $this->isPersonalLink && $this->guest?->token) {
            $this->dispatch('rsvp-accepted');
        }
    }

    public function editRsvp(): void
    {
        if (! $this->event->acceptsRsvps()) {
            return;
        }

        $this->isEditing = true;
        $this->plusOneName = $this->guest?->plus_one_name ?? '';
        $this->childNames = $this->guest
            ? $this->guest->children()->pluck('name')->all()
            : [];
        $this->childMenuOptionIds = $this->guest
            ? $this->guest->children()->pluck('menu_option_id')->map(fn ($id) => $id !== null ? (string) $id : null)->all()
            : [];
        $this->rsvpNote = $this->guest?->rsvp_note ?? '';
        $this->menuOptionId = $this->guest?->menu_option_id !== null
            ? (string) $this->guest->menu_option_id
            : null;
        $this->plusOneMenuOptionId = $this->guest?->plus_one_menu_option_id !== null
            ? (string) $this->guest->plus_one_menu_option_id
            : null;
        $this->needsAccommodation = ($this->guest?->accommodation_count ?? 0) > 0;
        $this->accommodationCount = $this->guest?->accommodation_count;
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

    public function switchLocale(string $locale): void
    {
        Locale::set($locale, persistToUser: false);
    }

    protected function applyDemoQueryParams(): void
    {
        $request = request();

        $theme = $request->query('theme');
        if (is_string($theme) && InvitationTheme::tryFrom($theme) !== null) {
            $this->previewTheme = $theme;
        }

        $template = $request->query('template');
        if (is_string($template) && InvitationTemplate::tryFrom($template) !== null) {
            $this->previewTemplate = $template;
        }

        if ($request->has('reveal')) {
            $reveal = $request->query('reveal');

            if ($reveal === null || $reveal === '' || $reveal === 'none') {
                $this->previewReveal = '';
            } elseif (is_string($reveal)) {
                $normalized = $this->normalizeRevealValue($reveal);

                if (InvitationReveal::tryFrom($normalized) !== null) {
                    $this->previewReveal = $normalized;
                }
            }
        }
    }

    protected function savePreviewSession(): void
    {
        if (! $this->event->is_demo) {
            return;
        }

        session()->put("demo_preview.{$this->event->id}", [
            'theme' => $this->previewTheme,
            'template' => $this->previewTemplate,
            'reveal' => $this->normalizeRevealValue($this->previewReveal),
        ]);
    }

    protected function normalizeRevealValue(string $value): string
    {
        return match ($value) {
            'polaroid' => 'storybook',
            'starlit-constellation' => 'sunrise-bloom',
            default => $value,
        };
    }

    protected function validateYesPreferences(): void
    {
        $visibleMenuIds = $this->visibleMenuOptionIds();
        $offersMenus = $visibleMenuIds !== [];
        $filledChildIndexes = $this->filledChildIndexes();
        $includesPlusOne = $this->guest?->plus_one_allowed && filled(trim($this->plusOneName));
        $partySize = 1 + ($includesPlusOne ? 1 : 0) + count($filledChildIndexes);

        $rules = [];

        if ($offersMenus) {
            $rules['menuOptionId'] = ['required', Rule::in($visibleMenuIds)];

            if ($includesPlusOne) {
                $rules['plusOneMenuOptionId'] = ['required', Rule::in($visibleMenuIds)];
            }

            foreach ($filledChildIndexes as $index) {
                $rules["childMenuOptionIds.{$index}"] = ['required', Rule::in($visibleMenuIds)];
            }
        }

        if ($this->event->accommodation_enabled) {
            $rules['needsAccommodation'] = ['boolean'];

            if ($this->needsAccommodation) {
                $rules['accommodationCount'] = [
                    'required',
                    'integer',
                    'min:1',
                    'max:'.$partySize,
                ];
            }
        }

        if ($rules !== []) {
            $this->validate($rules, [
                'menuOptionId.required' => __('invitation.menu_required'),
                'plusOneMenuOptionId.required' => __('invitation.menu_required'),
                'childMenuOptionIds.*.required' => __('invitation.menu_required'),
                'accommodationCount.required' => __('invitation.accommodation_count_required'),
                'accommodationCount.max' => __('invitation.accommodation_count_max'),
            ]);
        }
    }

    protected function persistExistingGuestResponse(RsvpStatus $rsvpStatus): void
    {
        $updateData = [
            'rsvp_status' => $rsvpStatus,
            'rsvp_responded_at' => now(),
            'rsvp_manual_override' => false,
            'rsvp_note' => filled($this->rsvpNote) ? trim($this->rsvpNote) : null,
        ];

        if ($rsvpStatus === RsvpStatus::Yes) {
            $includesPlusOne = $this->guest->plus_one_allowed && filled(trim($this->plusOneName));

            $updateData['plus_one_name'] = $includesPlusOne ? trim($this->plusOneName) : null;
            $updateData['menu_option_id'] = $this->normalizedMenuOptionId($this->menuOptionId);
            $updateData['plus_one_menu_option_id'] = $includesPlusOne
                ? $this->normalizedMenuOptionId($this->plusOneMenuOptionId)
                : null;
            $updateData['accommodation_count'] = $this->normalizedAccommodationCount();
        } else {
            $updateData['plus_one_name'] = null;
            $updateData['menu_option_id'] = null;
            $updateData['plus_one_menu_option_id'] = null;
            $updateData['accommodation_count'] = null;
        }

        $this->guest->update($updateData);
        $this->guest->refresh();

        $menuOptionIds = [];

        if ($rsvpStatus === RsvpStatus::Yes) {
            foreach ($this->filledChildIndexes() as $index) {
                $menuOptionIds[] = $this->normalizedMenuOptionId($this->childMenuOptionIds[$index] ?? null);
            }
        }

        app(SyncGuestChildren::class)->syncFromNames(
            $this->guest,
            $rsvpStatus === RsvpStatus::Yes ? $this->childNames : [],
            $rsvpStatus === RsvpStatus::Yes ? $menuOptionIds : [],
        );

        $this->guest->load('children');
    }

    protected function persistAnonymousGuestResponse(RsvpStatus $rsvpStatus): void
    {
        $this->guest = $this->event->guests()->create([
            'name' => $this->anonymousName,
            'rsvp_status' => $rsvpStatus,
            'rsvp_responded_at' => now(),
            'rsvp_note' => filled($this->rsvpNote) ? trim($this->rsvpNote) : null,
            'menu_option_id' => $rsvpStatus === RsvpStatus::Yes
                ? $this->normalizedMenuOptionId($this->menuOptionId)
                : null,
            'accommodation_count' => $rsvpStatus === RsvpStatus::Yes
                ? $this->normalizedAccommodationCount()
                : null,
        ]);
    }

    /**
     * @return list<int>
     */
    protected function filledChildIndexes(): array
    {
        $indexes = [];

        foreach ($this->childNames as $index => $name) {
            if (trim((string) $name) !== '') {
                $indexes[] = $index;
            }
        }

        return $indexes;
    }

    /**
     * @return list<string>
     */
    protected function visibleMenuOptionIds(): array
    {
        return $this->event->menuOptions
            ->filter(fn (WeddingMenuOption $option): bool => $option->is_visible)
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->values()
            ->all();
    }

    protected function normalizedMenuOptionId(string|int|null $value): ?int
    {
        if (! filled($value)) {
            return null;
        }

        $id = (int) $value;
        $visibleIds = array_map('intval', $this->visibleMenuOptionIds());

        return in_array($id, $visibleIds, true) ? $id : null;
    }

    protected function normalizedAccommodationCount(): ?int
    {
        if (! $this->event->accommodation_enabled || ! $this->needsAccommodation) {
            return null;
        }

        $count = (int) ($this->accommodationCount ?? 0);

        return $count > 0 ? $count : null;
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
            ? ($this->previewReveal !== '' ? InvitationReveal::from($this->normalizeRevealValue($this->previewReveal)) : null)
            : $this->event->reveal_animation;

        $demoCreateUrl = $this->event->is_demo
            ? DemoInvitationUrl::onboarding(
                $activeTheme->value,
                $activeTemplate->value,
                $activeReveal?->value ?? '',
            )
            : null;

        $showDemoCreateNudge = $this->event->is_demo && filled($demoCreateUrl);

        return view('livewire.invitation-page', [
            'activeTheme' => $activeTheme,
            'activeTemplate' => $activeTemplate,
            'activeReveal' => $activeReveal,
            'demoCreateUrl' => $demoCreateUrl,
            'showDemoCreateNudge' => $showDemoCreateNudge,
            'visibleMenuOptions' => $this->event->menuOptions
                ->filter(fn (WeddingMenuOption $option): bool => $option->is_visible)
                ->values(),
            'showRsvpNudge' => ! $this->event->is_demo
                && $this->isPersonalLink
                && $this->guest
                && ! $this->guest->hasResponded()
                && ! $this->rsvpSubmitted,
        ])
            ->title($this->event->couple_names.' | '.__('invitation.title'))
            ->layoutData([
                'event' => $this->event,
                'guest' => $this->guest,
                'isPreview' => $this->isPreview,
                'isTokenOnlyPreview' => $this->isTokenOnlyPreview,
                'isPersonalLink' => $this->isPersonalLink,
            ]);
    }
}
