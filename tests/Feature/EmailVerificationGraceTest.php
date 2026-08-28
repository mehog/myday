<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\EmailVerificationBanner;
use App\Livewire\Dashboard\Profile;
use App\Livewire\Onboarding\VerifyEmailNotice;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Support\DashboardNav;
use App\Support\UnverifiedEmail;
use App\Support\UserSignupIp;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class EmailVerificationGraceTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_unverified_user_has_email_verification_grace_by_default(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertTrue($user->hasEmailVerificationGrace());
        $this->assertNotNull($user->emailVerificationGraceExpiresAt());
    }

    public function test_verification_grace_expired_user_has_no_grace(): void
    {
        $user = User::factory()->verificationGraceExpired()->create();

        $this->assertFalse($user->hasEmailVerificationGrace());
    }

    public function test_verify_notice_redirects_to_dashboard_during_grace(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertRedirect(DashboardNav::homeUrl());
    }

    public function test_verify_notice_is_accessible_after_grace_expires(): void
    {
        $user = User::factory()->verificationGraceExpired()->create();

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk();
    }

    public function test_unverified_user_can_update_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create(['email' => 'old@example.com']);

        UnverifiedEmail::update($user, 'new@example.com');

        $user->refresh();

        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_user_cannot_update_email_to_existing_address(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->unverified()->create(['email' => 'mine@example.com']);

        $this->expectException(ValidationException::class);

        UnverifiedEmail::update($user, 'taken@example.com');
    }

    public function test_changing_email_does_not_extend_grace_period(): void
    {
        $user = User::factory()->verificationGraceExpired()->create([
            'email' => 'old@example.com',
        ]);

        UnverifiedEmail::update($user, 'new@example.com');

        $user->refresh();

        $this->assertFalse($user->hasEmailVerificationGrace());
    }

    public function test_dashboard_profile_allows_email_change_for_unverified_user(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create(['email' => 'old@example.com', 'is_admin' => false]);
        WeddingEvent::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(Profile::class)
            ->set('email', 'new@example.com')
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('new@example.com', $user->email);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_email_verification_banner_can_resend_and_update_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create(['email' => 'old@example.com', 'is_admin' => false]);
        WeddingEvent::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(EmailVerificationBanner::class)
            ->call('resend')
            ->assertSet('resent', true);

        Notification::assertSentTo($user, VerifyEmail::class);

        Livewire::actingAs($user)
            ->test(EmailVerificationBanner::class)
            ->set('email', 'new@example.com')
            ->call('updateEmail')
            ->assertHasNoErrors()
            ->assertSet('updated', true);

        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_verify_notice_can_update_email_after_grace_expires(): void
    {
        Notification::fake();

        $user = User::factory()->verificationGraceExpired()->create(['email' => 'old@example.com']);

        Livewire::actingAs($user)
            ->test(VerifyEmailNotice::class)
            ->set('email', 'new@example.com')
            ->call('updateEmail')
            ->assertHasNoErrors()
            ->assertSet('updated', true);

        $this->assertSame('new@example.com', $user->fresh()->email);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_signup_ip_capture_stores_ipstack_in_production(): void
    {
        Config::set('app.env', 'production');
        Config::set('services.ipstack.access_key', 'test-access-key');

        $ip = '203.0.113.30';

        Http::fake([
            'api.ipstack.com/*' => Http::response([
                'ip' => $ip,
                'country_code' => 'BA',
                'country_name' => 'Bosnia and Herzegovina',
            ]),
        ]);

        $user = User::factory()->unverified()->create();

        UserSignupIp::capture($user, $ip);

        $user->refresh();

        $this->assertSame($ip, $user->signup_ip);
        $this->assertSame('BA', $user->signupCountryCode());
    }

    public function test_unverified_user_within_grace_can_access_filament_app(): void
    {
        $user = User::factory()->unverified()->create(['is_admin' => false]);
        WeddingEvent::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get('/app')
            ->assertOk();
    }

    public function test_unverified_user_with_expired_grace_cannot_access_filament_app(): void
    {
        $user = User::factory()->verificationGraceExpired()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/app')
            ->assertRedirect(route('verification.notice'));
    }
}
