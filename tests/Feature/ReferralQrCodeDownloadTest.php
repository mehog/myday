<?php

namespace Tests\Feature;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReferralQrCodeDownloadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_cannot_download_referral_qr_code(): void
    {
        $this->get(route('referrals.qr-code.download', ['format' => 'a4']))
            ->assertRedirect(route('login'));
    }

    public function test_unverified_user_cannot_download_referral_qr_code(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('referrals.qr-code.download', ['format' => 'a4']))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_user_without_referral_account_gets_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('referrals.qr-code.download', ['format' => 'a4']))
            ->assertForbidden();
    }

    public function test_user_with_referral_account_can_download_pdf_for_each_format(): void
    {
        $user = User::factory()->create();
        $user->createReferralAccount();
        $user->unsetRelation('referralAccount');

        foreach (['a4', 'a5', 'letter'] as $format) {
            $response = $this->actingAs($user)
                ->get(route('referrals.qr-code.download', ['format' => $format]));

            $response->assertOk();
            $response->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF', $response->getContent() ?: '');
        }
    }

    public function test_invalid_format_falls_back_to_a4(): void
    {
        $user = User::factory()->create();
        Referral::query()->create([
            'user_id' => $user->id,
            'referrer_id' => null,
            'referral_code' => '_test1234',
        ]);

        $response = $this->actingAs($user)
            ->get(route('referrals.qr-code.download', ['format' => 'invalid-size']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent() ?: '');
    }
}
