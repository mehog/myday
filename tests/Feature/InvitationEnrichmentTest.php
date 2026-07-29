<?php

namespace Tests\Feature;

use App\InvitationTemplate;
use App\Livewire\InvitationPage;
use App\Models\Guest;
use App\Models\WeddingEvent;
use App\Models\WeddingLocation;
use App\Models\WeddingMenuOption;
use App\PlatformMenu;
use App\RsvpStatus;
use Livewire\Livewire;
use Tests\TestCase;

class InvitationEnrichmentTest extends TestCase
{
    public function test_new_wedding_gets_platform_menu_options(): void
    {
        $event = WeddingEvent::factory()->create();

        $keys = $event->menuOptions()->orderBy('sort_order')->pluck('platform_key')->all();

        $this->assertSame([
            PlatformMenu::Regular->value,
            PlatformMenu::GlutenFree->value,
            PlatformMenu::Vegetarian->value,
        ], array_map(
            fn ($key) => $key instanceof PlatformMenu ? $key->value : $key,
            $keys,
        ));
    }

    public function test_legacy_location_is_available_as_primary_location(): void
    {
        $event = WeddingEvent::factory()->create([
            'location_name' => 'Crkva Svetog Marka',
            'location_address' => 'Beograd',
            'location_lat' => 44.8176,
            'location_lng' => 20.4633,
        ]);

        $primary = $event->primaryLocation();

        $this->assertInstanceOf(WeddingLocation::class, $primary);
        $this->assertTrue($primary->is_primary);
        $this->assertSame('Crkva Svetog Marka', $primary->name);
        $this->assertSame('Crkva Svetog Marka', $event->primaryLocationName());
    }

