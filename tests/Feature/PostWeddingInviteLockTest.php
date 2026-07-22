<?php

namespace Tests\Feature;

use App\Filament\App\Resources\MyWeddingResource\Pages\EditMyWedding;
use App\LinkMode;
use App\Livewire\GuestContactPage;
use App\Livewire\InvitationPage;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class PostWeddingInviteLockTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_wedding_has_ended_after_calendar_day(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
        ]);

        $this->assertTrue($event->hasEnded());
    }

    public function test_wedding_has_not_ended_on_wedding_day(): void
    {
        $this->travelTo(now()->setTime(20, 0));

        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->setTime(16, 0),
        ]);

        $this->assertFalse($event->hasEnded());
    }

    public function test_accepts_rsvps_until_end_of_wedding_day(): void
    {
        $this->travelTo(now()->setTime(20, 0));

        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->setTime(16, 0),
            'rsvp_deadline' => now()->addWeek()->toDateString(),
        ]);

        $this->assertTrue($event->acceptsRsvps());
    }

    public function test_accepts_rsvps_false_after_rsvp_deadline(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth()->setTime(16, 0),
            'rsvp_deadline' => now()->subDay()->toDateString(),
        ]);

        $this->assertFalse($event->acceptsRsvps());
    }

    public function test_accepts_rsvps_false_after_wedding_day(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
        ]);

        $this->assertFalse($event->acceptsRsvps());
    }

    public function test_public_anonymous_rsvp_creates_guest_before_wedding(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth()->setTime(16, 0),
            'link_mode' => LinkMode::Public,
            'is_active' => true,
        ]);

        Livewire::test(InvitationPage::class, ['slug' => $event->slug])
            ->set('anonymousName', 'New Guest')
            ->call('respond', 'yes')
            ->assertSet('rsvpSubmitted', true);

        $this->assertDatabaseCount('guests', 1);
        $this->assertDatabaseHas('guests', ['name' => 'New Guest']);
    }

    public function test_public_anonymous_rsvp_blocked_after_wedding(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
            'link_mode' => LinkMode::Public,
            'is_active' => true,
        ]);

        Livewire::test(InvitationPage::class, ['slug' => $event->slug])
            ->set('anonymousName', 'Should Not Save')
            ->call('respond', 'yes')
            ->assertSet('rsvpSubmitted', false);

        $this->assertDatabaseCount('guests', 0);
    }

    public function test_personal_rsvp_blocked_after_rsvp_deadline(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth()->setTime(16, 0),
            'rsvp_deadline' => now()->subDay()->toDateString(),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create(['name' => 'Guest']);

        Livewire::test(InvitationPage::class, ['slug' => $event->slug, 'token' => $guest->token])
            ->call('respond', 'yes')
            ->assertSet('rsvpSubmitted', false);

        $this->assertNull($guest->fresh()->rsvp_status);
    }

    public function test_personal_rsvp_blocked_after_wedding(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create(['name' => 'Guest']);

        Livewire::test(InvitationPage::class, ['slug' => $event->slug, 'token' => $guest->token])
            ->call('respond', 'yes')
            ->assertSet('rsvpSubmitted', false);

        $this->assertNull($guest->fresh()->rsvp_status);
    }

    public function test_edit_rsvp_blocked_after_wedding(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create([
            'name' => 'Guest',
            'rsvp_status' => RsvpStatus::Yes,
            'rsvp_responded_at' => now()->subWeek(),
        ]);

        Livewire::test(InvitationPage::class, ['slug' => $event->slug, 'token' => $guest->token])
            ->call('editRsvp')
            ->assertSet('isEditing', false);
    }

    public function test_closed_rsvp_message_shown_after_wedding(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create([
            'name' => 'Guest',
            'rsvp_status' => RsvpStatus::Yes,
            'rsvp_responded_at' => now()->subWeek(),
        ]);

        $this->get(route('invitation.guest', ['slug' => $event->slug, 'token' => $guest->token]))
            ->assertOk()
            ->assertSee(__('invitation.rsvp_closed_after_wedding'), false);
    }

    public function test_personal_contact_page_reachable_after_wedding(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->subDay()->setTime(16, 0),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create(['name' => 'Guest']);

        Livewire::test(GuestContactPage::class, ['slug' => $event->slug, 'token' => $guest->token])
            ->assertOk();
    }

    public function test_admin_can_update_wedding_date_after_wedding(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
        ]);
        $newDate = now()->addMonths(3)->setTime(18, 0);

        $event->update(['wedding_date' => $newDate]);

        $this->assertEquals(
            $newDate->toDateTimeString(),
            $event->fresh()->wedding_date->toDateTimeString(),
        );
    }

    public function test_couple_cannot_update_wedding_date_after_wedding(): void
    {
        $owner = User::factory()->create();
        $originalDate = now()->subDays(2)->setTime(16, 0);
        $event = WeddingEvent::factory()->for($owner)->create([
            'wedding_date' => $originalDate,
            'is_active' => true,
        ]);
        $newDate = now()->addMonths(3)->format('Y-m-d H:i:s');

        $this->actingAs($owner);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(EditMyWedding::class, ['record' => $event->getKey()])
            ->fillForm([
                'groom_name' => $event->groom_name,
                'bride_name' => $event->bride_name,
                'wedding_date' => $newDate,
                'theme' => $event->theme->value,
                'template' => $event->template->value,
                'link_mode' => $event->link_mode->value,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(
            $originalDate->toDateTimeString(),
            $event->fresh()->wedding_date->toDateTimeString(),
        );
    }
}
