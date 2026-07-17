<?php

namespace Database\Seeders;

use App\GuestMessageType;
use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\InvitePlatform;
use App\LinkMode;
use App\Models\Guest;
use App\Models\GuestMessage;
use App\Models\User;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class MarketingDemoSeeder extends Seeder
{
    public bool $overwrite = false;

    public bool $skipped = false;

    private const USER_EMAIL = 'jasmin-djordje@nasdan.ba';

    private const EVENT_SLUG = 'jasmina-djordje';

    private const GUEST_COUNT = 150;

    private const CONFIRMED_COUNT = 90;

    private const DECLINED_COUNT = 8;

    private const MESSAGE_COUNT = 30;

    /** @var list<string> */
    private const FIRST_NAMES = [
        'Emir', 'Adnan', 'Haris', 'Kenan', 'Mirza', 'Tarik', 'Dino', 'Senad', 'Faruk', 'Nedim',
        'Alen', 'Edin', 'Amer', 'Armin', 'Vedad', 'Nedžad', 'Elvir', 'Samir', 'Kemal', 'Dženan',
        'Husein', 'Sead', 'Munib', 'Halid', 'Eldin', 'Meho', 'Fahrudin', 'Ibrahim', 'Ismet', 'Osman',
        'Mehmed', 'Omer', 'Mustafa', 'Halil', 'Hasan', 'Rešid', 'Esad', 'Muamer', 'Sanel', 'Admir',
        'Nermin', 'Nihad', 'Ermin', 'Ajdin', 'Benjamin', 'Eldar', 'Lejla', 'Amira', 'Amina', 'Selma',
        'Emina', 'Ajla', 'Medina', 'Melisa', 'Lamija', 'Ajša', 'Fatima', 'Hana', 'Ena', 'Elma',
        'Merima', 'Alma', 'Aldina', 'Belma', 'Ajna', 'Ema', 'Dina', 'Aida', 'Zehra', 'Nermina',
        'Belkisa', 'Nejra', 'Hamida', 'Sabina', 'Sanela', 'Amela', 'Mirela', 'Senada', 'Azra', 'Lamia',
        'Meliha', 'Esma', 'Lejla', 'Amna', 'Irma', 'Selmina', 'Edina', 'Maja', 'Ivana', 'Ana',
        'Marko', 'Nikola', 'Stefan', 'Luka', 'Filip', 'Damir', 'Denis', 'Arnel', 'Benjamin', 'Edvin',
    ];

    /** @var list<string> */
    private const LAST_NAMES = [
        'Hadžić', 'Kovačević', 'Begović', 'Delić', 'Softić', 'Hodžić', 'Imamović', 'Mehić', 'Salihović', 'Duraković',
        'Hasanović', 'Jusić', 'Karić', 'Mujić', 'Omeragić', 'Pirić', 'Salkić', 'Tihić', 'Zukić', 'Avdić',
        'Bašić', 'Čaušević', 'Dervišević', 'Eminić', 'Fazlić', 'Gavrić', 'Halilović', 'Ibrahimović', 'Jahić', 'Kadrić',
        'Latić', 'Memić', 'Nuhić', 'Osmanović', 'Pavlić', 'Redžić', 'Smajić', 'Tahirović', 'Usenić', 'Vuković',
        'Zukorlić', 'Ahmetović', 'Bajramović', 'Cerimagić', 'Dizdarević', 'Fejzić', 'Glamočak', 'Hrustić', 'Ibišević', 'Janković',
        'Kurtović', 'Musić', 'Pehlivanović', 'Ramić', 'Suljić', 'Terzić', 'Užičanin', 'Vranić', 'Zornić', 'Čelić',
    ];

    /** @var list<string> */
    private const PLUS_ONE_FIRST_NAMES = [
        'Amira', 'Selma', 'Emina', 'Lejla', 'Ajla', 'Medina', 'Hana', 'Ena', 'Alma', 'Belma',
        'Emir', 'Adnan', 'Haris', 'Kenan', 'Mirza', 'Tarik', 'Dino', 'Senad', 'Faruk', 'Alen',
        'Edin', 'Amer', 'Armin', 'Vedad', 'Elvir', 'Samir', 'Kemal', 'Sead', 'Halid', 'Eldin',
    ];

    /** @var list<string> */
    private const RSVP_NOTES = [
        'Sa velikom radošću potvrđujemo dolazak. Vidimo se u džamiji!',
        'Hvala na prekrasnoj pozivnici. Dolazimo cijela porodica.',
        'Jedva čekamo vaš veliki dan. Čestitamo unaprijed!',
        'Potvrđujemo dolazak za nas dvoje. Sretno vam!',
        'Biće nam čast biti dio ovog posebnog dana.',
        'Radujemo se nikahu i proslavi. Do tada!',
        'Hvala vam što ste nas uključili. Vidimo se uskoro!',
        'Neka vam Allah blagoslovi brak. Dolazimo sa radošću.',
        'Nažalost ne možemo doći zbog obaveza van grada. Sretno vam!',
        'Moramo otkazati dolazak zbog porodičnih obaveza. Čestitamo od srca!',
    ];

    /** @var list<string> */
    private const GUEST_MESSAGES = [
        'Dragi mladenci, čestitamo vam od srca! Jedva čekamo vaš veliki dan.',
        'Vaša ljubav nas inspiriše. Sretno vjenčanje, Jasmine i Đorđe!',
        'Hvala vam na prekrasnoj pozivnici. Vidimo se u džamiji!',
        'Neka vam brak bude ispunjen radosti, mira i blagoslova.',
        'Sa radošću potvrđujemo dolazak. Čestitamo vam unaprijed!',
        'Vaše vjenčanje će biti prekrasno — jedva čekamo proslavu.',
        'Neka vam Allah blagoslovi zajednički put. Sretno!',
        'Hvala na pozivu. Biće nam čast biti dio ovog posebnog dana.',
        'Čestitamo! Neka svaki dan bude kao prvi — pun ljubavi.',
        'Draga Jasmina i Đorđe, želimo vam sve najljepše u braku.',
        'Vaša priča je predivna. Radujemo se slavlju!',
        'Potvrđujemo dolazak za nas dvoje. Vidimo se uskoro!',
        'Neka vam kuća bude puna smijeha i topline.',
        'Hvala vam što ste nas uključili u ovaj poseban trenutak.',
        'Čestitamo od cijele porodice! Neka vam bude blagoslovljeno.',
        'Jedva čekamo da slavimo s vama u Sarajevu.',
        'Vaša pozivnica nas je oduševila. Sretno vjenčanje!',
        'Neka vam ljubav raste iz dana u dan. Sve najbolje!',
        'Biće nam čast svjedočiti vašem “da”.',
        'Hvala na lijepim riječima u pozivnici. Vidimo se!',
        'Čestitamo mladencima! Neka vam brak donese samo sreću.',
        'Radujemo se nikahu i proslavi. Sretno vam bilo!',
        'Vaša sreća nam mnogo znači. Sve najbolje!',
        'Potvrđujemo dolazak i šaljemo puno ljubavi.',
        'Neka vam Allah da zdravlje, mir i obilje u braku.',
        'Prekrasno vjenčanje nas čeka — hvala na pozivu!',
        'Čestitamo! Neka svaki dan bude nova avantura u dvoje.',
        'Draga Jasmina, drago Đorđe — sretno vam bilo zauvijek!',
        'Vaša ljubav je lijep primjer za sve nas. Sretno!',
        'Jedva čekamo da plesamo na vašoj proslavi. Čestitamo!',
    ];

    public function run(): void
    {
        $userExists = User::query()->where('email', self::USER_EMAIL)->exists();
        $eventExists = WeddingEvent::query()->where('slug', self::EVENT_SLUG)->exists();

        if (! $this->overwrite && ($userExists || $eventExists)) {
            $this->skipped = true;

            return;
        }

        $user = User::query()->updateOrCreate(
            ['email' => self::USER_EMAIL],
            [
                'name' => 'Jasmina&Đorđe',
                'password' => Hash::make('5E3L1Y84uFdd'),
                'is_admin' => false,
                'locale' => 'bs',
                'email_verified_at' => now(),
            ]
        );

        $weddingDate = Carbon::create(2026, 9, 19, 16, 0, 0);
        $rsvpDeadline = Carbon::create(2026, 9, 5);

        $event = WeddingEvent::query()->updateOrCreate(
            ['slug' => self::EVENT_SLUG],
            [
                'user_id' => $user->id,
                'is_demo' => false,
                'bride_name' => 'Jasmina',
                'groom_name' => 'Đorđe',
                'wedding_date' => $weddingDate,
                'location_name' => 'Gazi Husrev-begova džamija',
                'location_address' => 'Sarajevo, Bosna i Hercegovina',
                'location_lat' => 43.8594,
                'location_lng' => 18.4286,
                'theme' => InvitationTheme::RoyalWedding,
                'template' => InvitationTemplate::Classic,
                'reveal_animation' => InvitationReveal::Envelope,
                'link_mode' => LinkMode::TokenOnly,
                'music_url' => 'https://www.youtube.com/watch?v=2Vv-BfVoq4g',
                'rsvp_deadline' => $rsvpDeadline,
                'is_active' => true,
                'motto' => 'Dvije duše, jedno srce — zauvijek naše "da".',
                'send_message' => <<<'TEXT'
Dragi/a {name},

sa velikom radošću pozivamo Vas da budete dio našeg najljepšeg dana — vjenčanja Jasmine i Đorđa.

Vaš lični link za potvrdu dolaska:
{link}

S ljubavlju,
Jasmina & Đorđe
TEXT,
            ]
        );

        $event->scheduleItems()->delete();
        $event->scheduleItems()->createMany([
            ['time' => '08:00', 'title' => 'Odlazak po mladu', 'description' => 'Mladoženja i svatovi dolaze po mladu.', 'sort_order' => 1],
            ['time' => '10:00', 'title' => 'Šerijatsko vjenčanje (nikah)', 'description' => 'Vjenčanje u džamiji.', 'sort_order' => 2],
            ['time' => '12:00', 'title' => 'Svečani ručak', 'description' => 'Ručak za porodicu i najbliže goste.', 'sort_order' => 3],
            ['time' => '16:00', 'title' => 'Fotografisanje', 'description' => 'Zajedničko fotografisanje mladenaca.', 'sort_order' => 4],
            ['time' => '19:00', 'title' => 'Svečana večera i proslava', 'description' => 'Večera, ples i slavlje.', 'sort_order' => 5],
        ]);

        $event->guests()->forceDelete();

        $yesGuests = [];

        for ($index = 0; $index < self::GUEST_COUNT; $index++) {
            $guestNumber = $index + 1;
            $firstName = self::FIRST_NAMES[$index % count(self::FIRST_NAMES)];
            $lastName = self::LAST_NAMES[$index % count(self::LAST_NAMES)];
            $plusOneAllowed = $index % 5 < 2;
            $rsvpStatus = $this->rsvpStatusForIndex($index);
            $respondedAt = $rsvpStatus !== null
                ? $weddingDate->copy()->subDays(45 - ($index % 20))
                : null;

            $rsvpNote = $this->rsvpNoteForIndex($index);

            $plusOneName = null;
            if ($plusOneAllowed && $rsvpStatus === RsvpStatus::Yes && $index % 4 !== 3) {
                $plusOneFirst = self::PLUS_ONE_FIRST_NAMES[$index % count(self::PLUS_ONE_FIRST_NAMES)];
                $plusOneLast = self::LAST_NAMES[($index + 17) % count(self::LAST_NAMES)];
                $plusOneName = "{$plusOneFirst} {$plusOneLast}";
            }

            $guest = Guest::query()->create([
                'wedding_event_id' => $event->id,
                'name' => "{$firstName} {$lastName}",
                'email' => sprintf('marketing-guest-%03d@nasdan.ba', $guestNumber),
                'phone' => sprintf('+3876%07d', 1000000 + $guestNumber),
                'plus_one_allowed' => $plusOneAllowed,
                'plus_one_name' => $plusOneName,
                'rsvp_status' => $rsvpStatus,
                'rsvp_responded_at' => $respondedAt,
                'rsvp_note' => $rsvpNote,
                'invite_sent_at' => $rsvpStatus === RsvpStatus::Yes || $index % 3 === 0
                    ? $weddingDate->copy()->subDays(60 - ($index % 15))
                    : null,
                'invite_platform' => ($rsvpStatus === RsvpStatus::Yes || $index % 3 === 0)
                    ? $this->invitePlatformForIndex($index)
                    : null,
            ]);

            if ($rsvpStatus === RsvpStatus::Yes) {
                $yesGuests[] = $guest;
            }
        }

        foreach (array_slice($yesGuests, 0, self::MESSAGE_COUNT) as $messageIndex => $guest) {
            GuestMessage::query()->create([
                'wedding_event_id' => $event->id,
                'guest_id' => $guest->id,
                'sender_name' => $guest->name,
                'type' => GuestMessageType::Text,
                'content' => self::GUEST_MESSAGES[$messageIndex],
                'seen_at' => $messageIndex % 3 === 0
                    ? null
                    : $weddingDate->copy()->subDays(10 + ($messageIndex % 5)),
            ]);
        }
    }

    private function rsvpNoteForIndex(int $index): ?string
    {
        if ($index < 8) {
            return self::RSVP_NOTES[$index];
        }

        if ($index >= self::CONFIRMED_COUNT && $index < self::CONFIRMED_COUNT + 2) {
            return self::RSVP_NOTES[$index - self::CONFIRMED_COUNT + 8];
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
}
