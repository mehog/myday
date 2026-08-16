<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\WeddingEvent;
use Tests\TestCase;

class CoupleDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_cannot_access_couple_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_verified_couple_can_view_dashboard_home(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        WeddingEvent::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('NasDan');
    }

    public function test_dashboard_pages_are_reachable(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        WeddingEvent::factory()->create(['user_id' => $user->id]);

        $routes = [
            'dashboard',
            'dashboard.wedding',
            'dashboard.guests',
            'dashboard.messages',
            'dashboard.budget',
            'dashboard.seating',
            'dashboard.pushes',
            'dashboard.pushes.create',
            'dashboard.pricing',
            'dashboard.referrals',
            'dashboard.profile',
        ];

        foreach ($routes as $name) {
            $this->actingAs($user)
                ->get(route($name))
                ->assertOk();
        }
    }

    public function test_unverified_couple_is_redirected_from_dashboard(): void
    {
        $user = User::factory()->unverified()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect();
    }
}
