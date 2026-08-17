<?php

namespace Database\Seeders;

use App\InvitationTheme;
use App\LinkMode;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\PlanTier;
use App\Services\EnsureWeddingMenuOptions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WeddingEventSeeder extends Seeder
{
    public const HERO_IMAGE = MarketingDemoSeeder::HERO_IMAGE;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@myday.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $customer = User::query()->updateOrCreate(
            ['email' => 'customer@myday.test'],
            [
                'name' => 'Demo Par',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );

        WeddingEvent::query()->where('slug', 'demo')->delete();

        $this->seedEvent(
            slug: 'milan-anja',
            groom: 'Milan',
            bride: 'Anja',
            locationName: 'Crkva Svetog Marka',
            locationAddress: 'Beograd, Srbija',
            lat: 44.8176,
            lng: 20.4633,
            theme: InvitationTheme::AmberGold,
            schedule: [
                ['time' => '14:00', 'title' => 'Dolazak gostiju', 'description' => 'Dobrodošlica i piće dobrodošlice.', 'sort_order' => 1],
                ['time' => '15:00', 'title' => 'Vjenčanje', 'description' => 'Ceremonija i poljubac mira.', 'sort_order' => 2],
                ['time' => '17:00', 'title' => 'Svečanost', 'description' => 'Večera, ples i slavlje.', 'sort_order' => 3],
            ],
            guests: [
                ['name' => 'Marko Jovic', 'email' => 'marko@example.com'],
                ['name' => 'Jelena Petrovic', 'email' => 'jelena@example.com'],
                ['name' => 'Stefan Nikolic', 'email' => 'stefan@example.com'],
            ],
            userId: $customer->id,
        );

        // Bosnian public demos (Sarajevo)
        $this->seedEvent(
            slug: 'demo-islamsko',
            groom: 'Amer',
            bride: 'Amina',
            locationName: 'Gazi Husrev-begova džamija',
            locationAddress: 'Sarajevo, Bosna i Hercegovina',
            lat: 43.8594,
            lng: 18.4286,
            theme: InvitationTheme::AmberGold,
            schedule: [
                ['time' => '08:00', 'title' => 'Odlazak po mladu', 'description' => 'Mladoženja i svatovi dolaze po mladu.', 'sort_order' => 1],
                ['time' => '09:00', 'title' => 'Općinsko/matičarsko vjenčanje', 'description' => 'Potpisivanje u matičnom uredu.', 'sort_order' => 2],
                ['time' => '10:00', 'title' => 'Šerijatsko vjenčanje (nikah)', 'description' => 'Vjenčanje u džamiji.', 'sort_order' => 3],
                ['time' => '12:00', 'title' => 'Svečani ručak za goste', 'description' => 'Ručak za porodicu i najbliže goste.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Svečana večera i proslava', 'description' => 'Večera, ples i slavlje.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo Gost', 'email' => 'demo-islamsko@example.com'],
            ],
            isDemo: true,
            extraLocations: [
                [
                    'label' => 'Opština',
                    'name' => 'Općina Stari Grad',
                    'address' => 'Sarajevo, Bosna i Hercegovina',
                    'lat' => 43.8599,
                    'lng' => 18.4310,
                    'is_primary' => false,
                    'sort_order' => 1,
                ],
            ],
            primaryLocationLabel: 'Džamija',
        );

        $this->seedEvent(
            slug: 'demo-krscansko',
            groom: 'Milan',
            bride: 'Ana',
            locationName: 'Katedrala Srca Isusova',
            locationAddress: 'Sarajevo, Bosna i Hercegovina',
            lat: 43.8563,
            lng: 18.4131,
            theme: InvitationTheme::RoyalWedding,
            schedule: [
                ['time' => '09:00', 'title' => 'Odlazak po mladu', 'description' => 'Mladoženja i svatovi dolaze po mladu.', 'sort_order' => 1],
                ['time' => '10:00', 'title' => 'Vjenčanje u crkvi', 'description' => 'Crkvena ceremonija i blagoslov.', 'sort_order' => 2],
                ['time' => '12:00', 'title' => 'Svečani ručak za goste', 'description' => 'Ručak za porodicu i najbliže goste.', 'sort_order' => 3],
                ['time' => '15:00', 'title' => 'Fotografisanje', 'description' => 'Zajedničko fotografisanje mladenaca.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Svečana večera i proslava', 'description' => 'Večera, ples i slavlje.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo Gost', 'email' => 'demo-krscansko@example.com'],
            ],
            isDemo: true,
        );

        // English public demos (London)
        $this->seedEvent(
            slug: 'demo-islamsko-en',
            groom: 'Omar',
            bride: 'Layla',
            locationName: 'London Central Mosque',
            locationAddress: 'London, United Kingdom',
            lat: 51.5286,
            lng: -0.1650,
            theme: InvitationTheme::AmberGold,
            schedule: [
                ['time' => '10:00', 'title' => 'Guest arrival', 'description' => 'Welcome and seating.', 'sort_order' => 1],
                ['time' => '11:00', 'title' => 'Civil ceremony', 'description' => 'Signing at the registry office.', 'sort_order' => 2],
                ['time' => '12:00', 'title' => 'Islamic marriage (nikah)', 'description' => 'Wedding ceremony at the mosque.', 'sort_order' => 3],
                ['time' => '14:00', 'title' => 'Family luncheon', 'description' => 'Lunch for family and closest guests.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Wedding dinner & celebration', 'description' => 'Dinner, dancing and celebration.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo Guest', 'email' => 'demo-islamsko-en@example.com'],
            ],
            isDemo: true,
            invitationLocale: 'en',
        );

        $this->seedEvent(
            slug: 'demo-krscansko-en',
            groom: 'Oliver',
            bride: 'Emily',
            locationName: 'St Paul\'s Cathedral',
            locationAddress: 'London, United Kingdom',
            lat: 51.5138,
            lng: -0.0984,
            theme: InvitationTheme::RoyalWedding,
            schedule: [
                ['time' => '11:00', 'title' => 'Guest arrival', 'description' => 'Welcome drinks and seating.', 'sort_order' => 1],
                ['time' => '12:00', 'title' => 'Church ceremony & blessing', 'description' => 'Church ceremony and blessing.', 'sort_order' => 2],
                ['time' => '14:00', 'title' => 'Reception lunch', 'description' => 'Lunch for family and closest guests.', 'sort_order' => 3],
                ['time' => '16:00', 'title' => 'Wedding photo session', 'description' => 'Group photos with the newlyweds.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Wedding dinner & celebration', 'description' => 'Dinner, dancing and celebration.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo Guest', 'email' => 'demo-krscansko-en@example.com'],
            ],
            isDemo: true,
            invitationLocale: 'en',
        );

        // German public demos (Munich)
        $this->seedEvent(
            slug: 'demo-islamsko-de',
            groom: 'Yusuf',
            bride: 'Aylin',
            locationName: 'Pasinger Moschee',
            locationAddress: 'München, Deutschland',
            lat: 48.1470,
            lng: 11.4610,
            theme: InvitationTheme::AmberGold,
            schedule: [
                ['time' => '10:00', 'title' => 'Ankunft der Gäste', 'description' => 'Begrüßung und Platznehmen.', 'sort_order' => 1],
                ['time' => '11:00', 'title' => 'Standesamtliche Trauung', 'description' => 'Unterschrift beim Standesamt.', 'sort_order' => 2],
                ['time' => '12:00', 'title' => 'Islamische Trauung (Nikah)', 'description' => 'Trauung in der Moschee.', 'sort_order' => 3],
                ['time' => '14:00', 'title' => 'Familienessen', 'description' => 'Mittagessen für Familie und engste Gäste.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Festliches Abendessen & Feier', 'description' => 'Abendessen, Tanzen und Feier.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo Gast', 'email' => 'demo-islamsko-de@example.com'],
            ],
            isDemo: true,
            invitationLocale: 'de',
        );

        $this->seedEvent(
            slug: 'demo-krscansko-de',
            groom: 'Lukas',
            bride: 'Sophie',
            locationName: 'Frauenkirche',
            locationAddress: 'München, Deutschland',
            lat: 48.1386,
            lng: 11.5736,
            theme: InvitationTheme::RoyalWedding,
            schedule: [
                ['time' => '11:00', 'title' => 'Ankunft der Gäste', 'description' => 'Begrüßung und Platznehmen.', 'sort_order' => 1],
                ['time' => '12:00', 'title' => 'Kirchliche Trauung & Segen', 'description' => 'Kirchliche Zeremonie und Segen.', 'sort_order' => 2],
                ['time' => '14:00', 'title' => 'Empfang', 'description' => 'Sektempfang für Familie und engste Gäste.', 'sort_order' => 3],
                ['time' => '16:00', 'title' => 'Hochzeitsfotoshooting', 'description' => 'Gemeinsame Fotos mit den Brautleuten.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Festliches Abendessen & Feier', 'description' => 'Abendessen, Tanzen und Feier.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo Gast', 'email' => 'demo-krscansko-de@example.com'],
            ],
            isDemo: true,
            invitationLocale: 'de',
        );

        // Croatian public demos (Zagreb)
        $this->seedEvent(
            slug: 'demo-islamsko-hr',
            groom: 'Emir',
            bride: 'Lejla',
            locationName: 'Islamski centar Zagreb',
            locationAddress: 'Zagreb, Hrvatska',
            lat: 45.7910,
            lng: 15.9500,
            theme: InvitationTheme::AmberGold,
            schedule: [
                ['time' => '09:00', 'title' => 'Odlazak po mladu', 'description' => 'Mladoženja i svatovi dolaze po mladu.', 'sort_order' => 1],
                ['time' => '10:00', 'title' => 'Općinsko/matičarsko vjenčanje', 'description' => 'Potpisivanje u matičnom uredu.', 'sort_order' => 2],
                ['time' => '11:00', 'title' => 'Šerijatsko vjenčanje (nikah)', 'description' => 'Vjenčanje u džamiji.', 'sort_order' => 3],
                ['time' => '13:00', 'title' => 'Svečani ručak za goste', 'description' => 'Ručak za obitelj i najbliže goste.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Svečana večera i proslava', 'description' => 'Večera, ples i slavlje.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo gost', 'email' => 'demo-islamsko-hr@example.com'],
            ],
            isDemo: true,
            invitationLocale: 'hr',
            extraLocations: [
                [
                    'label' => 'Općina',
                    'name' => 'Gradski ured za opću upravu',
                    'address' => 'Zagreb, Hrvatska',
                    'lat' => 45.8120,
                    'lng' => 15.9780,
                    'is_primary' => false,
                    'sort_order' => 1,
                ],
            ],
            primaryLocationLabel: 'Džamija',
        );

        $this->seedEvent(
            slug: 'demo-krscansko-hr',
            groom: 'Ivan',
            bride: 'Lucija',
            locationName: 'Katedrala Uznesenja Blažene Djevice Marije',
            locationAddress: 'Zagreb, Hrvatska',
            lat: 45.8144,
            lng: 15.9780,
            theme: InvitationTheme::RoyalWedding,
            schedule: [
                ['time' => '10:00', 'title' => 'Odlazak po mladu', 'description' => 'Mladoženja i svatovi dolaze po mladu.', 'sort_order' => 1],
                ['time' => '11:30', 'title' => 'Vjenčanje u crkvi', 'description' => 'Crkvena ceremonija i blagoslov.', 'sort_order' => 2],
                ['time' => '13:30', 'title' => 'Svečani ručak za goste', 'description' => 'Ručak za obitelj i najbliže goste.', 'sort_order' => 3],
                ['time' => '16:00', 'title' => 'Fotografiranje', 'description' => 'Zajedničko fotografiranje mladenca.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Svečana večera i proslava', 'description' => 'Večera, ples i slavlje.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo gost', 'email' => 'demo-krscansko-hr@example.com'],
            ],
            isDemo: true,
            invitationLocale: 'hr',
        );

        // Serbian Latin public demos (Belgrade)
        $this->seedEvent(
            slug: 'demo-islamsko-sr',
            groom: 'Amar',
            bride: 'Emina',
            locationName: 'Bajrakli džamija',
            locationAddress: 'Beograd, Srbija',
            lat: 44.8220,
            lng: 20.4574,
            theme: InvitationTheme::AmberGold,
            schedule: [
                ['time' => '09:00', 'title' => 'Odlazak po mladu', 'description' => 'Mladoženja i svatovi dolaze po mladu.', 'sort_order' => 1],
                ['time' => '10:00', 'title' => 'Opštinsko/matičarsko venčanje', 'description' => 'Potpisivanje u matičnom uredu.', 'sort_order' => 2],
                ['time' => '11:00', 'title' => 'Šerijatsko venčanje (nikah)', 'description' => 'Venčanje u džamiji.', 'sort_order' => 3],
                ['time' => '13:00', 'title' => 'Svečani ručak za goste', 'description' => 'Ručak za porodicu i najbliže goste.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Svečana večera i proslava', 'description' => 'Večera, ples i slavlje.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo gost', 'email' => 'demo-islamsko-sr@example.com'],
            ],
            isDemo: true,
            invitationLocale: 'sr_Latn',
            extraLocations: [
                [
                    'label' => 'Opština',
                    'name' => 'Gradska uprava grada Beograda',
                    'address' => 'Beograd, Srbija',
                    'lat' => 44.8176,
                    'lng' => 20.4569,
                    'is_primary' => false,
                    'sort_order' => 1,
                ],
            ],
            primaryLocationLabel: 'Džamija',
        );

        $this->seedEvent(
            slug: 'demo-krscansko-sr',
            groom: 'Stefan',
            bride: 'Jovana',
            locationName: 'Hram Svetog Save',
            locationAddress: 'Beograd, Srbija',
            lat: 44.7981,
            lng: 20.4686,
            theme: InvitationTheme::RoyalWedding,
            schedule: [
                ['time' => '10:00', 'title' => 'Odlazak po mladu', 'description' => 'Mladoženja i svatovi dolaze po mladu.', 'sort_order' => 1],
                ['time' => '11:30', 'title' => 'Venčanje u crkvi', 'description' => 'Crkvena ceremonija i blagoslov.', 'sort_order' => 2],
                ['time' => '13:30', 'title' => 'Svečani ručak za goste', 'description' => 'Ručak za porodicu i najbliže goste.', 'sort_order' => 3],
                ['time' => '16:00', 'title' => 'Fotografisanje', 'description' => 'Zajedničko fotografisanje mladenca.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Svečana večera i proslava', 'description' => 'Večera, ples i slavlje.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo gost', 'email' => 'demo-krscansko-sr@example.com'],
            ],
            isDemo: true,
            invitationLocale: 'sr_Latn',
        );
    }

    /**
     * @param  list<array{label?: string|null, name: string, address?: string|null, lat?: float|null, lng?: float|null, is_primary?: bool, sort_order?: int}>  $extraLocations
     */
    private function seedEvent(
        string $slug,
        string $groom,
        string $bride,
        string $locationName,
        string $locationAddress,
        float $lat,
        float $lng,
        InvitationTheme $theme,
        array $schedule,
        array $guests = [],
        ?int $userId = null,
        bool $isDemo = false,
        array $extraLocations = [],
        ?string $primaryLocationLabel = null,
        ?string $invitationLocale = null,
    ): void {
        $event = WeddingEvent::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'user_id' => $userId,
                'is_demo' => $isDemo,
                'bride_name' => $bride,
                'groom_name' => $groom,
                'wedding_date' => now()->addMonths(4)->setTime(16, 0),
                'location_name' => $locationName,
                'location_address' => $locationAddress,
                'location_lat' => $lat,
                'location_lng' => $lng,
                'theme' => $theme,
                'link_mode' => LinkMode::Public,
                'music_url' => 'https://www.youtube.com/watch?v=450p7goxZqg',
                'hero_image' => self::HERO_IMAGE,
                'rsvp_deadline' => now()->addMonths(3),
                'accommodation_enabled' => true,
                'is_active' => true,
                'plan_tier' => PlanTier::Free,
                'guest_limit' => PlanTier::Free->guestLimit(),
                'invitation_locale' => $invitationLocale ?? config('app.default_locale', 'bs'),
            ]
        );

        app(EnsureWeddingMenuOptions::class)->handle($event);

        $event->locations()->delete();
        $event->locations()->create([
            'label' => $primaryLocationLabel,
            'name' => $locationName,
            'address' => $locationAddress,
            'lat' => $lat,
            'lng' => $lng,
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        foreach ($extraLocations as $location) {
            $event->locations()->create([
                'label' => $location['label'] ?? null,
                'name' => $location['name'],
                'address' => $location['address'] ?? null,
                'lat' => $location['lat'] ?? null,
                'lng' => $location['lng'] ?? null,
                'is_primary' => $location['is_primary'] ?? false,
                'sort_order' => $location['sort_order'] ?? 1,
            ]);
        }

        $event->scheduleItems()->delete();
        $event->scheduleItems()->createMany($schedule);

        foreach ($guests as $guestData) {
            Guest::query()->firstOrCreate(
                [
                    'wedding_event_id' => $event->id,
                    'email' => $guestData['email'],
                ],
                [
                    'name' => $guestData['name'],
                ]
            );
        }
    }
}
