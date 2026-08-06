<?php

namespace Tests\Unit;

use App\BudgetCalculationType;
use App\BudgetCategory;
use App\BudgetGuestMode;
use App\Models\Guest;
use App\Models\GuestChild;
use App\Models\User;
use App\Models\WeddingBudgetItem;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class WeddingBudgetTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_confirmed_headcount_includes_plus_ones_and_children(): void
    {
        $wedding = WeddingEvent::factory()->create();

        $guest = Guest::factory()->for($wedding)->create([
            'rsvp_status' => RsvpStatus::Yes,
            'plus_one_name' => 'Companion',
        ]);
        GuestChild::query()->create([
            'guest_id' => $guest->id,
            'name' => 'Child',
            'sort_order' => 0,
        ]);
        Guest::factory()->for($wedding)->create([
            'rsvp_status' => RsvpStatus::No,
        ]);

        $breakdown = $wedding->confirmedHeadcountBreakdown();

        $this->assertSame(1, $breakdown['guests']);
        $this->assertSame(1, $breakdown['plus_ones']);
        $this->assertSame(1, $breakdown['children']);
        $this->assertSame(3, $breakdown['total']);
        $this->assertSame(3, $wedding->confirmedHeadcount());
        $this->assertSame(2, $wedding->invitedHeadcount());
    }

    public function test_budget_guest_count_respects_mode(): void
    {
        $wedding = WeddingEvent::factory()->create([
            'budget_guest_mode' => BudgetGuestMode::Manual,
            'budget_guest_count' => 120,
        ]);

        Guest::factory()->for($wedding)->count(5)->create([
            'rsvp_status' => RsvpStatus::Yes,
        ]);

        $this->assertSame(120, $wedding->budgetGuestCount());

        $wedding->update(['budget_guest_mode' => BudgetGuestMode::Confirmed]);
        $this->assertSame(5, $wedding->fresh()->budgetGuestCount());

        $wedding->update(['budget_guest_mode' => BudgetGuestMode::Invited]);
        $this->assertSame(5, $wedding->fresh()->budgetGuestCount());
    }

    public function test_line_total_and_budget_totals(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->for($user)->create([
            'budget_guest_mode' => BudgetGuestMode::Manual,
            'budget_guest_count' => 10,
            'budget_currency' => 'EUR',
        ]);

        WeddingBudgetItem::factory()->for($wedding)->create([
            'name' => 'Band',
            'category' => BudgetCategory::BendIGlazba,
            'calculation_type' => BudgetCalculationType::Fixed,
            'amount' => 1000,
            'is_paid' => true,
            'sort_order' => 1,
        ]);
        WeddingBudgetItem::factory()->for($wedding)->perPerson()->create([
            'name' => 'Dinner',
            'category' => BudgetCategory::SalaIVecera,
            'amount' => 50,
            'is_paid' => false,
            'sort_order' => 2,
        ]);

        $wedding->load('budgetItems');
        $totals = $wedding->budgetTotals();

        $this->assertSame('1500.00', $totals['total']);
        $this->assertSame('1000.00', $totals['paid']);
        $this->assertSame('500.00', $totals['unpaid']);
        $this->assertSame('150.00', $totals['per_person']);
        $this->assertSame('EUR', $wedding->budgetCurrency());
    }

    public function test_budget_currency_defaults_to_eur_when_unset(): void
    {
        $wedding = WeddingEvent::factory()->create([
            'budget_currency' => null,
        ]);

        $this->assertNull($wedding->budget_currency);
        $this->assertSame('EUR', $wedding->budgetCurrency());
    }
}
