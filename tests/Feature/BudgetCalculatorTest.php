<?php

namespace Tests\Feature;

use App\BudgetCalculationType;
use App\BudgetCategory;
use App\BudgetGuestMode;
use App\Filament\App\Pages\BudgetCalculator;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingBudgetItem;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class BudgetCalculatorTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_page_loads_for_wedding_owner(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->for($user)->create([
            'groom_name' => 'Enes',
            'bride_name' => 'Adisa',
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(BudgetCalculator::class)
            ->assertSuccessful()
            ->assertSee(__('budget.page_title'))
            ->assertSee('Enes & Adisa')
            ->assertSee(__('budget.items_heading'));
    }

    public function test_owner_can_add_edit_toggle_and_delete_items(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->for($user)->create([
            'budget_guest_mode' => BudgetGuestMode::Manual,
            'budget_guest_count' => 96,
            'budget_currency' => 'EUR',
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(BudgetCalculator::class)
            ->set('newName', 'Muzika')
            ->set('newCategory', BudgetCategory::BendIGlazba->value)
            ->set('newCalculationType', BudgetCalculationType::Fixed->value)
            ->set('newAmount', '2500')
            ->call('addItem')
            ->assertHasNoErrors()
            ->assertSee('Muzika');

        $item = $wedding->budgetItems()->first();
        $this->assertNotNull($item);
        $this->assertSame('2500.00', (string) $item->amount);

        Livewire::test(BudgetCalculator::class)
            ->call('startEdit', $item->id)
            ->set('editName', 'Bend')
            ->set('editAmount', '2600')
            ->call('saveEdit')
            ->assertHasNoErrors()
            ->assertSee('Bend');

        $this->assertSame('Bend', $item->fresh()->name);
        $this->assertSame('2600.00', (string) $item->fresh()->amount);

        Livewire::test(BudgetCalculator::class)
            ->call('togglePaid', $item->id)
            ->assertHasNoErrors();

        $this->assertTrue($item->fresh()->is_paid);

        Livewire::test(BudgetCalculator::class)
            ->call('deleteItem', $item->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('wedding_budget_items', ['id' => $item->id]);
    }

    public function test_per_person_totals_use_guest_count(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->for($user)->create([
            'budget_guest_mode' => BudgetGuestMode::Confirmed,
            'budget_currency' => 'EUR',
        ]);
        Guest::factory()->for($wedding)->count(2)->create([
            'rsvp_status' => RsvpStatus::Yes,
            'plus_one_name' => 'Plus',
        ]);
        WeddingBudgetItem::factory()->for($wedding)->create([
            'name' => 'Večera',
            'category' => BudgetCategory::SalaIVecera,
            'calculation_type' => BudgetCalculationType::PerPerson,
            'amount' => 45,
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        // 2 guests + 2 plus-ones = 4; 45 * 4 = 180
        Livewire::test(BudgetCalculator::class)
            ->assertSee('180,00 EUR')
            ->assertSee(__('budget.amount_x_guests', [
                'amount' => '45,00 EUR',
                'count' => 4,
            ]));
    }

    public function test_guest_settings_currency_and_target_can_be_saved_separately(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->for($user)->create([
            'budget_currency' => 'EUR',
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(BudgetCalculator::class)
            ->set('guestMode', BudgetGuestMode::Manual->value)
            ->set('guestCountInput', '100')
            ->call('saveGuestSettings')
            ->assertHasNoErrors()
            ->set('currency', 'BAM')
            ->call('saveCurrency')
            ->assertHasNoErrors()
            ->set('targetInput', '8000')
            ->call('saveTarget')
            ->assertHasNoErrors();

        $wedding->refresh();
        $this->assertSame(BudgetGuestMode::Manual, $wedding->budget_guest_mode);
        $this->assertSame(100, $wedding->budget_guest_count);
        $this->assertSame('BAM', $wedding->budget_currency);
        $this->assertSame('8000.00', (string) $wedding->budget_target);
    }

    public function test_add_item_without_title_uses_category_label(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->for($user)->create([
            'budget_currency' => 'EUR',
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(BudgetCalculator::class)
            ->set('newName', '')
            ->set('newCategory', BudgetCategory::BendIGlazba->value)
            ->set('newCalculationType', BudgetCalculationType::Fixed->value)
            ->set('newAmount', '500')
            ->call('addItem')
            ->assertHasNoErrors();

        $item = $wedding->budgetItems()->first();
        $this->assertNotNull($item);
        $this->assertSame(BudgetCategory::BendIGlazba->label(), $item->name);
    }

    public function test_locked_wedding_rejects_mutations(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->for($user)->create([
            'wedding_date' => now()->subDays(2)->setTime(16, 0),
            'budget_currency' => 'EUR',
        ]);
        $item = WeddingBudgetItem::factory()->for($wedding)->create([
            'name' => 'Existing',
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(BudgetCalculator::class)
            ->assertSuccessful()
            ->assertSee(__('budget.locked_notice'))
            ->set('newName', 'New item')
            ->set('newAmount', '10')
            ->call('addItem')
            ->assertForbidden();

        $this->assertSame(1, $wedding->budgetItems()->count());
        $this->assertTrue($item->fresh()->exists);
    }

    public function test_items_are_scoped_to_owner_wedding(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ownerWedding = WeddingEvent::factory()->for($owner)->create([
            'budget_currency' => 'EUR',
        ]);
        $otherWedding = WeddingEvent::factory()->for($other)->create([
            'budget_currency' => 'EUR',
        ]);

        $otherItem = WeddingBudgetItem::factory()->for($otherWedding)->create([
            'name' => 'Secret other item',
        ]);

        $this->actingAs($owner);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(BudgetCalculator::class)
            ->assertDontSee('Secret other item')
            ->set('newName', 'Owner item')
            ->set('newCategory', BudgetCategory::Ostalo->value)
            ->set('newCalculationType', BudgetCalculationType::Fixed->value)
            ->set('newAmount', '50')
            ->call('addItem');

        $this->assertTrue($ownerWedding->budgetItems()->where('name', 'Owner item')->exists());
        $this->assertFalse($ownerWedding->budgetItems()->whereKey($otherItem->id)->exists());
    }
}
