<?php

namespace Database\Seeders;

use App\BudgetCalculationType;
use App\BudgetCategory;
use App\BudgetGuestMode;
use App\GuestMessageType;
use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\InvitePlatform;
use App\LinkMode;
use App\LinkType;
use App\Models\Guest;
use App\Models\GuestChild;
use App\Models\GuestMessage;
use App\Models\LinkVisit;
use App\Models\User;
use App\Models\WeddingBudgetItem;
use App\Models\WeddingEvent;
use App\Models\WeddingMenuOption;
use App\PlanTier;
use App\RsvpStatus;
use App\Services\EnsureWeddingMenuOptions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarketingDemoSeeder extends Seeder
{
    public bool $overwrite = false;

    public bool $skipped = false;

    /** @var list<string> */
    public array $seededLocales = [];

    /** @var list<array{locale: string, email: string, slug: string, invitation_url: string, featured_guest_url: string}> */
    public array $seededSummaries = [];

    public ?string $onlyLocale = null;

    public const HERO_IMAGE = 'hero-images/01KYJ42XFTRNRB41FJXV31HA9W.webp';

    public const GUEST_COUNT = 150;

    public const CONFIRMED_COUNT = 90;

    public const DECLINED_COUNT = 8;

    public const MESSAGE_COUNT = 30;

    public const PASSWORD = '5E3L1Y84uFdd';

    /**
     * @return list<string>
     */
    public static function supportedLocales(): array
    {
        return ['bs', 'hr', 'de', 'en', 'sr_Latn'];
    }

    public function run(): void
    {
        $locales = $this->onlyLocale
            ? [$this->onlyLocale]
            : self::supportedLocales();

        foreach ($locales as $locale) {
            $this->seedLocale($locale);
        }
    }

    private function seedLocale(string $locale): void
    {
        $profile = $this->loadProfile($locale);
        $email = $profile['user']['email'];
        $slug = $profile['event']['slug'];

        $userExists = User::query()->where('email', $email)->exists();
        $eventExists = WeddingEvent::query()->where('slug', $slug)->exists();

        if (! $this->overwrite && ($userExists || $eventExists)) {
            $this->skipped = true;

            return;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $profile['user']['name'],
                'password' => Hash::make($profile['user']['password'] ?? self::PASSWORD),
                'is_admin' => false,
                'locale' => $locale,
            ]
        );

        // email_verified_at is not fillable on User; force it so Filament login skips verification.
        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        $eventData = $profile['event'];
        $weddingDate = Carbon::parse($eventData['wedding_date']);
        $rsvpDeadline = Carbon::parse($eventData['rsvp_deadline']);
        $primary = $eventData['locations'][0];

        $event = WeddingEvent::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'user_id' => $user->id,
                'is_demo' => false,
                'bride_name' => $eventData['bride_name'],
                'groom_name' => $eventData['groom_name'],
                'wedding_date' => $weddingDate,
                'location_name' => $primary['name'],
                'location_address' => $primary['address'],
                'location_lat' => $primary['lat'],
                'location_lng' => $primary['lng'],
                'theme' => InvitationTheme::from($eventData['theme']),
                'template' => InvitationTemplate::from($eventData['template']),
                'reveal_animation' => InvitationReveal::from($eventData['reveal_animation']),
                'link_mode' => LinkMode::TokenOnly,
                'music_url' => $eventData['music_url'],
                'hero_image' => self::HERO_IMAGE,
                'rsvp_deadline' => $rsvpDeadline,
                'accommodation_enabled' => true,
                'is_active' => true,
                'plan_tier' => PlanTier::Premium,
                'guest_limit' => PlanTier::Premium->guestLimit(),
                'invitation_locale' => $locale,
                'motto' => $eventData['motto'],
                'send_message' => $eventData['send_message'],
            ]
        );

        app(EnsureWeddingMenuOptions::class)->handle($event);

        $event->menuOptions()->whereNull('platform_key')->delete();

        $customMenu = WeddingMenuOption::query()->create([
            'wedding_event_id' => $event->id,
            'platform_key' => null,
            'label' => $eventData['custom_menu_label'],
            'is_visible' => true,
            'sort_order' => 10,
        ]);

        $menuOptions = $event->menuOptions()->orderBy('sort_order')->get();
        $menuIds = $menuOptions->pluck('id')->all();
        $menuIds[] = $customMenu->id;
        $menuIds = array_values(array_unique($menuIds));

        $event->locations()->delete();
        $event->locations()->createMany($eventData['locations']);
        $event->syncLegacyLocationFromPrimary();

        $event->scheduleItems()->delete();
        $event->scheduleItems()->createMany($eventData['schedule']);

        $event->guestMessages()->delete();
        $event->guests()->forceDelete();
        LinkVisit::query()->where('wedding_event_id', $event->id)->delete();

        $yesGuests = [];
        $featuredGuest = null;

        for ($index = 0; $index < self::GUEST_COUNT; $index++) {
            $guestNumber = $index + 1;
            $firstName = $profile['first_names'][$index % count($profile['first_names'])];
            $lastName = $profile['last_names'][$index % count($profile['last_names'])];
            $name = $index === 0
                ? $profile['featured_guest_name']
                : "{$firstName} {$lastName}";

            $plusOneAllowed = $index % 5 < 2;
            $rsvpStatus = $this->rsvpStatusForIndex($index);
            $respondedAt = $rsvpStatus !== null
                ? $weddingDate->copy()->subDays(45 - ($index % 20))
                : null;

            $plusOneName = null;
            if ($plusOneAllowed && $rsvpStatus === RsvpStatus::Yes && $index % 4 !== 3) {
                $plusOneFirst = $profile['plus_one_first_names'][$index % count($profile['plus_one_first_names'])];
                $plusOneLast = $profile['last_names'][($index + 17) % count($profile['last_names'])];
                $plusOneName = "{$plusOneFirst} {$plusOneLast}";
            }

            $partySize = 1 + ($plusOneName ? 1 : 0);
            $menuOptionId = null;
            $plusOneMenuOptionId = null;
            $accommodationCount = null;

            if ($rsvpStatus === RsvpStatus::Yes) {
                $menuOptionId = $menuIds[$index % count($menuIds)];
                if ($plusOneName) {
                    $plusOneMenuOptionId = $menuIds[($index + 1) % count($menuIds)];
                }

                if ($index % 3 !== 2) {
                    $accommodationCount = min($partySize, 1 + ($index % max(1, $partySize)));
                }
            }

            $token = $index === 0
                ? $profile['featured_guest_token']
                : Str::random(32);

            $guest = Guest::query()->create([
                'wedding_event_id' => $event->id,
                'name' => $name,
                'email' => sprintf('marketing-%s-guest-%03d@%s', $locale, $guestNumber, $profile['email_domain']),
                'phone' => sprintf('%s%07d', $profile['phone_prefix'], 1000000 + $guestNumber),
                'plus_one_allowed' => $plusOneAllowed,
                'plus_one_name' => $plusOneName,
                'plus_one_seating_name' => $plusOneName,
                'token' => $token,
                'rsvp_status' => $rsvpStatus,
                'rsvp_responded_at' => $respondedAt,
                'rsvp_note' => $this->rsvpNoteForIndex($profile, $index),
                'menu_option_id' => $menuOptionId,
                'plus_one_menu_option_id' => $plusOneMenuOptionId,
                'accommodation_count' => $accommodationCount,
                'invite_sent_at' => $rsvpStatus === RsvpStatus::Yes || $index % 3 === 0
                    ? $weddingDate->copy()->subDays(60 - ($index % 15))
                    : null,
                'invite_platform' => ($rsvpStatus === RsvpStatus::Yes || $index % 3 === 0)
                    ? $this->invitePlatformForIndex($index)
                    : null,
                'invitation_locale' => $index === 0
                    ? $locale
                    : ($index % 11 === 0 && $locale !== 'en' ? 'en' : $locale),
            ]);

            if ($rsvpStatus === RsvpStatus::Yes && $index % 7 === 0) {
                $childName = $profile['child_names'][$index % count($profile['child_names'])];
                GuestChild::query()->create([
                    'guest_id' => $guest->id,
                    'name' => $childName,
                    'seating_name' => $childName,
                    'menu_option_id' => $menuIds[($index + 2) % count($menuIds)],
                    'sort_order' => 0,
                ]);

                if ($accommodationCount !== null) {
                    $guest->forceFill([
                        'accommodation_count' => min(($accommodationCount ?? 0) + 1, $partySize + 1),
                    ])->save();
                }
            }

            if ($rsvpStatus === RsvpStatus::Yes) {
                $yesGuests[] = $guest->fresh(['children']);
            }

            if ($index === 0) {
                $featuredGuest = $guest;
            }
        }

        foreach (array_slice($yesGuests, 0, self::MESSAGE_COUNT) as $messageIndex => $guest) {
            GuestMessage::query()->create([
                'wedding_event_id' => $event->id,
                'guest_id' => $guest->id,
                'sender_name' => $guest->name,
                'type' => GuestMessageType::Text,
                'content' => $profile['guest_messages'][$messageIndex],
                'seen_at' => $messageIndex % 3 === 0
                    ? null
                    : $weddingDate->copy()->subDays(10 + ($messageIndex % 5)),
            ]);
        }

        $this->seedSeatingPlan($event, $yesGuests);
        $this->seedLinkVisits($event, $yesGuests, $weddingDate);
        $this->seedBudget($event, $locale);

        $this->seededLocales[] = $locale;
        $this->seededSummaries[] = [
            'locale' => $locale,
            'email' => $email,
            'slug' => $slug,
            'invitation_url' => $event->fresh()->public_url,
            'featured_guest_url' => $featuredGuest
                ? $event->guestUrl($featuredGuest->fresh())
                : $event->fresh()->public_url,
        ];
    }

    private function seedBudget(WeddingEvent $event, string $locale): void
    {
        $labels = match ($locale) {
            'de' => [
                'dinner' => 'Festessen',
                'band' => 'Band',
                'attire' => 'Kleid & Anzug',
                'invites' => 'Einladungen',
            ],
            'en' => [
                'dinner' => 'Reception dinner',
                'band' => 'Live band',
                'attire' => 'Dress & suit',
                'invites' => 'Invitations',
            ],
            'sr_Latn' => [
                'dinner' => 'Svečana večera',
                'band' => 'Bend',
                'attire' => 'Venčanica i odelo',
                'invites' => 'Pozivnice',
            ],
            default => [
                'dinner' => 'Svečana večera',
                'band' => 'Bend',
                'attire' => 'Vjenčanica i odijelo',
                'invites' => 'Pozivnice',
            ],
        };

        $currency = $locale === 'bs' ? 'BAM' : 'EUR';

        $event->update([
            'budget_currency' => $currency,
            'budget_guest_mode' => BudgetGuestMode::Confirmed,
            'budget_target' => 10000,
        ]);

        WeddingBudgetItem::query()->where('wedding_event_id', $event->id)->delete();

        $event->budgetItems()->createMany([
            [
                'name' => $labels['dinner'],
                'category' => BudgetCategory::SalaIVecera,
                'calculation_type' => BudgetCalculationType::PerPerson,
                'amount' => 45,
                'is_paid' => false,
                'sort_order' => 1,
            ],
            [
                'name' => $labels['band'],
                'category' => BudgetCategory::BendIGlazba,
                'calculation_type' => BudgetCalculationType::Fixed,
                'amount' => 2500,
                'is_paid' => true,
                'sort_order' => 2,
            ],
            [
                'name' => $labels['attire'],
                'category' => BudgetCategory::VjencanicaIOdijelo,
                'calculation_type' => BudgetCalculationType::Fixed,
                'amount' => 1100,
                'is_paid' => true,
                'sort_order' => 3,
            ],
            [
                'name' => $labels['invites'],
                'category' => BudgetCategory::PozivniceITisak,
                'calculation_type' => BudgetCalculationType::Fixed,
                'amount' => 80,
                'is_paid' => false,
                'sort_order' => 4,
            ],
        ]);
    }

    /**
     * @param  list<Guest>  $yesGuests
     */
    private function seedSeatingPlan(WeddingEvent $event, array $yesGuests): void
    {
        $assignees = [];

        foreach (array_slice($yesGuests, 0, 70) as $guest) {
            $assignees[] = $guest->id;
            if (filled($guest->plus_one_name)) {
                $assignees[] = -$guest->id;
            }
            foreach ($guest->children as $child) {
                $assignees[] = $child->seatingAssigneeKey();
            }
        }

        $tables = [
            $this->makeTable('t_head', 'head', 500, 120, 10, 'Glavni sto', $assignees, true),
            $this->makeTable('t_1', 'round', 220, 320, 8, 'Sto 1', $assignees),
            $this->makeTable('t_2', 'round', 500, 320, 8, 'Sto 2', $assignees),
            $this->makeTable('t_3', 'round', 780, 320, 8, 'Sto 3', $assignees),
            $this->makeTable('t_4', 'rect', 220, 520, 8, 'Sto 4', $assignees),
            $this->makeTable('t_5', 'rect', 500, 520, 8, 'Sto 5', $assignees),
            $this->makeTable('t_6', 'round', 780, 520, 8, 'Sto 6', $assignees),
            $this->makeTable('t_7', 'round', 350, 700, 8, 'Sto 7', $assignees),
            $this->makeTable('t_8', 'round', 650, 700, 8, 'Sto 8', $assignees),
        ];

        // Localized head-table labels where helpful
        $locale = $event->invitation_locale;
        if ($locale === 'de') {
            $tables[0]['label'] = 'Brauttisch';
            foreach ($tables as $i => $table) {
                if ($i > 0) {
                    $tables[$i]['label'] = 'Tisch '.$i;
                }
            }
        } elseif ($locale === 'en') {
            $tables[0]['label'] = 'Head table';
            foreach ($tables as $i => $table) {
                if ($i > 0) {
                    $tables[$i]['label'] = 'Table '.$i;
                }
            }
        } elseif ($locale === 'hr') {
            $tables[0]['label'] = 'Glavni stol';
            foreach ($tables as $i => $table) {
                if ($i > 0) {
                    $tables[$i]['label'] = 'Stol '.$i;
                }
            }
        }

        $event->forceFill(['seating_plan' => ['tables' => $tables]])->save();
    }

    /**
     * @param  list<int|string>  $assignees
     * @return array<string, mixed>
     */
    private function makeTable(
        string $id,
        string $type,
        int $x,
        int $y,
        int $chairCount,
        string $label,
        array &$assignees,
        bool $withCouple = false,
    ): array {
        $seats = array_fill(0, $chairCount, null);

        if ($withCouple) {
            $seats[0] = 'groom';
            $seats[1] = 'bride';
            for ($i = 2; $i < $chairCount; $i++) {
                $seats[$i] = array_shift($assignees);
            }
        } else {
            for ($i = 0; $i < $chairCount; $i++) {
                $seats[$i] = array_shift($assignees);
            }
        }

        $table = [
            'id' => $id,
            'type' => $type,
            'x' => $x,
            'y' => $y,
            'rotation' => 0,
            'chair_count' => $chairCount,
            'label' => $label,
            'seats' => $seats,
        ];

        if ($type === 'round') {
            $table['radius'] = 70;
        } elseif ($type === 'head') {
            $table['width'] = 320;
            $table['height'] = 70;
        } else {
            $table['width'] = 160;
            $table['height'] = 100;
        }

        return $table;
    }

    /**
     * @param  list<Guest>  $yesGuests
     */
    private function seedLinkVisits(WeddingEvent $event, array $yesGuests, Carbon $weddingDate): void
    {
        $devices = ['mobile', 'desktop', 'tablet'];
        $browsers = ['Chrome', 'Safari', 'Firefox', 'Edge'];
        $oses = ['iOS', 'Android', 'macOS', 'Windows'];

        for ($i = 0; $i < 220; $i++) {
            $guest = $yesGuests[$i % max(1, count($yesGuests))] ?? null;
            $isPersonal = $guest !== null && $i % 3 !== 0;

            LinkVisit::query()->create([
                'wedding_event_id' => $event->id,
                'guest_id' => $isPersonal ? $guest->id : null,
                'link_type' => $isPersonal ? LinkType::Personal : LinkType::Public,
                'ip_hash' => hash('sha256', 'marketing-demo-'.$event->slug.'-'.$i),
                'user_agent' => 'MarketingDemoSeeder/1.0',
                'referer' => null,
                'device_type' => $devices[$i % count($devices)],
                'browser' => $browsers[$i % count($browsers)],
                'os' => $oses[$i % count($oses)],
                'visited_at' => $weddingDate->copy()->subDays(40 - ($i % 35))->addMinutes($i * 7),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private function rsvpNoteForIndex(array $profile, int $index): ?string
    {
        $notes = $profile['rsvp_notes'];

        if ($index < 8) {
            return $notes[$index];
        }

        if ($index >= self::CONFIRMED_COUNT && $index < self::CONFIRMED_COUNT + 2) {
            return $notes[$index - self::CONFIRMED_COUNT + 8] ?? null;
        }

        return null;
    }

    private function rsvpStatusForIndex(int $index): ?RsvpStatus
    {
        if ($index < self::CONFIRMED_COUNT) {
            return RsvpStatus::Yes;
        }

        if ($index < self::CONFIRMED_COUNT + self::DECLINED_COUNT) {
            return RsvpStatus::No;
        }

        return null;
    }

    private function invitePlatformForIndex(int $index): InvitePlatform
    {
        return match ($index % 5) {
            0 => InvitePlatform::WhatsApp,
            1 => InvitePlatform::Viber,
            2 => InvitePlatform::Telegram,
            3 => InvitePlatform::FacebookMessenger,
            default => InvitePlatform::Manual,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function loadProfile(string $locale): array
    {
        $path = database_path('seeders/data/marketing-demo/'.$locale.'.php');

        if (! is_file($path)) {
            throw new \InvalidArgumentException("Missing marketing demo profile for locale [{$locale}].");
        }

        /** @var array<string, mixed> $profile */
        $profile = require $path;

        return $profile;
    }
}
