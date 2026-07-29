<?php

namespace Tests\Feature;

use App\Filament\App\Widgets\MenuAccommodationSummaryWidget;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Models\WeddingMenuOption;
use App\PlatformMenu;
use App\RsvpStatus;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;

class MenuAccommodationSummaryWidgetTest extends TestCase
{
    public function test_summary_aggregates_menus_by_attendee_and_accommodation_by_group(): void
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
            'accommodation_enabled' => true,
        ]);

        $regular = $event->menuOptions()->where('platform_key', PlatformMenu::Regular)->firstOrFail();
        $vegetarian = $event->menuOptions()->where('platform_key', PlatformMenu::Vegetarian)->firstOrFail();
        $glutenFree = $event->menuOptions()->where('platform_key', PlatformMenu::GlutenFree)->firstOrFail();

        $guest = Guest::factory()->for($event)->create([
            'name' => 'Hanuma Softić',
            'rsvp_status' => RsvpStatus::Yes,
            'plus_one_name' => 'Mirza Softić',
            'menu_option_id' => $regular->id,
            'plus_one_menu_option_id' => $vegetarian->id,
            'accommodation_count' => 2,
        ]);
        $guest->children()->create([
            'name' => 'Ena',
            'menu_option_id' => $glutenFree->id,
            'sort_order' => 0,
        ]);

        Guest::factory()->for($event)->create([
            'name' => 'Declined Guest',
            'rsvp_status' => RsvpStatus::No,
            'menu_option_id' => $regular->id,
            'accommodation_count' => 3,
        ]);

        $otherEvent = WeddingEvent::factory()->create([
            'accommodation_enabled' => true,
        ]);
        $otherMenu = $otherEvent->menuOptions()->where('platform_key', PlatformMenu::Regular)->firstOrFail();
        Guest::factory()->for($otherEvent)->create([
            'name' => 'Other Wedding Guest',
            'rsvp_status' => RsvpStatus::Yes,
            'menu_option_id' => $otherMenu->id,
            'accommodation_count' => 4,
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(MenuAccommodationSummaryWidget::class)
            ->assertSuccessful()
            ->assertSee(__('app.menu_summary_heading'))
            ->assertSee(__('menu.platform_regular'))
            ->assertSee(__('menu.platform_vegetarian'))
            ->assertSee(__('menu.platform_gluten_free'))
            ->assertSee('Hanuma Softić')
            ->assertSee('Mirza Softić')
            ->assertSee('Ena')
            ->assertSee(__('app.accommodation_summary_heading'))
            ->assertSee(__('app.accommodation_summary_total_label'))
            ->assertSee('2')
            ->assertDontSee('Declined Guest')
            ->assertDontSee('Other Wedding Guest');
    }

    public function test_summary_shows_empty_states_when_no_requests(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->for($user)->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
            'accommodation_enabled' => true,
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(MenuAccommodationSummaryWidget::class)
            ->assertSuccessful()
            ->assertSee(__('app.menu_summary_no_selections'))
            ->assertSee(__('app.accommodation_summary_empty'));
    }

    public function test_summary_shows_disabled_accommodation_message(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->for($user)->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
            'accommodation_enabled' => false,
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(MenuAccommodationSummaryWidget::class)
            ->assertSuccessful()
            ->assertSee(__('app.accommodation_summary_disabled'));
    }

    public function test_widget_hidden_for_archived_weddings(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->for($user)->create([
            'wedding_date' => now()->subDays(2),
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $this->assertFalse(MenuAccommodationSummaryWidget::canView());
    }

    public function test_custom_menu_option_appears_in_summary(): void
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create([
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
            'accommodation_enabled' => false,
        ]);

        $custom = WeddingMenuOption::query()->create([
            'wedding_event_id' => $event->id,
            'platform_key' => null,
            'label' => 'Halal special',
            'is_visible' => true,
            'sort_order' => 10,
        ]);

        Guest::factory()->for($event)->create([
            'name' => 'Custom Menu Guest',
            'rsvp_status' => RsvpStatus::Yes,
            'menu_option_id' => $custom->id,
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(MenuAccommodationSummaryWidget::class)
            ->assertSuccessful()
            ->assertSee('Halal special')
            ->assertSee('Custom Menu Guest');
    }
}
