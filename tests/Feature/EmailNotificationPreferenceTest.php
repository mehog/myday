<?php

namespace Tests\Feature;

use App\DiscountEmailAudience;
use App\DiscountEmailCampaignStatus;
use App\DiscountEmailRecipientStatus;
use App\DiscountType;
use App\Jobs\SendDiscountCampaignEmailsJob;
use App\Livewire\Dashboard\Profile as DashboardProfile;
use App\Models\DiscountCode;
use App\Models\DiscountEmailCampaign;
use App\Models\DiscountEmailRecipient;
use App\Models\DiscountEmailTemplate;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Notifications\CoupleOnboardingTipNotification;
use App\Notifications\DiscountCodeEmailNotification;
use App\PlanTier;
use App\Services\DiscountCampaignAudienceResolver;
use App\Services\WeddingScheduledNotificationService;
use Database\Seeders\DiscountEmailTemplateSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;
use Thomasjohnkane\Snooze\Models\ScheduledNotification as ScheduledNotificationModel;

class EmailNotificationPreferenceTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_profile_can_disable_email_notifications_and_cancels_pending_tips(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'created_at' => now()]);
        $event = WeddingEvent::withoutEvents(fn () => WeddingEvent::factory()->for($user)->create([
            'is_active' => true,
            'plan_tier' => PlanTier::Free,
            'wedding_date' => now()->addMonths(4),
        ]));

        app(WeddingScheduledNotificationService::class)->syncCoupleOnboarding($event);
        $this->assertSame(3, ScheduledNotificationModel::query()
            ->whereNull('cancelled_at')
            ->where('target_type', User::class)
            ->where('target_id', $user->id)
            ->count());

        Livewire::actingAs($user)
            ->test(DashboardProfile::class)
            ->assertSet('email_notifications_enabled', true)
            ->assertSee(__('dashboard.profile_email_notifications'))
            ->set('email_notifications_enabled', false)
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertFalse($user->wantsProductEmail());
        $this->assertSame(0, ScheduledNotificationModel::query()
            ->whereNull('cancelled_at')
            ->where('target_type', User::class)
            ->where('target_id', $user->id)
            ->count());
    }

    public function test_verify_email_still_sends_when_product_emails_disabled(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->optedOutOfProductEmail()->create();

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_discount_audience_excludes_opted_out_users(): void
    {
        $this->seed(DiscountEmailTemplateSeeder::class);

        $enabled = User::factory()->create();
        WeddingEvent::factory()->for($enabled)->create([
            'plan_tier' => PlanTier::Premium,
            'is_active' => true,
        ]);

        $optedOut = User::factory()->optedOutOfProductEmail()->create();
        WeddingEvent::factory()->for($optedOut)->create([
            'plan_tier' => PlanTier::Premium,
            'is_active' => true,
        ]);

        $template = DiscountEmailTemplate::query()->firstOrFail();
        $campaign = DiscountEmailCampaign::query()->create([
            'discount_email_template_id' => $template->id,
            'audience' => DiscountEmailAudience::Paid,
            'status' => DiscountEmailCampaignStatus::Draft,
        ]);

        $users = app(DiscountCampaignAudienceResolver::class)->resolve($campaign);

        $this->assertTrue($users->contains('id', $enabled->id));
        $this->assertFalse($users->contains('id', $optedOut->id));
    }

    public function test_discount_send_job_skips_opted_out_users(): void
    {
        Notification::fake();
        $this->seed(DiscountEmailTemplateSeeder::class);

        $user = User::factory()->optedOutOfProductEmail()->create();
        WeddingEvent::factory()->for($user)->create([
            'plan_tier' => PlanTier::Free,
            'is_active' => true,
        ]);

        $template = DiscountEmailTemplate::query()->firstOrFail();
        $code = DiscountCode::query()->create([
            'code' => 'SAVE10',
            'name' => 'Save 10',
            'type' => DiscountType::Percentage,
            'amount' => 10,
            'is_active' => true,
        ]);
        $campaign = DiscountEmailCampaign::query()->create([
            'discount_email_template_id' => $template->id,
            'discount_code_id' => $code->id,
            'audience' => DiscountEmailAudience::Manual,
            'user_ids' => [$user->id],
            'status' => DiscountEmailCampaignStatus::Draft,
            'previewed_at' => now(),
        ]);

        $recipient = DiscountEmailRecipient::query()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => DiscountEmailRecipientStatus::Pending,
        ]);

        (new SendDiscountCampaignEmailsJob($campaign->id))->handle();

        $recipient->refresh();
        $this->assertSame(DiscountEmailRecipientStatus::Skipped, $recipient->status);
        Notification::assertNotSentTo($user, DiscountCodeEmailNotification::class);
    }

    public function test_onboarding_tip_mail_includes_unsubscribe_link(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->for($user)->create([
            'is_active' => true,
            'plan_tier' => PlanTier::Free,
        ]);

        $mail = (new CoupleOnboardingTipNotification('day1'))->toMail($user);
        $rendered = $mail->render();

        $this->assertStringContainsString(route('dashboard.profile'), $rendered);
        $this->assertStringContainsString(__('notifications.unsubscribe_action'), $rendered);
    }
}