    public function test_guest_must_choose_menu_for_each_attendee(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
        ]);
        $regular = $event->menuOptions()->where('platform_key', PlatformMenu::Regular)->firstOrFail();
        $vegetarian = $event->menuOptions()->where('platform_key', PlatformMenu::Vegetarian)->firstOrFail();
        $guest = Guest::factory()->for($event)->create([
            'plus_one_allowed' => true,
        ]);

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('plusOneName', 'Ana')
            ->set('childNames', ['Ena'])
            ->set('childMenuOptionIds', [null])
            ->call('respond', 'yes')
            ->assertHasErrors(['menuOptionId', 'plusOneMenuOptionId', 'childMenuOptionIds.0']);

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('plusOneName', 'Ana')
            ->set('childNames', ['Ena'])
            ->set('menuOptionId', (string) $regular->id)
            ->set('plusOneMenuOptionId', (string) $vegetarian->id)
            ->set('childMenuOptionIds', [(string) $regular->id])
            ->call('respond', 'yes')
            ->assertHasNoErrors();

        $guest->refresh();

        $this->assertSame(RsvpStatus::Yes, $guest->rsvp_status);
        $this->assertSame($regular->id, $guest->menu_option_id);
        $this->assertSame($vegetarian->id, $guest->plus_one_menu_option_id);
        $this->assertSame($regular->id, $guest->children()->first()?->menu_option_id);
    }

    public function test_hidden_and_foreign_menu_options_are_rejected(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
        ]);
        $otherEvent = WeddingEvent::factory()->create();
        $hidden = $event->menuOptions()->where('platform_key', PlatformMenu::Regular)->firstOrFail();
        $hidden->update(['is_visible' => false]);
        $foreign = $otherEvent->menuOptions()->where('platform_key', PlatformMenu::Vegetarian)->firstOrFail();
        $guest = Guest::factory()->for($event)->create();

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('menuOptionId', (string) $hidden->id)
            ->call('respond', 'yes')
            ->assertHasErrors(['menuOptionId']);

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('menuOptionId', (string) $foreign->id)
            ->call('respond', 'yes')
            ->assertHasErrors(['menuOptionId']);
    }

    public function test_custom_menu_option_can_be_selected(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
        ]);
        $custom = WeddingMenuOption::query()->create([
            'wedding_event_id' => $event->id,
            'platform_key' => null,
            'label' => 'Halal special',
            'is_visible' => true,
            'sort_order' => 10,
        ]);
        $guest = Guest::factory()->for($event)->create();

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('menuOptionId', (string) $custom->id)
            ->call('respond', 'yes')
            ->assertHasNoErrors();

        $this->assertSame($custom->id, $guest->fresh()->menu_option_id);
    }

    public function test_rsvp_no_clears_menu_and_accommodation(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
            'accommodation_enabled' => true,
        ]);
        $menu = $event->menuOptions()->firstOrFail();
        $guest = Guest::factory()->for($event)->create([
            'rsvp_status' => RsvpStatus::Yes,
            'menu_option_id' => $menu->id,
            'accommodation_count' => 2,
        ]);

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->call('respond', 'no')
            ->assertHasNoErrors();

        $guest->refresh();

        $this->assertSame(RsvpStatus::No, $guest->rsvp_status);
        $this->assertNull($guest->menu_option_id);
        $this->assertNull($guest->accommodation_count);
    }

    public function test_accommodation_is_optional_and_count_is_bounded(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
            'accommodation_enabled' => true,
        ]);
        $menu = $event->menuOptions()->firstOrFail();
        $guest = Guest::factory()->for($event)->create([
            'plus_one_allowed' => true,
        ]);

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('menuOptionId', (string) $menu->id)
            ->set('needsAccommodation', true)
            ->call('respond', 'yes')
            ->assertHasErrors(['accommodationCount']);

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('plusOneName', 'Ana')
            ->set('menuOptionId', (string) $menu->id)
            ->set('plusOneMenuOptionId', (string) $menu->id)
            ->set('needsAccommodation', true)
            ->set('accommodationCount', 5)
            ->call('respond', 'yes')
            ->assertHasErrors(['accommodationCount']);

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('plusOneName', 'Ana')
            ->set('menuOptionId', (string) $menu->id)
            ->set('plusOneMenuOptionId', (string) $menu->id)
            ->set('needsAccommodation', true)
            ->set('accommodationCount', 2)
            ->call('respond', 'yes')
            ->assertHasNoErrors();

        $this->assertSame(2, $guest->fresh()->accommodation_count);
    }

    public function test_accommodation_hidden_when_feature_disabled(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
            'accommodation_enabled' => false,
        ]);
        $menu = $event->menuOptions()->firstOrFail();
        $guest = Guest::factory()->for($event)->create();

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->assertDontSee(__('invitation.accommodation_question'))
            ->set('menuOptionId', (string) $menu->id)
            ->set('needsAccommodation', true)
            ->set('accommodationCount', 1)
            ->call('respond', 'yes')
            ->assertHasNoErrors();

        $this->assertNull($guest->fresh()->accommodation_count);
    }

    public function test_anonymous_rsvp_collects_primary_menu_only(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
            'accommodation_enabled' => true,
        ]);
        $menu = $event->menuOptions()->firstOrFail();

        Livewire::test(InvitationPage::class, ['slug' => $event->slug])
            ->set('anonymousName', 'Public Guest')
            ->set('menuOptionId', (string) $menu->id)
            ->set('needsAccommodation', true)
            ->set('accommodationCount', 1)
            ->call('respond', 'yes')
            ->assertHasNoErrors()
            ->assertSet('rsvpSubmitted', true);

        $guest = $event->guests()->first();

        $this->assertNotNull($guest);
        $this->assertSame($menu->id, $guest->menu_option_id);
        $this->assertSame(1, $guest->accommodation_count);
    }

    public function test_multiple_locations_render_on_classic_invitation(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
            'template' => InvitationTemplate::Classic,
            'location_name' => null,
            'location_address' => null,
            'location_lat' => null,
            'location_lng' => null,
        ]);
        $event->locations()->delete();
        $event->locations()->createMany([
            [
                'label' => 'Opština',
                'name' => 'Općina Stari Grad',
                'address' => 'Sarajevo',
                'lat' => 43.8599,
                'lng' => 18.4310,
                'is_primary' => true,
                'sort_order' => 0,
            ],
            [
                'label' => 'Džamija',
                'name' => 'Gazi Husrev-begova džamija',
                'address' => 'Sarajevo',
                'lat' => 43.8594,
                'lng' => 18.4286,
                'is_primary' => false,
                'sort_order' => 1,
            ],
        ]);

        Livewire::test(InvitationPage::class, ['slug' => $event->slug])
            ->assertSee('Općina Stari Grad')
            ->assertSee('Gazi Husrev-begova džamija')
            ->assertSee('Opština')
            ->assertSee('Džamija');
    }

    public function test_calendar_export_uses_primary_location(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'location_name' => 'Legacy Venue',
            'location_address' => 'Legacy Address',
        ]);
        $event->locations()->delete();
        $event->locations()->createMany([
            [
                'label' => 'Primary',
                'name' => 'Primary Hall',
                'address' => 'Primary Street 1',
                'is_primary' => true,
                'sort_order' => 0,
            ],
            [
                'label' => 'Secondary',
                'name' => 'Secondary Hall',
                'address' => 'Secondary Street 2',
                'is_primary' => false,
                'sort_order' => 1,
            ],
        ]);

        $response = $this->get(route('invitation.ics', $event->slug));

        $response->assertOk();
        $response->assertSee('LOCATION:Primary Hall Primary Street 1', false);
        $response->assertDontSee('Secondary Hall', false);
    }

    public function test_editing_rsvp_restores_menu_and_accommodation_state(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
            'accommodation_enabled' => true,
        ]);
        $menu = $event->menuOptions()->firstOrFail();
        $guest = Guest::factory()->for($event)->create([
            'rsvp_status' => RsvpStatus::Yes,
            'menu_option_id' => $menu->id,
            'accommodation_count' => 1,
        ]);

        Livewire::test(InvitationPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->call('editRsvp')
            ->assertSet('menuOptionId', (string) $menu->id)
            ->assertSet('needsAccommodation', true)
            ->assertSet('accommodationCount', 1);
    }
}
