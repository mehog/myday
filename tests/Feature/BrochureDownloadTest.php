<?php

namespace Tests\Feature;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrochureDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_download_brochure(): void
    {
        $this->get(route('referrals.brochure.download'))
            ->assertRedirect(route('login'));
    }

    public function test_unverified_user_cannot_download_brochure(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('referrals.brochure.download'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_user_without_referral_account_gets_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('referrals.brochure.download'))
            ->assertForbidden();
    }

    public function test_user_with_referral_account_can_download_brochure_pdf(): void
    {
        $user = User::factory()->create();
        $user->createReferralAccount();
        $user->unsetRelation('referralAccount');

        $response = $this->actingAs($user)
            ->get(route('referrals.brochure.download'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent() ?: '');
    }

    public function test_user_with_existing_referral_record_can_download_brochure_pdf(): void
    {
        $user = User::factory()->create();
        Referral::query()->create([
            'user_id' => $user->id,
            'referrer_id' => null,
            'referral_code' => '_brochure1',
        ]);

        $response = $this->actingAs($user)
            ->get(route('referrals.brochure.download'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
