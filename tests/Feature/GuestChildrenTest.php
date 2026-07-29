<?php

namespace Tests\Feature;

use App\Filament\App\Pages\SeatingPlan;
use App\Livewire\InvitationPage;
use App\Models\Guest;
use App\Models\GuestChild;
use App\Models\User;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use App\Services\SyncGuestChildren;
use Livewire\Livewire;
use Tests\TestCase;

class GuestChildrenTest extends TestCase
{
    public function test_guest_can_submit_children_with_rsvp_yes(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
        ]);
        $event->menuOptions()->update(['is_visible' => false]);
        $guest = Guest::factory()->for($event)->create([
            'plus_one_allowed' => false,
        ]);

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('childNames', ['Ena Softić', 'Luka Softić', ''])
            ->call('respond', 'yes')
            ->assertHasNoErrors();

        $guest->refresh();

        $this->assertSame(RsvpStatus::Yes, $guest->rsvp_status);
        $this->assertSame(
            ['Ena Softić', 'Luka Softić'],
            $guest->children()->pluck('name')->all(),
        );
    }

    public function test_editing_children_preserves_ids_and_formal_names(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
        ]);
        $event->menuOptions()->update(['is_visible' => false]);
        $guest = Guest::factory()->for($event)->create([
            'rsvp_status' => RsvpStatus::Yes,
        ]);

        $first = $guest->children()->create([
            'name' => 'Ena',
            'seating_name' => 'Ena Softić',
            'sort_order' => 0,
        ]);
        $second = $guest->children()->create([
            'name' => 'Luka',
            'seating_name' => null,
            'sort_order' => 1,
        ]);

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->call('editRsvp')
            ->set('childNames', ['Ena Updated', 'Luka'])
            ->call('respond', 'yes')
            ->assertHasNoErrors();

        $first->refresh();
        $second->refresh();

        $this->assertSame('Ena Updated', $first->name);
        $this->assertSame('Ena Softić', $first->seating_name);
        $this->assertSame('Luka', $second->name);
        $this->assertSame(2, $guest->children()->count());
    }

    public function test_rsvp_no_clears_children(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create([
            'rsvp_status' => RsvpStatus::Yes,
        ]);
        $guest->children()->create([
            'name' => 'Ena',
            'sort_order' => 0,
        ]);

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->call('respond', 'no')
            ->assertHasNoErrors();

        $this->assertSame(0, $guest->children()->count());
    }

    public function test_admin_sync_can_override_formal_names_and_remove_children(): void
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create();
        $guest = Guest::factory()->for($event)->create([
            'rsvp_status' => RsvpStatus::Yes,
        ]);

        $child = $guest->children()->create([
            'name' => 'Ena',
            'sort_order' => 0,
        ]);

        app(SyncGuestChildren::class)->syncFromAdmin($guest, [
            [
                'id' => $child->id,
                'name' => 'Ena Softić',
                'seating_name' => 'Ena Softić',
            ],
            [
                'name' => 'Luka Softić',
                'seating_name' => null,
            ],
        ]);

        $guest->refresh();
        $child->refresh();

        $this->assertSame('Ena Softić', $child->name);
        $this->assertSame('Ena Softić', $child->seating_name);
        $this->assertSame(2, $guest->children()->count());

        app(SyncGuestChildren::class)->syncFromAdmin($guest, [
            [
                'id' => $child->id,
                'name' => 'Ena Softić',
                'seating_name' => 'Ena Softić',
            ],
        ]);

        $this->assertSame(1, $guest->children()->count());
        $this->assertTrue($guest->children()->whereKey($child->id)->exists());
    }

    public function test_removing_child_clears_seating_assignment(): void
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create();
        $guest = Guest::factory()->for($event)->create([
            'rsvp_status' => RsvpStatus::Yes,
        ]);
        $child = $guest->children()->create([
            'name' => 'Ena',
            'sort_order' => 0,
        ]);

        $event->update([
            'seating_plan' => [
                'tables' => [
                    [
                        'id' => 't_1',
                        'label' => 'Sto 1',
                        'chair_count' => 2,
                        'seats' => [$guest->id, $child->seatingAssigneeKey()],
                    ],
                ],
            ],
        ]);

        app(SyncGuestChildren::class)->syncFromNames($guest, []);

        $event->refresh();

        $this->assertSame(
            [$guest->id, null],
            $event->seating_plan['tables'][0]['seats'],
        );
        $this->assertSame(0, $guest->children()->count());
    }

    public function test_seating_plan_includes_children_with_stable_ids(): void
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
        ]);
        $guest = Guest::factory()->for($event)->create([
            'name' => 'Hanuma Softić',
            'rsvp_status' => RsvpStatus::Yes,
        ]);
        $child = $guest->children()->create([
            'name' => 'Ena',
            'seating_name' => 'Ena Softić',
            'sort_order' => 0,
        ]);

        /** @var User $user */
        $this->actingAs($user);

        $guests = Livewire::test(SeatingPlan::class)
            ->instance()
            ->getGuests();

        $childEntry = $guests->firstWhere('is_child', true);

        $this->assertNotNull($childEntry);
        $this->assertSame($child->seatingAssigneeKey(), $childEntry['id']);
        $this->assertSame('Ena Softić (Hanuma Softić)', $childEntry['name']);
    }

    public function test_place_cards_download_succeeds_with_children(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create();
        $guest = Guest::factory()->for($event)->create([
            'name' => 'Ana Kovačević',
            'rsvp_status' => RsvpStatus::Yes,
            'plus_one_name' => 'Marko Kovač',
        ]);
        $guest->children()->create([
            'name' => 'Ena',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($user)
            ->get(route('guests.place-cards.download'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent() ?: '');
    }

    public function test_child_display_name_prefers_formal_override(): void
    {
        $child = new GuestChild([
            'name' => 'Ena',
            'seating_name' => 'Ena Softić',
        ]);
        $child->id = 12;

        $this->assertSame('Ena Softić', $child->displayName());
        $this->assertSame('child:12', $child->seatingAssigneeKey());
        $this->assertNull(GuestChild::idFromSeatingAssigneeKey('guest:1'));
        $this->assertSame(12, GuestChild::idFromSeatingAssigneeKey('child:12'));

        $fallback = new GuestChild([
            'name' => 'Ena',
            'seating_name' => null,
        ]);

        $this->assertSame('Ena', $fallback->displayName());
    }
}
