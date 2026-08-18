<?php

namespace App\Livewire\Onboarding;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\LinkMode;
use App\Models\Guest;
use App\Models\Referral;
use App\Models\ScheduleItem;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Models\WeddingLocation;
use App\PlanTier;
use App\Support\DashboardNav;
use App\Support\Locale;
use App\Support\MediaDisk;
use App\Support\MetaPixel;
use App\Support\OnboardingSongs;
use App\Support\OnboardingSteps;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Throwable;

#[Layout('layouts.onboarding')]
class WeddingOnboarding extends Component
{
    use WithFileUploads;

    #[Url(as: 'step', history: true, keep: true)]
    public string $step = 'names';

    public string $groom_name = '';

    public string $bride_name = '';

    public string $wedding_date = '';

    public string $theme = '';

    public string $template = '';

    public string $reveal_animation = '';

    public string $location_name = '';

    public string $location_address = '';

    public string $motto = '';

    /** @var TemporaryUploadedFile|string|null */
    public $hero_image = null;

    public string $music_url = '';

    public string $song_query = '';

    /** @var list<string> */
    public array $songSuggestions = [];

    /** @var array<int, array{time: string, title: string, description: string}> */
    public array $scheduleItems = [];

    /** @var array<int, array{name: string, email: string, plus_one_allowed: bool}> */
    public array $guests = [];

    public string $your_name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $previewError = null;

    public ?string $submitError = null;

    /** @var array{title: string, thumbnail_url: string}|null */
    public ?array $songPreview = null;

