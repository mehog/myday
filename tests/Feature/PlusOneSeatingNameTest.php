<?php

namespace Tests\Feature;

use App\Filament\App\Pages\SeatingPlan;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Livewire\Livewire;
use Tests\TestCase;

class PlusOneSeatingNameTest extends TestCase
{
    public function test_seating_plan_uses_formal_plus_one_name_when_set(): void
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
        ]);

        Guest::query()->create([
            'wedding_event_id' => $event->id,
            'name' => 'Hanuma Softić',
            'token' => 'plus-one-seating-token-123456789',
            'rsvp_status' => RsvpStatus::Yes,
            'plus_one_allowed' => true,
            'plus_one_name' => '💙Velid (ljubav jedina)💙',
            'plus_one_seating_name' => 'Velid Softić',
        ]);

        $this->actingAs($user);

        $guests = Livewire::test(SeatingPlan::class)
            ->instance()
            ->getGuests();

        $plusOne = $guests->firstWhere('is_plus_one', true);

        $this->assertNotNull($plusOne);
        $this->assertSame('Velid Softić (Hanuma Softić)', $plusOne['name']);
    }

    public function test_seating_plan_falls_back_to_guest_entered_plus_one_name(): void
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
        ]);

        Guest::query()->create([
            'wedding_event_id' => $event->id,
            'name' => 'Hanuma Softić',
            'token' => 'plus-one-fallback-token-12345678',
            'rsvp_status' => RsvpStatus::Yes,
            'plus_one_allowed' => true,
            'plus_one_name' => 'Velid',
            'plus_one_seating_name' => null,
        ]);

        $this->actingAs($user);

        $guests = Livewire::test(SeatingPlan::class)
            ->instance()
            ->getGuests();

        $plusOne = $guests->firstWhere('is_plus_one', true);

        $this->assertNotNull($plusOne);
        $this->assertSame('Velid (Hanuma Softić)', $plusOne['name']);
    }

    public function test_place_cards_use_formal_plus_one_name_when_set(): void
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->paid()->create();

        Guest::query()->create([
            'wedding_event_id' => $event->id,
            'name' => 'Ana Kovačević',
            'token' => 'place-card-formal-token-1234567',
            'rsvp_status' => RsvpStatus::Yes,
            'plus_one_allowed' => true,
            'plus_one_name' => '💙Velid (ljubav)💙',
            'plus_one_seating_name' => 'Velid Softić',
        ]);

        $response = $this->actingAs($user)
            ->get(route('guests.place-cards.download'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent() ?: '');
    }
}
