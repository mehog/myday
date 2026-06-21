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
        User::query()->firstOrCreate(
            ['email' => 'admin@myday.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        $event = WeddingEvent::query()->updateOrCreate(
            ['slug' => 'milan-anja'],
            [
                'bride_name' => 'Anja',
                'groom_name' => 'Milan',
                'wedding_date' => now()->addMonths(4)->setTime(16, 0),
                'location_name' => 'Crkva Svetog Marka',
                'location_address' => 'Beograd, Srbija',
                'location_lat' => 44.8176,
                'location_lng' => 20.4633,
                'theme' => InvitationTheme::AmberGold,
                'link_mode' => LinkMode::Public,
                'music_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
                'rsvp_deadline' => now()->addMonths(3),
                'is_active' => true,
            ]
        );

        $event->scheduleItems()->delete();
        $event->scheduleItems()->createMany([
            ['time' => '14:00', 'title' => 'Dolazak gostiju', 'description' => 'Dobrodošlica i piće dobrodošlice.', 'sort_order' => 1],
            ['time' => '15:00', 'title' => 'Vjenčanje', 'description' => 'Ceremonija i poljubac mira.', 'sort_order' => 2],
            ['time' => '17:00', 'title' => 'Svečanost', 'description' => 'Večera, ples i slavlje.', 'sort_order' => 3],
        ]);

        $guests = [
            ['name' => 'Marko Jovic', 'email' => 'marko@example.com'],
            ['name' => 'Jelena Petrovic', 'email' => 'jelena@example.com'],
            ['name' => 'Stefan Nikolic', 'email' => 'stefan@example.com'],
        ];

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
