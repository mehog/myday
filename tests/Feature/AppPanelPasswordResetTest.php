<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Auth\Pages\PasswordReset\ResetPassword;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class AppPanelPasswordResetTest extends TestCase
{
    use RefreshInMemoryDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('app'));
    }

    public function test_couple_login_page_links_to_password_reset_request(): void
    {
        $this->get('/app/login')
            ->assertOk()
            ->assertSee('/app/password-reset/request', false);
    }

    public function test_password_reset_request_page_is_available(): void
    {
        $this->get('/app/password-reset/request')
            ->assertOk();

        Livewire::test(RequestPasswordReset::class)
            ->assertSuccessful();
    }

    public function test_couple_can_request_a_password_reset_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'is_admin' => false,
            'email' => 'couple@example.com',
        ]);

        Livewire::test(RequestPasswordReset::class)
            ->fillForm([
                'email' => $user->email,
            ])
            ->call('request')
            ->assertHasNoFormErrors()
            ->assertNotified();

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user): bool {
            $this->assertStringContainsString('/app/password-reset/reset', $notification->url);
            $this->assertStringContainsString(urlencode($user->email), $notification->url);

            return true;
        });
    }

    public function test_admin_does_not_receive_app_panel_password_reset_email(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'is_admin' => true,
            'email' => 'admin@example.com',
        ]);

        Livewire::test(RequestPasswordReset::class)
            ->fillForm([
                'email' => $admin->email,
            ])
            ->call('request')
            ->assertHasNoFormErrors();

        Notification::assertNotSentTo($admin, ResetPasswordNotification::class);
    }

    public function test_couple_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'email' => 'couple-reset@example.com',
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::broker()->createToken($user);

        Livewire::test(ResetPassword::class, [
            'email' => $user->email,
            'token' => $token,
        ])
            ->fillForm([
                'password' => 'new-password-123',
                'passwordConfirmation' => 'new-password-123',
            ])
            ->call('resetPassword')
            ->assertHasNoFormErrors()
            ->assertRedirect(Filament::getLoginUrl());

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertFalse(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_unknown_email_does_not_send_password_reset_email(): void
    {
        Notification::fake();

        Livewire::test(RequestPasswordReset::class)
            ->fillForm([
                'email' => 'missing@example.com',
            ])
            ->call('request')
            ->assertHasNoFormErrors();

        Notification::assertNothingSent();
    }
}
