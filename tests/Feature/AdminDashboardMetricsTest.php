<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\ReferralPayout;
use App\Models\User;
use App\Models\WeddingEvent;
use App\ReferralPayoutStatus;
use App\Support\AdminDashboardMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_activations_count_excludes_active_and_demo_weddings(): void
    {
        WeddingEvent::factory()->inactive()->create();
        WeddingEvent::factory()->create(['is_active' => true]);
        WeddingEvent::factory()->inactive()->create(['is_demo' => true]);

        $this->assertSame(1, AdminDashboardMetrics::pendingActivationsCount());
        $this->assertCount(1, AdminDashboardMetrics::pendingActivationsQuery()->get());
    }

    public function test_unverified_couples_count_excludes_admins(): void
    {
        User::factory()->unverified()->create(['is_admin' => false]);
        User::factory()->unverified()->create(['is_admin' => true]);
        User::factory()->create(['is_admin' => false]);

        $this->assertSame(1, AdminDashboardMetrics::unverifiedCouplesCount());
        $this->assertCount(1, AdminDashboardMetrics::unverifiedUsersQuery()->get());
    }

    public function test_new_signups_count_includes_only_recent_non_admin_users(): void
    {
        User::factory()->create([
            'is_admin' => false,
            'created_at' => now()->subDays(3),
        ]);
        User::factory()->create([
            'is_admin' => false,
            'created_at' => now()->subDays(10),
        ]);
        User::factory()->create([
            'is_admin' => true,
            'created_at' => now()->subDays(2),
        ]);

        $this->assertSame(1, AdminDashboardMetrics::newSignupsCount());
    }

    public function test_pending_payouts_count_and_query(): void
    {
        $referrer = User::factory()->create();

        ReferralPayout::query()->create([
            'referrer_id' => $referrer->id,
            'amount' => 50,
            'currency' => 'EUR',
            'period' => '2026-Q1',
            'status' => ReferralPayoutStatus::Pending,
        ]);
        ReferralPayout::query()->create([
            'referrer_id' => $referrer->id,
            'amount' => 75,
            'currency' => 'EUR',
            'period' => '2026-Q2',
            'status' => ReferralPayoutStatus::Paid,
            'paid_at' => now(),
        ]);

        $this->assertSame(1, AdminDashboardMetrics::pendingPayoutsCount());
        $this->assertCount(1, AdminDashboardMetrics::pendingPayoutsQuery()->get());
    }

    public function test_recent_enquiries_count_includes_only_last_seven_days(): void
    {
        Enquiry::factory()->create(['created_at' => now()->subDays(2)]);
        Enquiry::factory()->create(['created_at' => now()->subDays(10)]);

        $this->assertSame(1, AdminDashboardMetrics::recentEnquiriesCount());
        $this->assertCount(2, AdminDashboardMetrics::recentEnquiriesQuery()->get());
    }
}
