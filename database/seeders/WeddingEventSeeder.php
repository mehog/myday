<?php

namespace Database\Seeders;

use App\InvitationTheme;
use App\LinkMode;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WeddingEventSeeder extends Seeder
{
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
                ['time' => '08:00', 'title' => 'Odlazak po mladu', 'description' => 'Mlađak i svatovi dolaze po mladu.', 'sort_order' => 1],
                ['time' => '09:00', 'title' => 'Općinsko/matičarsko vjenčanje', 'description' => 'Potpisivanje u matičnom uredu.', 'sort_order' => 2],
                ['time' => '10:00', 'title' => 'Šerijatsko vjenčanje (nikah)', 'description' => 'Vjenčanje u džamiji.', 'sort_order' => 3],
                ['time' => '12:00', 'title' => 'Svečani ručak za uže gosti', 'description' => 'Ručak za porodicu i najbliže goste.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Svečana večera i proslava', 'description' => 'Večera, ples i slavlje.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo Gost', 'email' => 'demo-islamsko@example.com'],
            ],
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
                ['time' => '09:00', 'title' => 'Odlazak po mladu', 'description' => 'Mlađak i svatovi dolaze po mladu.', 'sort_order' => 1],
                ['time' => '10:00', 'title' => 'Vjenčanje u crkvi', 'description' => 'Crkvena ceremonija i blagoslov.', 'sort_order' => 2],
                ['time' => '12:00', 'title' => 'Svečani ručak za uže gosti', 'description' => 'Ručak za porodicu i najbliže goste.', 'sort_order' => 3],
                ['time' => '15:00', 'title' => 'Fotografisanje', 'description' => 'Zajedničko fotografisanje mladenaca.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Svečana večera i proslava', 'description' => 'Večera, ples i slavlje.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo Gost', 'email' => 'demo-krscansko@example.com'],
            ],
        );

        $this->seedEvent(
            slug: 'demo-islamsko-en',
            groom: 'Amer',
            bride: 'Amina',
            locationName: 'Gazi Husrev-beg Mosque',
            locationAddress: 'Sarajevo, Bosnia and Herzegovina',
            lat: 43.8594,
            lng: 18.4286,
            theme: InvitationTheme::AmberGold,
            schedule: [
                ['time' => '08:00', 'title' => 'Procession to the bride\'s home', 'description' => 'The groom and wedding party arrive to collect the bride.', 'sort_order' => 1],
                ['time' => '09:00', 'title' => 'Civil marriage ceremony', 'description' => 'Signing at the registry office.', 'sort_order' => 2],
                ['time' => '10:00', 'title' => 'Islamic marriage (nikah)', 'description' => 'Wedding ceremony at the mosque.', 'sort_order' => 3],
                ['time' => '12:00', 'title' => 'Family luncheon', 'description' => 'Lunch for family and closest guests.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Wedding dinner & celebration', 'description' => 'Dinner, dancing and celebration.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo Guest', 'email' => 'demo-islamsko-en@example.com'],
            ],
        );

        $this->seedEvent(
            slug: 'demo-krscansko-en',
            groom: 'Milan',
            bride: 'Ana',
            locationName: 'Sacred Heart Cathedral',
            locationAddress: 'Sarajevo, Bosnia and Herzegovina',
            lat: 43.8563,
            lng: 18.4131,
            theme: InvitationTheme::RoyalWedding,
            schedule: [
                ['time' => '09:00', 'title' => 'Procession to the bride\'s home', 'description' => 'The groom and wedding party arrive to collect the bride.', 'sort_order' => 1],
                ['time' => '10:00', 'title' => 'Church ceremony & blessing', 'description' => 'Church ceremony and blessing.', 'sort_order' => 2],
                ['time' => '12:00', 'title' => 'Family luncheon', 'description' => 'Lunch for family and closest guests.', 'sort_order' => 3],
                ['time' => '15:00', 'title' => 'Wedding photo session', 'description' => 'Group photos with the newlyweds.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Wedding dinner & celebration', 'description' => 'Dinner, dancing and celebration.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo Guest', 'email' => 'demo-krscansko-en@example.com'],
            ],
        );

        $this->seedEvent(
            slug: 'demo-islamsko-de',
            groom: 'Amer',
            bride: 'Amina',
            locationName: 'Gazi Husrev-beg-Moschee',
            locationAddress: 'Sarajevo, Bosnien und Herzegowina',
            lat: 43.8594,
            lng: 18.4286,
            theme: InvitationTheme::AmberGold,
            schedule: [
                ['time' => '08:00', 'title' => 'Abholung der Braut', 'description' => 'Der Bräutigam und die Hochzeitsgesellschaft holen die Braut ab.', 'sort_order' => 1],
                ['time' => '09:00', 'title' => 'Standesamtliche Trauung', 'description' => 'Unterschrift beim Standesamt.', 'sort_order' => 2],
                ['time' => '10:00', 'title' => 'Islamische Trauung (Nikah)', 'description' => 'Trauung in der Moschee.', 'sort_order' => 3],
                ['time' => '12:00', 'title' => 'Familienessen', 'description' => 'Mittagessen für Familie und engste Gäste.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Festliches Abendessen & Feier', 'description' => 'Abendessen, Tanzen und Feier.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo Gast', 'email' => 'demo-islamsko-de@example.com'],
            ],
        );

        $this->seedEvent(
            slug: 'demo-krscansko-de',
            groom: 'Milan',
            bride: 'Ana',
            locationName: 'Kathedrale zum Heiligsten Herzen Jesu',
            locationAddress: 'Sarajevo, Bosnien und Herzegowina',
            lat: 43.8563,
            lng: 18.4131,
            theme: InvitationTheme::RoyalWedding,
            schedule: [
                ['time' => '09:00', 'title' => 'Abholung der Braut', 'description' => 'Der Bräutigam und die Hochzeitsgesellschaft holen die Braut ab.', 'sort_order' => 1],
                ['time' => '10:00', 'title' => 'Kirchliche Trauung & Segen', 'description' => 'Kirchliche Zeremonie und Segen.', 'sort_order' => 2],
                ['time' => '12:00', 'title' => 'Familienessen', 'description' => 'Mittagessen für Familie und engste Gäste.', 'sort_order' => 3],
                ['time' => '15:00', 'title' => 'Hochzeitsfotoshooting', 'description' => 'Gemeinsame Fotos mit den Brautleuten.', 'sort_order' => 4],
                ['time' => '19:00', 'title' => 'Festliches Abendessen & Feier', 'description' => 'Abendessen, Tanzen und Feier.', 'sort_order' => 5],
            ],
            guests: [
                ['name' => 'Demo Gast', 'email' => 'demo-krscansko-de@example.com'],
            ],
        );
    }

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
    ): void {
        $event = WeddingEvent::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'user_id' => $userId,
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
                'rsvp_deadline' => now()->addMonths(3),
                'is_active' => true,
            ]
        );

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
