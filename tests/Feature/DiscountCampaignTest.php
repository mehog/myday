<?php

namespace Tests\Feature;

use App\DiscountEmailAudience;
use App\DiscountEmailCampaignStatus;
use App\DiscountEmailRecipientStatus;
use App\DiscountType;
use App\Jobs\SendDiscountCampaignEmailsJob;
use App\Models\DiscountCode;
use App\Models\DiscountEmailCampaign;
use App\Models\DiscountEmailRecipient;
use App\Models\DiscountEmailTemplate;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Notifications\DiscountCodeEmailNotification;
use App\PlanTier;
use App\Services\DiscountCampaignAudienceResolver;
use App\Services\DiscountCampaignSender;
use Database\Seeders\DiscountEmailTemplateSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Mime\Email;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class DiscountCampaignTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_audience_resolver_unpaid_verified(): void
    {
        $unpaid = User::factory()->create();
        WeddingEvent::factory()->inactive()->for($unpaid)->create(['plan_tier' => null]);

        $paid = User::factory()->create();
        WeddingEvent::factory()->for($paid)->create(['plan_tier' => PlanTier::Basic]);

        $unverified = User::factory()->unverified()->create();
        WeddingEvent::factory()->inactive()->for($unverified)->create(['plan_tier' => null]);

        $admin = User::factory()->create(['is_admin' => true]);
        WeddingEvent::factory()->inactive()->for($admin)->create(['plan_tier' => null]);

        $demoUser = User::factory()->create();
        WeddingEvent::factory()->inactive()->for($demoUser)->create([
            'plan_tier' => null,
            'is_demo' => true,
        ]);

        $campaign = $this->makeCampaign(['audience' => DiscountEmailAudience::UnpaidVerified]);
        $users = app(DiscountCampaignAudienceResolver::class)->resolve($campaign);

        $this->assertTrue($users->contains('id', $unpaid->id));
        $this->assertFalse($users->contains('id', $paid->id));
        $this->assertFalse($users->contains('id', $unverified->id));
        $this->assertFalse($users->contains('id', $admin->id));
        $this->assertFalse($users->contains('id', $demoUser->id));
    }

    public function test_audience_resolver_manual_users(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        User::factory()->create();

        $campaign = $this->makeCampaign([
            'audience' => DiscountEmailAudience::Manual,
            'user_ids' => [$a->id, $b->id],
        ]);

        $users = app(DiscountCampaignAudienceResolver::class)->resolve($campaign);

        $this->assertCount(2, $users);
        $this->assertTrue($users->contains('id', $a->id));
        $this->assertTrue($users->contains('id', $b->id));
    }

    public function test_manual_audience_requires_users(): void
    {
        $campaign = $this->makeCampaign([
            'audience' => DiscountEmailAudience::Manual,
            'user_ids' => [],
        ]);

        $this->expectException(InvalidArgumentException::class);
        app(DiscountCampaignAudienceResolver::class)->resolve($campaign);
    }

    public function test_send_locale_overrides_user_preferred_locale(): void
    {
        Notification::fake();

        $user = User::factory()->create(['locale' => 'en', 'name' => 'Ana']);
        WeddingEvent::factory()->inactive()->for($user)->create(['plan_tier' => null]);

        $campaign = $this->makeCampaign([
            'audience' => DiscountEmailAudience::Manual,
            'user_ids' => [$user->id],
            'send_locale' => 'bs',
            'previewed_at' => now(),
        ]);

        app(DiscountCampaignSender::class)->send($campaign, requirePreview: false);
        (new SendDiscountCampaignEmailsJob($campaign->id))->handle();

        Notification::assertSentTo($user, DiscountCodeEmailNotification::class, function (DiscountCodeEmailNotification $notification) {
            return $notification->locale === 'bs';
        });

        $recipient = DiscountEmailRecipient::query()->where('user_id', $user->id)->first();
        $this->assertSame('bs', $recipient?->locale);
        $this->assertSame(DiscountEmailRecipientStatus::Sent, $recipient?->status);
    }

    public function test_inherited_locale_uses_user_preference(): void
    {
        Notification::fake();

        $user = User::factory()->create(['locale' => 'de']);
        WeddingEvent::factory()->inactive()->for($user)->create(['plan_tier' => null]);

        $campaign = $this->makeCampaign([
            'audience' => DiscountEmailAudience::Manual,
            'user_ids' => [$user->id],
            'send_locale' => null,
            'previewed_at' => now(),
        ]);

        app(DiscountCampaignSender::class)->send($campaign, requirePreview: false);
        (new SendDiscountCampaignEmailsJob($campaign->id))->handle();

        Notification::assertSentTo($user, DiscountCodeEmailNotification::class, function (DiscountCodeEmailNotification $notification) {
            return $notification->locale === 'de';
        });
    }

    public function test_expires_clause_omitted_when_code_has_no_expiry(): void
    {
        $user = User::factory()->create(['locale' => 'en', 'name' => 'Ana']);
        WeddingEvent::factory()->inactive()->for($user)->create(['plan_tier' => null]);

        $template = $this->makeTemplate([
            'subjects' => [
                'en' => 'Use {{code}}{{expires_clause}}',
                'bs' => 'Use {{code}}{{expires_clause}}',
                'de' => 'Use {{code}}{{expires_clause}}',
                'hr' => 'Use {{code}}{{expires_clause}}',
            ],
            'bodies' => [
                'en' => 'Code {{code}} for {{discount_label}} off{{expires_clause}}.',
                'bs' => 'Code {{code}} for {{discount_label}} off{{expires_clause}}.',
                'de' => 'Code {{code}} for {{discount_label}} off{{expires_clause}}.',
                'hr' => 'Code {{code}} for {{discount_label}} off{{expires_clause}}.',
            ],
        ]);

        $code = DiscountCode::query()->create([
            'code' => 'OPEN15',
            'name' => 'Open',
            'type' => DiscountType::Percentage,
            'amount' => 15,
            'is_active' => true,
            'expires_at' => null,
        ]);

        $campaign = DiscountEmailCampaign::query()->create([
            'discount_code_id' => $code->id,
            'discount_email_template_id' => $template->id,
            'audience' => DiscountEmailAudience::Manual,
            'user_ids' => [$user->id],
            'status' => DiscountEmailCampaignStatus::Draft,
        ]);

        $user->notifyNow(new DiscountCodeEmailNotification($campaign, $code, 'en'));

        $email = $this->lastSentEmail();
        $this->assertSame('Use OPEN15', $email->getSubject());
        $this->assertStringContainsString('Code OPEN15 for 15% off.', $email->getTextBody());
        $this->assertStringNotContainsString('before', $email->getTextBody());
        $this->assertStringNotContainsString('No expiry', $email->getTextBody());
    }

    public function test_expires_clause_included_when_code_has_expiry(): void
    {
        $user = User::factory()->create(['locale' => 'en', 'name' => 'Ana']);
        WeddingEvent::factory()->inactive()->for($user)->create(['plan_tier' => null]);

        $expiresAt = now()->addDays(10)->startOfDay();

        $template = $this->makeTemplate([
            'subjects' => [
                'en' => 'Use {{code}}{{expires_clause}}',
                'bs' => 'Use {{code}}{{expires_clause}}',
                'de' => 'Use {{code}}{{expires_clause}}',
                'hr' => 'Use {{code}}{{expires_clause}}',
            ],
            'bodies' => [
                'en' => 'Code {{code}}{{expires_clause}}.',
                'bs' => 'Code {{code}}{{expires_clause}}.',
                'de' => 'Code {{code}}{{expires_clause}}.',
                'hr' => 'Code {{code}}{{expires_clause}}.',
            ],
        ]);

        $code = DiscountCode::query()->create([
            'code' => 'SAVE15',
            'name' => 'Spring',
            'type' => DiscountType::Percentage,
            'amount' => 15,
            'is_active' => true,
            'expires_at' => $expiresAt,
        ]);

        $campaign = DiscountEmailCampaign::query()->create([
            'discount_code_id' => $code->id,
            'discount_email_template_id' => $template->id,
            'audience' => DiscountEmailAudience::Manual,
            'user_ids' => [$user->id],
            'status' => DiscountEmailCampaignStatus::Draft,
        ]);

        $user->notifyNow(new DiscountCodeEmailNotification($campaign, $code, 'en'));

        $date = $expiresAt->timezone(config('app.timezone'))->format('Y-m-d');
        $email = $this->lastSentEmail();
        $this->assertSame('Use SAVE15 before '.$date, $email->getSubject());
        $this->assertStringContainsString('before '.$date, $email->getTextBody());
    }

    public function test_notification_uses_template_locale_content(): void
    {
        $user = User::factory()->create([
            'locale' => 'en',
            'name' => 'Ana',
        ]);
        WeddingEvent::factory()->inactive()->for($user)->create(['plan_tier' => null]);

        $template = $this->makeTemplate([
            'subjects' => [
                'en' => 'EN subject {{code}}',
                'bs' => 'BS subject {{code}}',
                'de' => 'DE subject {{code}}',
                'hr' => 'HR subject {{code}}',
            ],
            'bodies' => [
                'en' => 'EN body for {{name}}',
                'bs' => 'BS body for {{name}}',
                'de' => 'DE body for {{name}}',
                'hr' => 'HR body for {{name}}',
            ],
        ]);

        $code = DiscountCode::query()->create([
            'code' => 'SAVE15',
            'name' => 'Spring',
            'type' => DiscountType::Percentage,
            'amount' => 15,
            'is_active' => true,
            'expires_at' => now()->addDays(10)->startOfDay(),
        ]);

        $campaign = DiscountEmailCampaign::query()->create([
            'discount_code_id' => $code->id,
            'discount_email_template_id' => $template->id,
            'audience' => DiscountEmailAudience::Manual,
            'user_ids' => [$user->id],
            'status' => DiscountEmailCampaignStatus::Draft,
        ]);

        app()->setLocale('en');
        $user->notifyNow(new DiscountCodeEmailNotification($campaign, $code, 'bs'));

        $email = $this->lastSentEmail();
        $this->assertSame('BS subject SAVE15', $email->getSubject());
        $this->assertStringContainsString('BS body for Ana', $email->getTextBody());
        $this->assertSame('en', app()->getLocale());
    }

    public function test_send_is_idempotent_for_already_sent_recipients(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        WeddingEvent::factory()->inactive()->for($user)->create(['plan_tier' => null]);

        $campaign = $this->makeCampaign([
            'audience' => DiscountEmailAudience::Manual,
            'user_ids' => [$user->id],
            'previewed_at' => now(),
            'status' => DiscountEmailCampaignStatus::Draft,
        ]);

        app(DiscountCampaignSender::class)->send($campaign, requirePreview: false);
        (new SendDiscountCampaignEmailsJob($campaign->id))->handle();

        $this->assertSame(1, Notification::sent($user, DiscountCodeEmailNotification::class)->count());

        $campaign->refresh()->update(['status' => DiscountEmailCampaignStatus::Draft]);
        app(DiscountCampaignSender::class)->materializeRecipients($campaign);
        (new SendDiscountCampaignEmailsJob($campaign->id))->handle();

        $this->assertSame(1, Notification::sent($user, DiscountCodeEmailNotification::class)->count());
        $this->assertSame(1, $campaign->recipients()->count());
    }

    public function test_preview_required_before_send(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->inactive()->for($user)->create(['plan_tier' => null]);

        $campaign = $this->makeCampaign([
            'audience' => DiscountEmailAudience::Manual,
            'user_ids' => [$user->id],
            'previewed_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        app(DiscountCampaignSender::class)->send($campaign, requirePreview: true);
    }

    public function test_send_dispatches_job_and_snapshots_subject(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        WeddingEvent::factory()->inactive()->for($user)->create(['plan_tier' => null]);

        $campaign = $this->makeCampaign([
            'audience' => DiscountEmailAudience::Manual,
            'user_ids' => [$user->id],
            'send_locale' => 'en',
            'previewed_at' => now(),
        ]);

        app(DiscountCampaignSender::class)->send($campaign, requirePreview: false);

        Queue::assertPushed(SendDiscountCampaignEmailsJob::class, function (SendDiscountCampaignEmailsJob $job) use ($campaign) {
            return $job->campaignId === $campaign->id && $job->failedOnly === false;
        });

        $campaign->refresh();
        $this->assertSame(DiscountEmailCampaignStatus::Sending, $campaign->status);
        $this->assertSame(1, $campaign->recipients()->count());
        $this->assertSame('EN offer {{code}}', $campaign->subject);
        $this->assertNotNull($campaign->body);
    }

    public function test_resend_failed_only(): void
    {
        Notification::fake();

        $ok = User::factory()->create();
        $fail = User::factory()->create();

        $campaign = $this->makeCampaign([
            'audience' => DiscountEmailAudience::Manual,
            'user_ids' => [$ok->id, $fail->id],
            'status' => DiscountEmailCampaignStatus::Sent,
            'sent_at' => now(),
            'previewed_at' => now(),
        ]);

        DiscountEmailRecipient::query()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $ok->id,
            'email' => $ok->email,
            'status' => DiscountEmailRecipientStatus::Sent,
            'sent_at' => now(),
            'locale' => 'en',
        ]);
        DiscountEmailRecipient::query()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $fail->id,
            'email' => $fail->email,
            'status' => DiscountEmailRecipientStatus::Failed,
            'error' => 'boom',
        ]);

        (new SendDiscountCampaignEmailsJob($campaign->id, failedOnly: true))->handle();

        Notification::assertSentTo($fail, DiscountCodeEmailNotification::class);
        Notification::assertNotSentTo($ok, DiscountCodeEmailNotification::class);
        $this->assertSame(DiscountEmailRecipientStatus::Sent, $campaign->recipients()->where('user_id', $fail->id)->first()?->status);
    }

    public function test_template_cannot_be_deleted_when_in_use(): void
    {
        $campaign = $this->makeCampaign();
        $template = $campaign->template;

        $this->assertTrue($template->isInUse());

        $this->expectException(QueryException::class);
        $template->delete();
    }

    public function test_template_seeder_creates_two_multilingual_examples(): void
    {
        $this->seed(DiscountEmailTemplateSeeder::class);

        $templates = DiscountEmailTemplate::query()->orderBy('name')->get();
        $this->assertCount(2, $templates);

        foreach ($templates as $template) {
            foreach (['en', 'bs', 'de', 'hr'] as $locale) {
                $this->assertNotEmpty($template->subjectFor($locale));
                $this->assertNotEmpty($template->bodyFor($locale));
            }
        }

        $this->seed(DiscountEmailTemplateSeeder::class);
        $this->assertSame(2, DiscountEmailTemplate::query()->count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeTemplate(array $overrides = []): DiscountEmailTemplate
    {
        return DiscountEmailTemplate::query()->create(array_merge([
            'name' => 'Test template '.fake()->unique()->numerify('###'),
            'is_active' => true,
            'subjects' => [
                'en' => 'EN offer {{code}}',
                'bs' => 'BS offer {{code}}',
                'de' => 'DE offer {{code}}',
                'hr' => 'HR offer {{code}}',
            ],
            'bodies' => [
                'en' => 'EN use {{code}} for {{discount_label}} off{{expires_clause}}.',
                'bs' => 'BS use {{code}} for {{discount_label}} off{{expires_clause}}.',
                'de' => 'DE use {{code}} for {{discount_label}} off{{expires_clause}}.',
                'hr' => 'HR use {{code}} for {{discount_label}} off{{expires_clause}}.',
            ],
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeCampaign(array $overrides = []): DiscountEmailCampaign
    {
        $code = DiscountCode::query()->create([
            'code' => 'TEST'.fake()->unique()->numerify('###'),
            'name' => 'Test code',
            'type' => DiscountType::Percentage,
            'amount' => 15,
            'is_active' => true,
        ]);

        $template = $overrides['discount_email_template_id'] ?? null
            ? DiscountEmailTemplate::query()->findOrFail($overrides['discount_email_template_id'])
            : $this->makeTemplate();

        return DiscountEmailCampaign::query()->create(array_merge([
            'discount_code_id' => $code->id,
            'discount_email_template_id' => $template->id,
            'audience' => DiscountEmailAudience::UnpaidVerified,
            'status' => DiscountEmailCampaignStatus::Draft,
        ], $overrides));
    }

    protected function lastSentEmail(): Email
    {
        $messages = app('mail.manager')->mailer()->getSymfonyTransport()->messages();

        $this->assertNotEmpty($messages);

        $email = $messages->last()->getOriginalMessage();
        $this->assertInstanceOf(Email::class, $email);

        return $email;
    }
}
