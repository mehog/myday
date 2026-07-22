<?php

namespace Tests\Unit;

use App\Models\Guest;
use App\Models\WeddingEvent;
use App\PlanTier;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class WeddingEventGuestLimitTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_unpaid_wedding_allows_guests_without_limit(): void
    {
        $wedding = WeddingEvent::factory()->inactive()->create([
            'plan_tier' => null,
            'guest_limit' => null,
        ]);

        Guest::factory()->count(3)->create(['wedding_event_id' => $wedding->id]);

        $this->assertTrue($wedding->canAddGuests(10));
        $this->assertNull($wedding->remainingGuestSlots());
        $this->assertSame(PlanTier::Basic, $wedding->requiredTierForCurrentGuests());
    }

    public function test_paid_basic_plan_enforces_limit(): void
    {
        $wedding = WeddingEvent::factory()->create([
            'plan_tier' => PlanTier::Basic,
            'guest_limit' => 2,
        ]);

        Guest::factory()->count(2)->create(['wedding_event_id' => $wedding->id]);

        $this->assertFalse($wedding->fresh()->canAddGuests());
        $this->assertSame(0, $wedding->fresh()->remainingGuestSlots());
    }

    public function test_soft_deleted_guests_do_not_count(): void
    {
        $wedding = WeddingEvent::factory()->create([
            'plan_tier' => PlanTier::Basic,
            'guest_limit' => 1,
        ]);

        $guest = Guest::factory()->create(['wedding_event_id' => $wedding->id]);
        $guest->delete();

        $this->assertTrue($wedding->fresh()->canAddGuests());
        $this->assertSame(1, $wedding->fresh()->remainingGuestSlots());
    }

    public function test_purchase_rules_for_upgrades_and_guest_coverage(): void
    {
        $wedding = WeddingEvent::factory()->inactive()->create([
            'plan_tier' => null,
            'guest_limit' => null,
        ]);

        Guest::factory()->count(150)->create(['wedding_event_id' => $wedding->id]);

        $wedding->forceFill([
            'plan_tier' => PlanTier::Basic,
            'guest_limit' => 100,
        ])->save();

        $this->assertFalse($wedding->fresh()->canPurchaseTier(PlanTier::Basic));
        $this->assertTrue($wedding->fresh()->canPurchaseTier(PlanTier::Plus));
        $this->assertTrue($wedding->fresh()->canPurchaseTier(PlanTier::Premium));
        $this->assertTrue($wedding->fresh()->canPurchaseTier(PlanTier::Deluxe));
        $this->assertSame(PlanTier::Plus, $wedding->fresh()->requiredTierForCurrentGuests());
    }

    public function test_apply_plan_tier_keeps_highest_and_activates(): void
    {
        $wedding = WeddingEvent::factory()->inactive()->create([
            'plan_tier' => PlanTier::Basic,
            'guest_limit' => 100,
        ]);

        $wedding->applyPlanTier(PlanTier::Plus);

        $this->assertTrue($wedding->fresh()->is_active);
        $this->assertSame(PlanTier::Plus, $wedding->fresh()->plan_tier);
        $this->assertSame(200, $wedding->fresh()->guest_limit);

        $wedding->applyPlanTier(PlanTier::Basic);

        $this->assertSame(PlanTier::Plus, $wedding->fresh()->plan_tier);
        $this->assertSame(200, $wedding->fresh()->guest_limit);
    }
}
