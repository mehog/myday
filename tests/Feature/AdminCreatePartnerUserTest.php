<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Referral;
use App\Models\User;
use App\PlanTier;
use App\Services\SeedPartnerDemoWedding;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class AdminCreatePartnerUserTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_admin_can_create_partner_with_demo_wedding_and_referral_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Partner User',
                'email' => 'partner@example.com',
                'password' => 'secret-password',
                'is_admin' => false,
                'is_partner' => true,
                'locale' => 'en',
                'referral_fee_percentage' => 15,
                'referral_code' => 'partner-code',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'partner@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertFalse($user->is_admin);
        $this->assertSame('15.00', (string) $user->referral_fee_percentage);
        $this->assertTrue($user->hasReferralAccount());
        $this->assertSame('partner-code', $user->getReferralCode());

        $wedding = $user->weddingEvent;

        $this->assertNotNull($wedding);
        $this->assertFalse($wedding->is_demo);
        $this->assertTrue($wedding->is_active);
        $this->assertSame(PlanTier::Premium, $wedding->plan_tier);
        $this->assertSame('en', $wedding->invitation_locale);
        $this->assertSame(SeedPartnerDemoWedding::GUEST_COUNT, $wedding->guests()->count());
        $this->assertSame(3, $wedding->scheduleItems()->count());
        $this->assertTrue($wedding->locations()->exists());
    }

    public function test_normal_create_user_does_not_seed_partner_data(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Regular User',
                'email' => 'regular@example.com',
                'password' => 'password',
                'is_admin' => false,
                'is_partner' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'regular@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertFalse($user->hasReferralAccount());
        $this->assertNull($user->weddingEvent);
    }

    public function test_partner_create_rejects_duplicate_referral_code(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $existing = User::factory()->create();
        $existing->createReferralAccount();
        $existing->setReferralCode('taken-code');

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Other Partner',
                'email' => 'other-partner@example.com',
                'password' => 'password',
                'is_admin' => false,
                'is_partner' => true,
                'referral_code' => 'taken-code',
            ])
            ->call('create')
            ->assertHasFormErrors(['referral_code']);

        $this->assertNull(User::query()->where('email', 'other-partner@example.com')->first());
        $this->assertSame(1, Referral::query()->where('referral_code', 'taken-code')->count());
    }

    public function test_seed_partner_demo_wedding_is_noop_when_user_already_has_wedding(): void
    {
        $user = User::factory()->create();
        $existing = $user->ownedWeddingEvent()->create([
            'slug' => 'existing-wedding',
            'bride_name' => 'Existing',
            'groom_name' => 'Couple',
            'wedding_date' => now()->addMonth(),
            'is_active' => true,
            'is_demo' => false,
        ]);

        $result = app(SeedPartnerDemoWedding::class)->handle($user->fresh());

        $this->assertNull($result);
        $this->assertSame($existing->id, $user->fresh()->weddingEvent->id);
        $this->assertSame(0, $existing->guests()->count());
    }
}
