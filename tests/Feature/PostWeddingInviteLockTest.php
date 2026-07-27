<?php

namespace Tests\Feature;

use App\Exceptions\WeddingArchivedException;
use App\Filament\App\Resources\MyWeddingResource\Pages\EditMyWedding;
use App\Filament\Imports\GuestImporter;
use App\LinkMode;
use App\Livewire\GuestContactPage;
use App\Livewire\InvitationPage;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Filament\Facades\Filament;
use Livewire\Livewire;
use RuntimeException;
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
            ->assertSee(__('invitation.rsvp_closed_after_wedding'), false)
            ->assertSee(__('invitation.share_photos_and_messages'), false)
            ->assertSee(route('invitation.contact.guest', [$event->slug, $guest->token]), false)
            ->assertDontSee(__('invitation.add_to_calendar'), false);
    }

    public function test_contact_link_shown_after_wedding_for_guests_without_yes_rsvp(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create([
            'name' => 'Guest',
            'rsvp_status' => RsvpStatus::No,
            'rsvp_responded_at' => now()->subWeek(),
        ]);
        $pendingGuest = Guest::factory()->for($event)->create([
            'name' => 'Pending Guest',
            'rsvp_status' => null,
        ]);

        $this->get(route('invitation.guest', ['slug' => $event->slug, 'token' => $guest->token]))
            ->assertOk()
            ->assertSee(__('invitation.share_photos_and_messages'), false)
            ->assertSee(route('invitation.contact.guest', [$event->slug, $guest->token]), false);

        $this->get(route('invitation.guest', ['slug' => $event->slug, 'token' => $pendingGuest->token]))
            ->assertOk()
            ->assertSee(__('invitation.share_photos_and_messages'), false)
            ->assertSee(route('invitation.contact.guest', [$event->slug, $pendingGuest->token]), false);
    }

    public function test_personal_contact_page_reachable_after_wedding(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->subDay()->setTime(16, 0),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create(['name' => 'Guest']);

        $html = Livewire::test(GuestContactPage::class, ['slug' => $event->slug, 'token' => $guest->token])
            ->assertOk()
            ->html();

        $this->assertTrue($event->acceptsGuestPhotos());
        $this->assertLessThan(
            strpos($html, __('invitation.send_text_message')),
            strpos($html, __('invitation.send_photos')),
            'Photo section should appear above text messages after the wedding.',
        );
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

    public function test_couple_cannot_rename_couple_or_change_invitation_after_wedding(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->create([
            'groom_name' => 'Original Groom',
            'bride_name' => 'Original Bride',
            'location_name' => 'Original Venue',
            'motto' => 'Original motto',
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
            'is_active' => true,
        ]);

        $this->actingAs($owner);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(EditMyWedding::class, ['record' => $event->getKey()])
            ->assertFormFieldIsDisabled('groom_name')
            ->assertFormFieldIsDisabled('bride_name')
            ->assertFormFieldIsDisabled('theme')
            ->assertFormFieldIsDisabled('location_name')
            ->fillForm([
                'groom_name' => 'New Groom',
                'bride_name' => 'New Bride',
                'location_name' => 'New Venue',
                'motto' => 'New motto',
                'theme' => $event->theme->value,
                'template' => $event->template->value,
                'link_mode' => $event->link_mode->value,
                'wedding_date' => $event->wedding_date->format('Y-m-d H:i:s'),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $event->fresh();

        $this->assertSame('Original Groom', $fresh->groom_name);
        $this->assertSame('Original Bride', $fresh->bride_name);
        $this->assertSame('Original Venue', $fresh->location_name);
        $this->assertSame('Original motto', $fresh->motto);
    }

    public function test_couple_can_edit_invitation_on_wedding_day(): void
    {
        $this->travelTo(now()->setTime(20, 0));

        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->create([
            'groom_name' => 'Original Groom',
            'wedding_date' => now()->setTime(16, 0),
            'is_active' => true,
        ]);

        $this->actingAs($owner);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(EditMyWedding::class, ['record' => $event->getKey()])
            ->assertFormFieldIsEnabled('groom_name')
            ->fillForm([
                'groom_name' => 'Updated Groom',
                'bride_name' => $event->bride_name,
                'theme' => $event->theme->value,
                'template' => $event->template->value,
                'link_mode' => $event->link_mode->value,
                'wedding_date' => $event->wedding_date->format('Y-m-d H:i:s'),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Updated Groom', $event->fresh()->groom_name);
    }

    public function test_couple_cannot_create_or_mutate_guests_after_wedding(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create(['name' => 'Keep Me']);

        $this->actingAs($owner);

        $this->assertTrue($event->isCoupleMutationLocked($owner));

        try {
            Guest::factory()->for($event)->create(['name' => 'Should Fail']);
            $this->fail('Expected WeddingArchivedException when creating a guest after the wedding.');
        } catch (WeddingArchivedException) {
            // expected
        }

        try {
            $guest->update(['name' => 'Renamed']);
            $this->fail('Expected WeddingArchivedException when updating a guest after the wedding.');
        } catch (WeddingArchivedException) {
            // expected
        }

        try {
            $guest->delete();
            $this->fail('Expected WeddingArchivedException when deleting a guest after the wedding.');
        } catch (WeddingArchivedException) {
            // expected
        }

        $this->assertSame('Keep Me', $guest->fresh()->name);
        $this->assertNull($guest->fresh()->deleted_at);
        $this->assertDatabaseMissing('guests', ['name' => 'Should Fail']);
    }

    public function test_couple_cannot_restore_or_import_guests_after_wedding(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
            'is_active' => true,
        ]);

        $deleted = Guest::factory()->for($event)->create(['name' => 'Soft Deleted']);
        // Soft-delete without couple auth so setup can create the archived state.
        auth()->logout();
        $deleted->delete();

        $this->actingAs($owner);

        try {
            $deleted->restore();
            $this->fail('Expected WeddingArchivedException when restoring a guest after the wedding.');
        } catch (WeddingArchivedException) {
            // expected
        }

        $this->assertSoftDeleted($deleted);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(__('app.wedding_archived_guest_lock'));

        GuestImporter::importFromContents($event, "name,email\nAlice,a@example.com\n");
    }

    public function test_admin_can_mutate_guests_after_wedding(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $guest = Guest::factory()->for($event)->create(['name' => 'Admin Guest']);
        $guest->update(['name' => 'Admin Renamed']);
        $guest->delete();

        $this->assertSoftDeleted($guest);
        $guest->restore();
        $this->assertNotSoftDeleted($guest);
        $this->assertSame('Admin Renamed', $guest->fresh()->name);
    }

    public function test_unauthenticated_code_can_still_seed_guests_for_archived_weddings(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
        ]);

        $guest = Guest::factory()->for($event)->create(['name' => 'Seed Guest']);

        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'name' => 'Seed Guest',
        ]);
    }
}
