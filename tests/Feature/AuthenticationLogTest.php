<?php

namespace Tests\Feature;

use App\Filament\Pages\AdminDashboard;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\RelationManagers\AuthenticationsRelationManager;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\LoginActivityStatsWidget;
use App\Filament\Widgets\RecentLoginsWidget;
use App\Models\User;
use App\Support\AdminDashboardMetrics;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Rappasoft\LaravelAuthenticationLog\Notifications\NewDevice;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class AuthenticationLogTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_successful_login_creates_authentication_log_without_notifications(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        Auth::login($user);

        $this->assertDatabaseHas('authentication_log', [
            'authenticatable_type' => User::class,
            'authenticatable_id' => $user->id,
            'login_successful' => true,
        ]);

        Notification::assertNothingSent();
        Notification::assertNotSentTo($user, NewDevice::class);
    }

    public function test_failed_login_is_logged_without_notifications(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        event(new Failed('web', $user, ['email' => $user->email, 'password' => 'wrong']));

        $this->assertDatabaseHas('authentication_log', [
            'authenticatable_type' => User::class,
            'authenticatable_id' => $user->id,
            'login_successful' => false,
        ]);

        Notification::assertNothingSent();
    }

    public function test_admin_dashboard_metrics_and_widgets_show_recent_logins(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $couple = User::factory()->create(['name' => 'Recent Couple', 'email' => 'couple@example.com']);

        $couple->authentications()->create([
            'ip_address' => '203.0.113.10',
            'user_agent' => 'PHPUnit',
            'device_name' => 'Test Browser',
            'login_at' => now()->subMinute(),
            'login_successful' => true,
            'last_activity_at' => now()->subMinute(),
        ]);

        $couple->authentications()->create([
            'ip_address' => '203.0.113.11',
            'user_agent' => 'PHPUnit',
            'login_at' => now()->subMinutes(2),
            'login_successful' => false,
            'last_activity_at' => now()->subMinutes(2),
        ]);

        $this->assertSame(1, AdminDashboardMetrics::successfulLoginsTodayCount());
        $this->assertSame(1, AdminDashboardMetrics::failedLoginsTodayCount());
        $this->assertCount(1, AdminDashboardMetrics::recentSuccessfulLoginsQuery()->get());

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(LoginActivityStatsWidget::class)
            ->assertSee('Successful logins')
            ->assertSee('Failed logins');

        Livewire::test(RecentLoginsWidget::class)
            ->assertSee('Recent Couple')
            ->assertSee('couple@example.com')
            ->assertSee('203.0.113.10');

        $this->assertContains(LoginActivityStatsWidget::class, (new AdminDashboard)->getWidgets());
        $this->assertContains(RecentLoginsWidget::class, (new AdminDashboard)->getWidgets());
    }

    public function test_login_event_creates_authentication_log_for_user(): void
    {
        $user = User::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        event(new Login('web', $user, false));

        $this->assertSame(1, $user->authentications()->count());
    }

    public function test_user_resource_logins_relation_shows_authentication_history(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $user->authentications()->create([
            'ip_address' => '198.51.100.20',
            'user_agent' => 'Mozilla/5.0',
            'device_name' => 'Chrome on macOS',
            'login_at' => now()->subHour(),
            'login_successful' => true,
            'last_activity_at' => now()->subMinutes(10),
        ]);

        $user->authentications()->create([
            'ip_address' => '198.51.100.21',
            'user_agent' => 'Mozilla/5.0',
            'device_name' => 'Safari on iOS',
            'login_at' => now()->subMinutes(30),
            'login_successful' => false,
        ]);

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->assertContains(AuthenticationsRelationManager::class, UserResource::getRelations());

        Livewire::test(AuthenticationsRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass' => EditUser::class,
        ])
            ->assertSee('198.51.100.20')
            ->assertSee('198.51.100.21')
            ->assertSee('Chrome on macOS')
            ->assertSee('Success')
            ->assertSee('Failed');
    }
}
