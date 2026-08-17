<?php

namespace Tests\Feature\Dashboard;

use App\InvitePlatform;
use App\Livewire\Dashboard\Guests as DashboardGuests;
use App\Livewire\Dashboard\Locations;
use App\Livewire\Dashboard\Menus;
use App\Livewire\Dashboard\Schedule;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Models\WeddingMenuOption;
use App\PlatformMenu;
use App\RsvpStatus;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardRelationParityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_locations_create_sets_primary_and_legacy(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $wedding = WeddingEvent::factory()->create(['user_id' => $user->id]);
        $wedding->locations()->delete();

        Livewire::actingAs($user)
            ->test(Locations::class)
            ->call('openCreate')
            ->set('name', 'Town Hall')
            ->set('address', 'Main St 1')
            ->set('is_primary', true)
            ->set('sort_order', '1')
            ->call('save')
            ->assertHasNoErrors();

        $wedding->refresh();
        $this->assertDatabaseHas('wedding_locations', [
            'wedding_event_id' => $wedding->id,
            'name' => 'Town Hall',
            'is_primary' => true,
        ]);
        $this->assertSame('Town Hall', $wedding->location_name);
    }

    public function test_menus_cannot_delete_platform_option(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $wedding = WeddingEvent::factory()->create(['user_id' => $user->id]);

        $platform = $wedding->menuOptions()
            ->where('platform_key', PlatformMenu::Regular->value)
            ->first()
            ?? WeddingMenuOption::query()->create([
                'wedding_event_id' => $wedding->id,
                'platform_key' => PlatformMenu::Regular,
                'label' => null,
                'is_visible' => true,
                'sort_order' => 1,
            ]);

        Livewire::actingAs($user)
            ->test(Menus::class)
            ->call('delete', $platform->id)
            ->assertSet('flashError', __('menu.cannot_delete_platform'));

        $this->assertDatabaseHas('wedding_menu_options', ['id' => $platform->id]);
    }

    public function test_menus_can_create_custom_option(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        WeddingEvent::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(Menus::class)
            ->call('openCreate')
            ->set('label', 'Halal')
            ->set('is_visible', true)
            ->set('sort_order', '10')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wedding_menu_options', [
            'label' => 'Halal',
            'platform_key' => null,
        ]);
    }

    public function test_schedule_create_and_delete(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $wedding = WeddingEvent::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(Schedule::class)
            ->call('openCreate')
            ->set('time', '16:00')
            ->set('title', 'Ceremony')
            ->set('sort_order', '0')
            ->call('save')
            ->assertHasNoErrors();

        $item = $wedding->scheduleItems()->first();
        $this->assertNotNull($item);

        Livewire::actingAs($user)
            ->test(Schedule::class)
            ->call('delete', $item->id);

        $this->assertDatabaseMissing('schedule_items', ['id' => $item->id]);
    }

    public function test_guest_create_full_form_and_send_invite_sets_platform(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $wedding = WeddingEvent::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(DashboardGuests::class)
            ->call('openCreate')
            ->set('name', 'Ana Test')
            ->set('email', 'ana@example.com')
            ->set('phone', '+38761111222')
            ->set('plus_one_allowed', true)
            ->set('labels', ['family'])
            ->call('saveGuest')
            ->assertHasNoErrors();

        $guest = Guest::query()->where('wedding_event_id', $wedding->id)->where('name', 'Ana Test')->first();
        $this->assertNotNull($guest);
        $this->assertTrue((bool) $guest->plus_one_allowed);
        $this->assertSame('ana@example.com', $guest->email);

        Livewire::actingAs($user)
            ->test(DashboardGuests::class)
            ->assertDontSee(__('dashboard.guests_trash'), false)
            ->assertSee(__('guests.filter_labels_all'), false)
            ->call('openSendInvite', $guest->id)
            ->call('sendVia', InvitePlatform::WhatsApp->value)
            ->assertSet('flashMessage', __('guests.guest_marked_sent'));

        $guest->refresh();
        $this->assertNotNull($guest->invite_sent_at);
        $this->assertSame(InvitePlatform::WhatsApp, $guest->invite_platform);
    }

    public function test_guest_mark_rsvp_with_menus_and_restore_trashed(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $wedding = WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'accommodation_enabled' => true,
        ]);

        $menu = $wedding->menuOptions()->first()
            ?? WeddingMenuOption::query()->create([
                'wedding_event_id' => $wedding->id,
                'platform_key' => null,
                'label' => 'Regular',
                'is_visible' => true,
                'sort_order' => 1,
            ]);

        $guest = Guest::factory()->create([
            'wedding_event_id' => $wedding->id,
            'plus_one_allowed' => true,
        ]);

        Livewire::actingAs($user)
            ->test(DashboardGuests::class)
            ->call('openMarkRsvp', $guest->id)
            ->set('rsvp_status', RsvpStatus::Yes->value)
            ->set('plus_one_name', 'Marko')
            ->set('menu_option_id', $menu->id)
            ->set('plus_one_menu_option_id', $menu->id)
            ->set('accommodation_count', '2')
            ->call('saveMarkRsvp')
            ->assertHasNoErrors();

        $guest->refresh();
        $this->assertSame(RsvpStatus::Yes, $guest->rsvp_status);
        $this->assertSame('Marko', $guest->plus_one_name);
        $this->assertSame($menu->id, $guest->menu_option_id);
        $this->assertSame(2, $guest->accommodation_count);
        $this->assertTrue($guest->rsvp_manual_override);

        Livewire::actingAs($user)
            ->test(DashboardGuests::class)
            ->call('deleteGuest', $guest->id);

        $this->assertSoftDeleted('guests', ['id' => $guest->id]);

        Livewire::actingAs($user)
            ->test(DashboardGuests::class)
            ->call('restoreGuest', $guest->id);

        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'deleted_at' => null,
        ]);
    }

    public function test_place_cards_requires_feature(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $wedding = WeddingEvent::factory()->free()->create(['user_id' => $user->id]);
        Guest::factory()->create([
            'wedding_event_id' => $wedding->id,
            'rsvp_status' => RsvpStatus::Yes,
        ]);

        Livewire::actingAs($user)
            ->test(DashboardGuests::class)
            ->call('openPlaceCards')
            ->assertDispatched('open-upgrade-modal');
    }
}
