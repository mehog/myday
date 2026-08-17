<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\PlanFeature;
use App\PlanTier;
use App\RsvpStatus;
use App\Support\DashboardNav;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class PlanFeatureGateTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_free_plan_blocks_seating_pdf_export(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'plan_tier' => PlanTier::Free,
            'guest_limit' => 50,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('seating-plan.export-pdf'))
            ->assertForbidden();
    }

    public function test_paid_plan_allows_seating_pdf_export(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'plan_tier' => PlanTier::Basic,
            'guest_limit' => 100,
            'is_active' => true,
            'seating_plan' => ['tables' => []],
        ]);

        $this->actingAs($user)
            ->get(route('seating-plan.export-pdf'))
            ->assertOk();
    }

    public function test_free_plan_blocks_place_cards_download(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'plan_tier' => PlanTier::Free,
            'guest_limit' => 50,
            'is_active' => true,
        ]);

        Guest::factory()->create([
            'wedding_event_id' => $wedding->id,
            'rsvp_status' => RsvpStatus::Yes,
        ]);

        $this->actingAs($user)
            ->get(route('guests.place-cards.download'))
            ->assertForbidden();
    }

    public function test_free_plan_does_not_accept_guest_photos(): void
    {
        $wedding = WeddingEvent::factory()->create([
            'plan_tier' => PlanTier::Free,
            'guest_limit' => 50,
            'wedding_date' => now(),
            'is_active' => true,
        ]);

        $this->assertFalse($wedding->hasFeature(PlanFeature::QrPhotoAlbum));
        $this->assertFalse($wedding->acceptsGuestPhotos());
    }

    public function test_paid_plan_accepts_guest_photos_in_window(): void
    {
        $wedding = WeddingEvent::factory()->create([
            'plan_tier' => PlanTier::Plus,
            'guest_limit' => 250,
            'wedding_date' => now(),
            'is_active' => true,
        ]);

        $this->assertTrue($wedding->hasFeature(PlanFeature::QrPhotoAlbum));
        $this->assertTrue($wedding->acceptsGuestPhotos());
    }

    public function test_checkout_rejects_free_and_deluxe_tiers(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'plan_tier' => PlanTier::Free,
            'guest_limit' => 50,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('dodo.checkout'), ['tier' => 'free'])
            ->assertRedirect(DashboardNav::pricingUrl());

        $this->actingAs($user)
            ->from(DashboardNav::pricingUrl())
            ->post(route('dodo.checkout'), ['tier' => 'deluxe'])
            ->assertRedirect(DashboardNav::pricingUrl());
    }
}