    public function mount(): void
    {
        $user = Auth::user();

        if ($user !== null) {
            if (! $user->hasVerifiedEmail()) {
                $this->redirectRoute('verification.notice');

                return;
            }

            $this->redirect(DashboardNav::homeUrl());

            return;
        }

        $this->hydrateFromProgress();
        $this->applyStyleQueryParams();

        if (! OnboardingSteps::isValid($this->step)) {
            $this->step = 'names';
        }

        if (! OnboardingSteps::canAccess($this->step, $this->accessData())) {
            $this->step = OnboardingSteps::firstIncompleteStep($this->accessData());
        }

        if ($this->scheduleItems === []) {
            $this->scheduleItems = [
                ['time' => '', 'title' => '', 'description' => ''],
            ];
        }

        if ($this->guests === []) {
            $this->guests = [
                ['name' => '', 'email' => '', 'plus_one_allowed' => false],
            ];
        }

        $this->songSuggestions = [];
        $this->ensureSongSuggestions();
        $this->persistProgress();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['password', 'password_confirmation', 'hero_image', 'previewError', 'submitError', 'songPreview', 'songSuggestions'], true)) {
            return;
        }

        if (str_starts_with($property, 'hero_image')) {
            return;
        }

        $this->persistProgress();
    }

    protected function applyStyleQueryParams(): void
    {
        $request = request();

        $theme = $request->query('theme');
        if (is_string($theme) && InvitationTheme::tryFrom($theme) !== null) {
            $this->theme = $theme;
        }

        $template = $request->query('template');
        if (is_string($template) && InvitationTemplate::tryFrom($template) !== null) {
            $this->template = $template;
        }

        if ($request->has('reveal')) {
            $reveal = $request->query('reveal');

            if ($reveal === null || $reveal === '' || $reveal === 'none') {
                $this->reveal_animation = '';
            } elseif (is_string($reveal) && InvitationReveal::tryFrom($reveal) !== null) {
                $this->reveal_animation = $reveal;
            }
        }
    }

    public function switchLocale(string $locale): void
    {
        Locale::set($locale);
        $this->songSuggestions = [];
    }

    public function nextStep(): void
    {
        $this->previewError = null;
        $this->submitError = null;

        if (! OnboardingSteps::isTip($this->step)) {
            $this->validate($this->rulesForStep($this->step), $this->messagesForStep($this->step));
        }

        $next = OnboardingSteps::next($this->step);

        if ($next === null) {
            return;
        }

        $this->step = $next;
        $this->persistProgress();

        if ($this->step === 'review') {
            $this->storeDraft();
        }
    }

    public function skipStep(): void
    {
        if (! OnboardingSteps::isOptional($this->step)) {
            return;
        }

        $this->clearOptionalStepData($this->step);

        $next = OnboardingSteps::next($this->step);

        if ($next !== null) {
            $this->step = $next;
        }

        $this->persistProgress();
    }

    public function previousStep(): void
    {
        $previous = OnboardingSteps::previous($this->step);

        if ($previous !== null) {
            $this->step = $previous;
            $this->previewError = null;
            $this->persistProgress();
        }
    }

    public function selectTheme(string $theme): void
    {
        if (InvitationTheme::tryFrom($theme) === null) {
            return;
        }

        $this->theme = $theme;
        $this->step = OnboardingSteps::next('theme') ?? $this->step;
        $this->persistProgress();
    }

    public function selectTemplate(string $template): void
    {
        if (InvitationTemplate::tryFrom($template) === null) {
            return;
        }

        $this->template = $template;
        $this->step = OnboardingSteps::next('template') ?? $this->step;
        $this->persistProgress();
    }

    public function selectReveal(string $reveal): void
    {
        if ($reveal === '' || $reveal === 'none') {
            $this->reveal_animation = '';
        } elseif (InvitationReveal::tryFrom($reveal) !== null) {
            $this->reveal_animation = $reveal;
        } else {
            return;
        }

        $this->step = OnboardingSteps::next('reveal') ?? $this->step;
        $this->persistProgress();
    }

    public function selectSong(string $url): void
    {
        $this->music_url = $url;
        $this->song_query = '';
        $this->songPreview = null;
        $this->step = OnboardingSteps::next('song') ?? $this->step;
        $this->persistProgress();
    }

    public function selectMotto(string $motto): void
    {
        $this->motto = $motto;
        $this->persistProgress();
    }

    public function updatedMusicUrl(): void
    {
        $this->songPreview = null;

        if ($this->looksLikeYoutubeUrl($this->music_url)) {
            $this->fetchSongPreview($this->music_url);
        }
    }

    public function applySongQuery(): void
    {
        $query = trim($this->song_query);

        if ($query === '') {
            return;
        }

        if ($this->looksLikeYoutubeUrl($query)) {
            $this->music_url = $query;
            $this->fetchSongPreview($query);

            return;
        }
    }

    public function addScheduleItem(): void
    {
        $this->scheduleItems[] = ['time' => '', 'title' => '', 'description' => ''];
        $this->persistProgress();
    }

    public function removeScheduleItem(int $index): void
    {
        unset($this->scheduleItems[$index]);
        $this->scheduleItems = array_values($this->scheduleItems);

        if ($this->scheduleItems === []) {
            $this->scheduleItems = [
                ['time' => '', 'title' => '', 'description' => ''],
            ];
        }

        $this->persistProgress();
    }

    public function applySchedulePreset(string $time, string $title): void
    {
        foreach ($this->scheduleItems as $item) {
            if (($item['time'] ?? '') === $time && ($item['title'] ?? '') === $title) {
                return;
            }
        }

        $emptyIndex = null;
        foreach ($this->scheduleItems as $index => $item) {
            if (trim((string) ($item['time'] ?? '')) === '' && trim((string) ($item['title'] ?? '')) === '') {
                $emptyIndex = $index;
                break;
            }
        }

        if ($emptyIndex !== null) {
            $this->scheduleItems[$emptyIndex]['time'] = $time;
            $this->scheduleItems[$emptyIndex]['title'] = $title;
            $this->persistProgress();

            return;
        }

        $this->scheduleItems[] = ['time' => $time, 'title' => $title, 'description' => ''];
        $this->persistProgress();
    }

    public function addGuest(): void
    {
        $this->guests[] = ['name' => '', 'email' => '', 'plus_one_allowed' => false];
        $this->persistProgress();
    }

    public function removeGuest(int $index): void
    {
        unset($this->guests[$index]);
        $this->guests = array_values($this->guests);

        if ($this->guests === []) {
            $this->guests = [
                ['name' => '', 'email' => '', 'plus_one_allowed' => false],
            ];
        }

        $this->persistProgress();
    }

    public function openPreview(): void
    {
        $this->previewError = null;

        if (! filled($this->groom_name) || ! filled($this->bride_name) || ! filled($this->wedding_date) || ! filled($this->theme) || ! filled($this->template)) {
            $this->previewError = __('onboarding.preview_incomplete');

            return;
        }

        $this->storeDraft();
        $this->dispatch(
            'invitation-preview-open',
            url: route('onboarding.preview'),
            title: __('onboarding.preview_modal_title'),
        );
    }

    public function submit(): void
    {
        $this->submitError = null;
        $this->previewError = null;

        try {
            $this->validate(array_merge(
                $this->rulesForStep('names'),
                $this->rulesForStep('date'),
                $this->rulesForStep('theme'),
                $this->rulesForStep('template'),
                $this->rulesForStep('account'),
                $this->rulesForStep('song'),
                $this->rulesForStep('cover'),
                $this->rulesForStep('motto'),
                $this->rulesForStep('location'),
                $this->rulesForStep('schedule'),
                $this->rulesForStep('guests'),
            ), array_merge(
                $this->messagesForStep('names'),
                $this->messagesForStep('date'),
                $this->messagesForStep('theme'),
                $this->messagesForStep('template'),
                $this->messagesForStep('account'),
                $this->messagesForStep('song'),
                $this->messagesForStep('cover'),
                $this->messagesForStep('motto'),
                $this->messagesForStep('location'),
                $this->messagesForStep('schedule'),
                $this->messagesForStep('guests'),
            ));
        } catch (ValidationException $e) {
            $fields = array_keys($e->errors());
            $firstField = $fields[0] ?? 'groom_name';
            $this->step = OnboardingSteps::stepForField($firstField);
            $this->submitError = __('onboarding.submit_fix_errors');
            $this->persistProgress();

            throw $e;
        }

        try {
            $heroPath = $this->storeHeroImage();

            $user = DB::transaction(function () use ($heroPath) {
                $user = User::query()->create([
                    'name' => $this->your_name,
                    'email' => $this->email,
                    'password' => $this->password,
                    'is_admin' => false,
                ]);

                $wedding = WeddingEvent::query()->create([
                    'user_id' => $user->id,
                    'groom_name' => $this->groom_name,
                    'bride_name' => $this->bride_name,
                    'slug' => $this->uniqueSlug(),
                    'wedding_date' => Carbon::parse($this->wedding_date)->startOfDay(),
                    'theme' => InvitationTheme::from($this->theme),
                    'template' => InvitationTemplate::from($this->template),
                    'reveal_animation' => $this->reveal_animation !== ''
                        ? InvitationReveal::from($this->reveal_animation)
                        : null,
                    'link_mode' => LinkMode::TokenOnly,
                    'plan_tier' => PlanTier::Free,
                    'guest_limit' => PlanTier::Free->guestLimit(),
                    'is_active' => true,
                    'music_url' => $this->music_url !== '' ? $this->music_url : null,
                    'motto' => $this->motto !== '' ? $this->motto : null,
                    'hero_image' => $heroPath,
                    'location_name' => $this->location_name !== '' ? $this->location_name : null,
                    'location_address' => $this->location_address !== '' ? $this->location_address : null,
                ]);

                if ($this->location_name !== '' || $this->location_address !== '') {
                    WeddingLocation::query()->create([
                        'wedding_event_id' => $wedding->id,
                        'label' => __('onboarding.location_primary_label'),
                        'name' => $this->location_name !== '' ? $this->location_name : null,
                        'address' => $this->location_address !== '' ? $this->location_address : null,
                        'is_primary' => true,
                        'sort_order' => 0,
                    ]);
                }

                $sortOrder = 0;
                foreach ($this->normalizedScheduleItems() as $item) {
                    ScheduleItem::query()->create([
                        'wedding_event_id' => $wedding->id,
                        'time' => $item['time'],
                        'title' => $item['title'],
                        'description' => $item['description'] !== '' ? $item['description'] : null,
                        'sort_order' => $sortOrder++,
                    ]);
                }

                $guestLimit = PlanTier::Free->guestLimit() ?? 50;
                foreach (array_slice($this->normalizedGuests(), 0, $guestLimit) as $guest) {
                    Guest::query()->create([
                        'wedding_event_id' => $wedding->id,
                        'name' => $guest['name'],
                        'email' => $guest['email'] !== '' ? $guest['email'] : null,
                        'plus_one_allowed' => $guest['plus_one_allowed'],
                    ]);
                }

                $referrerId = null;
                $referralCode = Cookie::get(config('referral.cookie_name'));

                if (is_string($referralCode) && $referralCode !== '') {
                    $referrer = Referral::userByReferralCode($referralCode);

                    if ($referrer !== null && $referrer->id !== $user->id) {
                        $referrerId = $referrer->id;
                    }
                }

                $user->createReferralAccount($referrerId);

                return $user;
            });
        } catch (Throwable $e) {
            Log::error('Onboarding submit failed', ['exception' => $e]);
            $this->submitError = __('onboarding.submit_failed');

            return;
        }

        session()->forget([
            config('onboarding.draft_session_key'),
            config('onboarding.progress_session_key'),
        ]);

        Auth::login($user);
        $user->sendEmailVerificationNotification();

        MetaPixel::flashCompleteRegistration();

        $this->redirectRoute('verification.notice');
    }

    public function render()
    {
        $catalog = OnboardingSongs::catalog();
        $query = mb_strtolower(trim($this->song_query));
        $songBrowseMode = $query === '' || $this->looksLikeYoutubeUrl($this->song_query);

        if ($songBrowseMode) {
            $this->ensureSongSuggestions();

            $songs = collect($this->songSuggestions)
                ->map(fn (string $id) => $catalog->firstWhere('id', $id))
                ->filter()
                ->values();
        } else {
            $songs = $catalog->filter(function (array $song) use ($query): bool {
                $haystack = mb_strtolower($song['title'].' '.$song['artist']);

                return str_contains($haystack, $query);
            })->values();
        }

        $songs = $this->pinSelectedSong($songs, $catalog);

        $mottoPresets = collect(range(1, 5))
            ->map(fn (int $n): string => (string) __('onboarding.motto_preset_'.$n))
            ->all();

        return view('livewire.onboarding.wedding-onboarding', [
            'themes' => InvitationTheme::cases(),
            'templates' => InvitationTemplate::cases(),
            'reveals' => InvitationReveal::cases(),
            'selectedTheme' => $this->theme !== ''
                ? InvitationTheme::tryFrom($this->theme)
                : null,
            'selectedTemplate' => $this->template !== ''
                ? InvitationTemplate::tryFrom($this->template)
                : null,
            'selectedReveal' => $this->reveal_animation !== ''
                ? InvitationReveal::tryFrom($this->reveal_animation)
                : null,
            'countedPosition' => OnboardingSteps::countedPosition($this->step),
            'countedTotal' => OnboardingSteps::countedTotal(),
            'progressPercent' => (OnboardingSteps::countedPosition($this->step) / OnboardingSteps::countedTotal()) * 100,
            'isOptional' => OnboardingSteps::isOptional($this->step),
            'isTip' => OnboardingSteps::isTip($this->step),
            'canGoBack' => OnboardingSteps::previous($this->step) !== null,
            'songs' => $songs,
            'songBrowseMode' => $songBrowseMode,
            'schedulePresets' => config('onboarding.schedule_presets', []),
            'mottoPresets' => $mottoPresets,
        ])->title(__('onboarding.meta_title'));
    }

    /**
     * @return array<string, mixed>
     */
    private function rulesForStep(string $step): array
    {
        return match ($step) {
            'names' => [
                'groom_name' => ['required', 'string', 'max:255'],
                'bride_name' => ['required', 'string', 'max:255'],
            ],
            'date' => [
                'wedding_date' => ['required', 'date', 'after:today'],
            ],
            'theme' => [
                'theme' => ['required', 'string', Rule::in(array_column(InvitationTheme::cases(), 'value'))],
            ],
            'template' => [
                'template' => ['required', 'string', Rule::in(array_column(InvitationTemplate::cases(), 'value'))],
            ],
            'reveal' => [
                'reveal_animation' => ['nullable', 'string', Rule::in(array_column(InvitationReveal::cases(), 'value'))],
            ],
            'location' => [
                'location_name' => ['nullable', 'string', 'max:255'],
                'location_address' => ['nullable', 'string', 'max:500'],
            ],
            'motto' => [
                'motto' => ['nullable', 'string', 'max:300'],
            ],
            'cover' => [
                'hero_image' => ['nullable', 'image', 'max:5120'],
            ],
            'song' => [
                'music_url' => ['nullable', 'url', 'max:500'],
            ],
            'schedule' => [
                'scheduleItems' => ['array'],
                'scheduleItems.*.time' => ['nullable', 'date_format:H:i'],
                'scheduleItems.*.title' => ['nullable', 'string', 'max:255'],
                'scheduleItems.*.description' => ['nullable', 'string', 'max:1000'],
            ],
            'guests' => [
                'guests' => ['array', 'max:'.(PlanTier::Free->guestLimit() ?? 50)],
                'guests.*.name' => ['nullable', 'string', 'max:255'],
                'guests.*.email' => ['nullable', 'email', 'max:255'],
                'guests.*.plus_one_allowed' => ['boolean'],
            ],
            'account' => [
                'your_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    private function messagesForStep(string $step): array
    {
        return match ($step) {
            'names' => [
                'groom_name.required' => __('onboarding.groom_name_required'),
                'bride_name.required' => __('onboarding.bride_name_required'),
            ],
            'date' => [
                'wedding_date.required' => __('onboarding.wedding_date_required'),
                'wedding_date.after' => __('onboarding.wedding_date_future'),
            ],
            'theme' => [
                'theme.required' => __('onboarding.theme_required'),
            ],
            'template' => [
                'template.required' => __('onboarding.template_required'),
            ],
            'account' => [
                'your_name.required' => __('onboarding.your_name_required'),
                'email.required' => __('onboarding.email_required'),
                'email.email' => __('onboarding.email_invalid'),
                'email.unique' => __('onboarding.email_taken'),
                'password.required' => __('onboarding.password_required'),
                'password.min' => __('onboarding.password_min'),
                'password.confirmed' => __('onboarding.password_confirmed'),
            ],
            'song' => [
                'music_url.url' => __('onboarding.music_url_invalid'),
            ],
            default => [],
        };
    }

    private function clearOptionalStepData(string $step): void
    {
        match ($step) {
            'reveal' => $this->reveal_animation = '',
            'location' => [$this->location_name, $this->location_address] = ['', ''],
            'motto' => $this->motto = '',
            'cover' => $this->hero_image = null,
            'song' => [$this->music_url, $this->song_query, $this->songPreview] = ['', '', null],
            'schedule' => $this->scheduleItems = [['time' => '', 'title' => '', 'description' => '']],
            'guests' => $this->guests = [['name' => '', 'email' => '', 'plus_one_allowed' => false]],
            default => null,
        };
    }

    /**
     * @return array{groom_name: string, bride_name: string, wedding_date: string, theme: string, template: string, your_name: string, email: string}
     */
    private function accessData(): array
    {
        return [
            'groom_name' => $this->groom_name,
            'bride_name' => $this->bride_name,
            'wedding_date' => $this->wedding_date,
            'theme' => $this->theme,
            'template' => $this->template,
            'your_name' => $this->your_name,
            'email' => $this->email,
        ];
    }

    private function persistProgress(): void
    {
        session()->put(config('onboarding.progress_session_key'), [
            'step' => $this->step,
            'groom_name' => $this->groom_name,
            'bride_name' => $this->bride_name,
            'wedding_date' => $this->wedding_date,
            'theme' => $this->theme,
            'template' => $this->template,
            'reveal_animation' => $this->reveal_animation,
            'location_name' => $this->location_name,
            'location_address' => $this->location_address,
            'motto' => $this->motto,
            'music_url' => $this->music_url,
            'song_query' => $this->song_query,
            'scheduleItems' => $this->scheduleItems,
            'guests' => $this->guests,
            'your_name' => $this->your_name,
            'email' => $this->email,
        ]);
    }

    private function hydrateFromProgress(): void
    {
        $progress = session(config('onboarding.progress_session_key'));

        if (! is_array($progress)) {
            return;
        }

        foreach ([
            'groom_name', 'bride_name', 'wedding_date', 'theme', 'template', 'reveal_animation',
            'location_name', 'location_address', 'motto', 'music_url', 'song_query',
            'your_name', 'email',
        ] as $field) {
            if (array_key_exists($field, $progress) && is_string($progress[$field])) {
                $this->{$field} = $progress[$field];
            }
        }

        if (isset($progress['scheduleItems']) && is_array($progress['scheduleItems'])) {
            $this->scheduleItems = $progress['scheduleItems'];
        }

        if (isset($progress['guests']) && is_array($progress['guests'])) {
            $this->guests = $progress['guests'];
        }

        // Prefer an explicit ?step= query; otherwise restore the saved step.
        $requestStep = request()->query('step');
        if ((! is_string($requestStep) || $requestStep === '') && isset($progress['step']) && is_string($progress['step']) && OnboardingSteps::isValid($progress['step'])) {
            $this->step = $progress['step'];
        }
    }

    private function storeDraft(): void
    {
        $heroTempUrl = null;
        if ($this->hero_image instanceof TemporaryUploadedFile) {
            $heroTempUrl = $this->hero_image->temporaryUrl();
        }

        session()->put(config('onboarding.draft_session_key'), [
            'groom_name' => $this->groom_name,
            'bride_name' => $this->bride_name,
            'wedding_date' => $this->wedding_date,
            'theme' => $this->theme,
            'template' => $this->template,
            'reveal_animation' => $this->reveal_animation,
            'location_name' => $this->location_name,
            'location_address' => $this->location_address,
            'motto' => $this->motto,
            'music_url' => $this->music_url,
            'hero_temp_url' => $heroTempUrl,
            'schedule_items' => $this->normalizedScheduleItems(),
            'invitation_locale' => app()->getLocale(),
        ]);
    }

    private function storeHeroImage(): ?string
    {
        if (! $this->hero_image instanceof TemporaryUploadedFile) {
            return null;
        }

        return $this->hero_image->store('hero-images', MediaDisk::name());
    }

    /**
     * @return list<array{time: string, title: string, description: string}>
     */
    private function normalizedScheduleItems(): array
    {
        $items = [];

        foreach ($this->scheduleItems as $item) {
            $time = trim((string) ($item['time'] ?? ''));
            $title = trim((string) ($item['title'] ?? ''));
            $description = trim((string) ($item['description'] ?? ''));

            if ($time === '' || $title === '') {
                continue;
            }

            $items[] = [
                'time' => $time,
                'title' => $title,
                'description' => $description,
            ];
        }

        return $items;
    }

    /**
     * @return list<array{name: string, email: string, plus_one_allowed: bool}>
     */
    private function normalizedGuests(): array
    {
        $guests = [];

        foreach ($this->guests as $guest) {
            $name = trim((string) ($guest['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $guests[] = [
                'name' => $name,
                'email' => trim((string) ($guest['email'] ?? '')),
                'plus_one_allowed' => (bool) ($guest['plus_one_allowed'] ?? false),
            ];
        }

        return $guests;
    }

    private function pinSelectedSong(Collection $songs, Collection $catalog): Collection
    {
        if ($this->music_url === '') {
            return $songs->values();
        }

        $selected = $catalog->firstWhere('url', $this->music_url);

        if (! is_array($selected)) {
            return $songs->values();
        }

        return $songs
            ->reject(fn (array $song): bool => ($song['url'] ?? null) === $this->music_url)
            ->prepend($selected)
            ->values();
    }

    private function ensureSongSuggestions(): void
    {
        $catalogIds = OnboardingSongs::catalog()->pluck('id')->filter()->values();

        if ($catalogIds->isEmpty()) {
            $this->songSuggestions = [];

            return;
        }

        $valid = collect($this->songSuggestions)
            ->filter(fn ($id): bool => is_string($id) && $catalogIds->contains($id))
            ->unique()
            ->values();

        if ($valid->count() === $catalogIds->count()) {
            $this->songSuggestions = $valid->all();

            return;
        }

        $this->songSuggestions = $catalogIds->shuffle()->values()->all();
    }

    private function looksLikeYoutubeUrl(string $value): bool
    {
        return (bool) preg_match('#(?:youtube\.com|youtu\.be)/#i', $value);
    }

    private function fetchSongPreview(string $url): void
    {
        try {
            $response = Http::timeout(4)->get('https://www.youtube.com/oembed', [
                'url' => $url,
                'format' => 'json',
            ]);

            if (! $response->successful()) {
                return;
            }

            $data = $response->json();

            $this->songPreview = [
                'title' => (string) ($data['title'] ?? ''),
                'thumbnail_url' => (string) ($data['thumbnail_url'] ?? ''),
            ];
        } catch (Throwable) {
            $this->songPreview = null;
        }
    }

    private function uniqueSlug(): string
    {
        $baseSlug = Str::slug($this->groom_name.'-'.$this->bride_name);
        $slug = $baseSlug !== '' ? $baseSlug : 'wedding';
        $counter = 1;

        while (WeddingEvent::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
