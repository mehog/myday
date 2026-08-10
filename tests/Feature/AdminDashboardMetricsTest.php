<?php

namespace Tests\Feature;

use App\Models\ReferralPayout;
use App\Models\User;
use App\Models\WeddingEvent;
use App\ReferralPayoutStatus;
use App\Support\AdminDashboardMetrics;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class AdminDashboardMetricsTest extends TestCase
{
    use RefreshInMemoryDatabase;

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

    public function test_login_activity_metrics_count_today_and_recent_successful(): void
    {
        $user = User::factory()->create();

        $user->authentications()->create([
            'ip_address' => '127.0.0.1',
            'login_at' => now(),
            'login_successful' => true,
        ]);
        $user->authentications()->create([
            'ip_address' => '127.0.0.1',
            'login_at' => now(),
            'login_successful' => false,
        ]);
        $user->authentications()->create([
            'ip_address' => '127.0.0.1',
            'login_at' => now()->subDay(),
            'login_successful' => true,
        ]);

        $this->assertSame(1, AdminDashboardMetrics::successfulLoginsTodayCount());
        $this->assertSame(1, AdminDashboardMetrics::failedLoginsTodayCount());
        $this->assertCount(2, AdminDashboardMetrics::recentSuccessfulLoginsQuery()->get());
        $this->assertCount(1, AdminDashboardMetrics::recentSuccessfulLoginsQuery(1)->get());
    }
}
