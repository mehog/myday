<?php

namespace Tests\Feature;

use App\Exceptions\GuestLimitExceededException;
use App\Filament\Imports\GuestImporter;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\PlanTier;
use RuntimeException;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class GuestLimitTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_observer_blocks_create_when_limit_reached(): void
    {
        $wedding = WeddingEvent::factory()->create([
            'plan_tier' => PlanTier::Basic,
            'guest_limit' => 1,
        ]);

        Guest::factory()->create(['wedding_event_id' => $wedding->id]);

        $this->expectException(GuestLimitExceededException::class);

        Guest::factory()->create(['wedding_event_id' => $wedding->id]);
    }

    public function test_import_rejects_when_exceeding_remaining_slots(): void
    {
        $wedding = WeddingEvent::factory()->create([
            'plan_tier' => PlanTier::Basic,
            'guest_limit' => 2,
        ]);

        Guest::factory()->create(['wedding_event_id' => $wedding->id]);

        $csv = "name,email\nAlice,a@example.com\nBob,b@example.com\n";

        $this->expectException(RuntimeException::class);

        GuestImporter::importFromContents($wedding, $csv);
    }

    public function test_import_succeeds_within_limit(): void
    {
        $wedding = WeddingEvent::factory()->create([
            'plan_tier' => PlanTier::Basic,
            'guest_limit' => 3,
        ]);

        $csv = "name,email\nAlice,a@example.com\nBob,b@example.com\n";

        $count = GuestImporter::importFromContents($wedding, $csv);

        $this->assertSame(2, $count);
        $this->assertSame(2, $wedding->fresh()->activeGuestCount());
    }

    public function test_restore_blocked_when_at_limit(): void
    {
        $wedding = WeddingEvent::factory()->create([
            'plan_tier' => PlanTier::Basic,
            'guest_limit' => 1,
        ]);

        $deleted = Guest::factory()->create(['wedding_event_id' => $wedding->id]);
        $deleted->delete();

        Guest::factory()->create(['wedding_event_id' => $wedding->id]);

        $this->expectException(GuestLimitExceededException::class);

        $deleted->restore();
    }

    public function test_unpaid_wedding_can_still_add_guests(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->inactive()->create([
            'user_id' => $user->id,
            'plan_tier' => null,
            'guest_limit' => null,
        ]);

        Guest::factory()->count(5)->create(['wedding_event_id' => $wedding->id]);

        $this->assertSame(5, $wedding->fresh()->activeGuestCount());
        $this->assertTrue($wedding->fresh()->canAddGuests());
    }
}
