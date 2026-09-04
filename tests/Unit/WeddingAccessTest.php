<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\WeddingEvent;
use App\Models\WeddingMember;
use App\WeddingMemberRole;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class WeddingAccessTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_accessible_wedding_prefers_owned_wedding(): void
    {
        $user = User::factory()->create();
        $owned = WeddingEvent::factory()->create(['user_id' => $user->id]);
        $other = WeddingEvent::factory()->create();
        WeddingMember::query()->create([
            'wedding_event_id' => $other->id,
            'user_id' => $user->id,
            'role' => WeddingMemberRole::Partner,
        ]);

        $this->assertSame($owned->id, $user->accessibleWedding()?->id);
    }

    public function test_scope_accessible_by_includes_member_weddings(): void
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();
        $wedding = WeddingEvent::factory()->create(['user_id' => $owner->id]);

        WeddingMember::query()->create([
            'wedding_event_id' => $wedding->id,
            'user_id' => $partner->id,
            'role' => WeddingMemberRole::Partner,
        ]);

        $this->assertTrue(WeddingEvent::query()->accessibleBy($partner)->whereKey($wedding->id)->exists());
    }
}
