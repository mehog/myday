<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\User;
use App\Support\AdminUserVerification;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manually_verify_unverified_user(): void
    {
        Event::fake([Verified::class]);

        $user = User::factory()->unverified()->create();

        $this->assertFalse($user->hasVerifiedEmail());

        $verified = AdminUserVerification::verify($user);

        $this->assertTrue($verified);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        Event::assertDispatched(Verified::class, fn (Verified $event): bool => $event->user->is($user));
    }

    public function test_admin_manual_verify_is_noop_for_already_verified_user(): void
    {
        Event::fake([Verified::class]);

        $user = User::factory()->create();

        $this->assertTrue($user->hasVerifiedEmail());

        $verified = AdminUserVerification::verify($user);

        $this->assertFalse($verified);
        Event::assertNotDispatched(Verified::class);
    }

    public function test_admin_can_resend_verification_email_for_unverified_user(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        AdminUserVerification::resend($user);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_admin_created_user_is_auto_verified(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Created User',
                'email' => 'created@example.com',
                'password' => 'password',
                'is_admin' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'created@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasVerifiedEmail());
    }
}
