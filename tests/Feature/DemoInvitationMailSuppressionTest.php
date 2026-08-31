<?php

namespace Tests\Feature;

use App\DiscountEmailAudience;
use App\DiscountEmailCampaignStatus;
use App\DiscountEmailRecipientStatus;
use App\DiscountType;
use App\Jobs\SendDiscountCampaignEmailsJob;
use App\Listeners\PreventDemoInvitationMail;
use App\Models\DiscountCode;
use App\Models\DiscountEmailCampaign;
use App\Models\DiscountEmailRecipient;
use App\Models\DiscountEmailTemplate;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Notifications\CoupleActivationReminderNotification;
use App\Notifications\CoupleOnboardingTipNotification;
use App\Notifications\DiscountCodeEmailNotification;
use App\Notifications\GuestRsvpReminderNotification;
use App\PlanTier;
use App\Services\DiscountCampaignAudienceResolver;
use App\Services\WeddingScheduledNotificationService;
use Database\Seeders\DiscountEmailTemplateSeeder;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mime\Email;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;
use Thomasjohnkane\Snooze\Models\ScheduledNotification as ScheduledNotificationModel;

class DemoInvitationMailSuppressionTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_mail_listener_cancels_messages_to_demo_guests(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_demo' => true,
            'is_active' => true,
        ]);
        Guest::factory()->for($event)->create([
            'email' => 'demo-guest@example.com',
        ]);

        $message = (new Email)
            ->to('demo-guest@example.com')
            ->from('noreply@example.com')
            ->subject('RSVP reminder')
            ->text('Please respond');

        $result = app(PreventDemoInvitationMail::class)->handle(new MessageSending($message));

        $this->assertFalse($result);
    }

    public function test_mail_listener_cancels_messages_to_demo_owners(): void
    {
        $user = User::factory()->create(['email' => 'demo-owner@example.com']);
        WeddingEvent::factory()->for($user)->create([
            'is_demo' => true,
            'is_active' => false,
        ]);

        $message = (new Email)
            ->to('demo-owner@example.com')
            ->from('noreply@example.com')
            ->subject('Tip')
            ->text('Onboarding tip');

        $result = app(PreventDemoInvitationMail::class)->handle(new MessageSending($message));

        $this->assertFalse($result);
    }

    public function test_mail_listener_allows_non_demo_recipients(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_demo' => false,
            'is_marketing' => false,
            'is_active' => true,
        ]);
        Guest::factory()->for($event)->create([
            'email' => 'real-guest@example.com',
        ]);

        $message = (new Email)
            ->to('real-guest@example.com')
            ->from('noreply@example.com')
            ->subject('RSVP reminder')
            ->text('Please respond');

        $result = app(PreventDemoInvitationMail::class)->handle(new MessageSending($message));

        $this->assertNull($result);
    }

    public function test_mail_listener_cancels_messages_to_marketing_guests(): void
    {
        $event = WeddingEvent::factory()->marketing()->create([
            'is_active' => true,
        ]);
        Guest::factory()->for($event)->create([
            'email' => 'marketing-guest@example.com',
        ]);

        $message = (new Email)
            ->to('marketing-guest@example.com')
            ->from('noreply@example.com')
            ->subject('RSVP reminder')
            ->text('Please respond');

        $result = app(PreventDemoInvitationMail::class)->handle(new MessageSending($message));

        $this->assertFalse($result);
    }

    public function test_mail_listener_cancels_messages_to_marketing_owners(): void
    {
        $user = User::factory()->create(['email' => 'marketing-owner@example.com']);
        WeddingEvent::factory()->marketing()->for($user)->create([
            'is_active' => true,
        ]);

        $message = (new Email)
            ->to('marketing-owner@example.com')
            ->from('noreply@example.com')
            ->subject('Tip')
            ->text('Onboarding tip');

        $result = app(PreventDemoInvitationMail::class)->handle(new MessageSending($message));

        $this->assertFalse($result);
    }

    public function test_marketing_wedding_suppresses_mail_without_being_demo(): void
    {
        $event = WeddingEvent::factory()->marketing()->create();

        $this->assertTrue($event->suppressesOutboundMail());
        $this->assertFalse($event->is_demo);
        $this->assertTrue($event->is_marketing);
    }

    public function test_scheduled_guest_reminders_are_not_created_for_demo_events(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_demo' => true,
            'is_active' => true,
            'rsvp_deadline' => now()->addWeeks(3)->toDateString(),
            'wedding_date' => now()->addMonths(2),
        ]);
        $guest = Guest::factory()->for($event)->create([
            'email' => 'demo-scheduled@example.com',
            'rsvp_status' => null,
        ]);

        app(WeddingScheduledNotificationService::class)->syncGuest($guest);

        $this->assertSame(0, ScheduledNotificationModel::query()
            ->where('target_type', Guest::class)
            ->where('target_id', $guest->id)
            ->whereNull('cancelled_at')
            ->count());
    }

    public function test_scheduled_guest_reminders_are_not_created_for_marketing_events(): void
    {
        $event = WeddingEvent::factory()->marketing()->create([
            'is_active' => true,
            'rsvp_deadline' => now()->addWeeks(3)->toDateString(),
            'wedding_date' => now()->addMonths(2),
        ]);
        $guest = Guest::factory()->for($event)->create([
            'email' => 'marketing-scheduled@example.com',
            'rsvp_status' => null,
        ]);

        app(WeddingScheduledNotificationService::class)->syncGuest($guest);

        $this->assertSame(0, ScheduledNotificationModel::query()
            ->where('target_type', Guest::class)
            ->where('target_id', $guest->id)
            ->whereNull('cancelled_at')
            ->count());
    }

    public function test_flipping_is_marketing_cancels_pending_guest_reminders(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_demo' => false,
            'is_marketing' => false,
            'is_active' => true,
            'rsvp_deadline' => now()->addWeeks(3)->toDateString(),
            'wedding_date' => now()->addMonths(2),
        ]);
        $guest = Guest::factory()->for($event)->create([
            'email' => 'will-be-marketing@example.com',
            'rsvp_status' => null,
        ]);

        app(WeddingScheduledNotificationService::class)->syncGuest($guest);

        $this->assertGreaterThan(0, ScheduledNotificationModel::query()
            ->where('target_type', Guest::class)
            ->where('target_id', $guest->id)
            ->whereNull('cancelled_at')
            ->count());

        $event->update(['is_marketing' => true]);

        $this->assertSame(0, ScheduledNotificationModel::query()
            ->where('target_type', Guest::class)
            ->where('target_id', $guest->id)
            ->whereNull('cancelled_at')
            ->count());
    }

    public function test_couple_onboarding_interrupt_for_demo_owners(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->for($user)->create([
            'is_demo' => true,
            'is_active' => false,
        ]);

        $this->assertTrue((new CoupleOnboardingTipNotification('day1'))->shouldInterrupt($user));
        $this->assertTrue((new CoupleActivationReminderNotification)->shouldInterrupt($user));
    }

    public function test_couple_onboarding_interrupt_for_marketing_owners(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->marketing()->for($user)->create([
            'is_active' => true,
        ]);

        $this->assertTrue($user->ownsDemoInvitation());
        $this->assertTrue((new CoupleOnboardingTipNotification('day1'))->shouldInterrupt($user));
    }

    public function test_couple_onboarding_does_not_interrupt_active_non_demo_owners(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->for($user)->create([
            'is_demo' => false,
            'is_marketing' => false,
            'is_active' => true,
            'plan_tier' => PlanTier::Free,
        ]);

        $this->assertFalse((new CoupleOnboardingTipNotification('day1'))->shouldInterrupt($user));
    }

    public function test_backfill_couple_onboarding_dry_run_writes_nothing(): void
    {
        $user = User::factory()->create(['created_at' => now()->subDays(10)]);
        WeddingEvent::factory()->for($user)->create([
            'is_active' => true,
            'plan_tier' => PlanTier::Free,
            'wedding_date' => now()->addMonths(3),
        ]);

        $this->artisan('notifications:backfill-couple-onboarding', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertSame(0, ScheduledNotificationModel::query()
            ->where('target_type', User::class)
            ->where('target_id', $user->id)
            ->count());
    }

    public function test_backfill_couple_onboarding_skips_demo_marketing_and_archived(): void
    {
        $realUser = User::factory()->create(['created_at' => now()->subDays(10)]);
        WeddingEvent::factory()->for($realUser)->create([
            'is_active' => true,
            'plan_tier' => PlanTier::Free,
            'wedding_date' => now()->addMonths(3),
        ]);

        $demoUser = User::factory()->create(['created_at' => now()->subDays(10)]);
        WeddingEvent::factory()->for($demoUser)->create([
            'is_demo' => true,
            'is_active' => true,
            'wedding_date' => now()->addMonths(3),
        ]);

        $marketingUser = User::factory()->create(['created_at' => now()->subDays(10)]);
        WeddingEvent::factory()->marketing()->for($marketingUser)->create([
            'is_active' => true,
            'wedding_date' => now()->addMonths(3),
        ]);

        $archivedUser = User::factory()->create(['created_at' => now()->subDays(10)]);
        WeddingEvent::factory()->for($archivedUser)->create([
            'is_active' => true,
            'plan_tier' => PlanTier::Free,
            'wedding_date' => now()->subDays(2),
        ]);

        $this->artisan('notifications:backfill-couple-onboarding')
            ->assertSuccessful();

        $this->assertSame(3, ScheduledNotificationModel::query()
            ->whereNull('cancelled_at')
            ->where('target_type', User::class)
            ->where('target_id', $realUser->id)
            ->count());

        $this->assertSame(0, ScheduledNotificationModel::query()
            ->whereNull('cancelled_at')
            ->where('target_type', User::class)
            ->where('target_id', $demoUser->id)
            ->count());

        $this->assertSame(0, ScheduledNotificationModel::query()
            ->whereNull('cancelled_at')
            ->where('target_type', User::class)
            ->where('target_id', $marketingUser->id)
            ->count());

        $this->assertSame(0, ScheduledNotificationModel::query()
            ->whereNull('cancelled_at')
            ->where('target_type', User::class)
            ->where('target_id', $archivedUser->id)
            ->count());
    }

    public function test_discount_audiences_exclude_demo_invitation_owners(): void
    {
        $this->seed(DiscountEmailTemplateSeeder::class);

        $demoPaid = User::factory()->create();
        WeddingEvent::factory()->for($demoPaid)->create([
            'is_demo' => true,
            'plan_tier' => PlanTier::Premium,
            'is_active' => true,
        ]);

        $realPaid = User::factory()->create();
        WeddingEvent::factory()->for($realPaid)->create([
            'is_demo' => false,
            'is_marketing' => false,
            'plan_tier' => PlanTier::Premium,
            'is_active' => true,
        ]);

        $campaign = $this->makeCampaign([
            'audience' => DiscountEmailAudience::Paid,
        ]);

        $users = app(DiscountCampaignAudienceResolver::class)->resolve($campaign);

        $this->assertTrue($users->contains('id', $realPaid->id));
        $this->assertFalse($users->contains('id', $demoPaid->id));

        $allCampaign = $this->makeCampaign([
            'audience' => DiscountEmailAudience::All,
        ]);

        $allUsers = app(DiscountCampaignAudienceResolver::class)->resolve($allCampaign);
        $this->assertFalse($allUsers->contains('id', $demoPaid->id));
        $this->assertTrue($allUsers->contains('id', $realPaid->id));
    }

    public function test_discount_audiences_exclude_marketing_invitation_owners(): void
    {
        $this->seed(DiscountEmailTemplateSeeder::class);

        $marketingPaid = User::factory()->create();
        WeddingEvent::factory()->marketing()->for($marketingPaid)->create([
            'plan_tier' => PlanTier::Premium,
            'is_active' => true,
        ]);

        $realPaid = User::factory()->create();
        WeddingEvent::factory()->for($realPaid)->create([
            'is_demo' => false,
            'is_marketing' => false,
            'plan_tier' => PlanTier::Premium,
            'is_active' => true,
        ]);

        $campaign = $this->makeCampaign([
            'audience' => DiscountEmailAudience::Paid,
        ]);

        $users = app(DiscountCampaignAudienceResolver::class)->resolve($campaign);

        $this->assertTrue($users->contains('id', $realPaid->id));
        $this->assertFalse($users->contains('id', $marketingPaid->id));
    }

    public function test_discount_send_job_skips_demo_invitation_owners(): void
    {
        Notification::fake();
        $this->seed(DiscountEmailTemplateSeeder::class);

        $demoUser = User::factory()->create();
        WeddingEvent::factory()->for($demoUser)->create([
            'is_demo' => true,
            'plan_tier' => PlanTier::Basic,
        ]);

        $campaign = $this->makeCampaign([
            'audience' => DiscountEmailAudience::Manual,
            'user_ids' => [$demoUser->id],
            'status' => DiscountEmailCampaignStatus::Sending,
        ]);

        // Simulate a recipient that was materialized before demo exclusion.
        DiscountEmailRecipient::query()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $demoUser->id,
            'email' => $demoUser->email,
            'status' => DiscountEmailRecipientStatus::Pending,
        ]);

        (new SendDiscountCampaignEmailsJob($campaign->id))->handle();

        $recipient = DiscountEmailRecipient::query()
            ->where('campaign_id', $campaign->id)
            ->where('user_id', $demoUser->id)
            ->first();

        $this->assertNotNull($recipient);
        $this->assertSame(DiscountEmailRecipientStatus::Skipped, $recipient->status);
        $this->assertSame('Demo invitation', $recipient->error);
        Notification::assertNotSentTo($demoUser, DiscountCodeEmailNotification::class);
    }

    public function test_non_demo_guest_notifications_are_still_dispatched(): void
    {
        Notification::fake();

        $event = WeddingEvent::factory()->create([
            'is_demo' => false,
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create([
            'email' => 'real-guest@example.com',
        ]);

        $guest->notify(new GuestRsvpReminderNotification(7));

        Notification::assertSentTo($guest, GuestRsvpReminderNotification::class);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeCampaign(array $overrides = []): DiscountEmailCampaign
    {
        $code = DiscountCode::query()->create([
            'code' => 'TEST'.fake()->unique()->numerify('###'),
            'name' => 'Test code',
            'type' => DiscountType::Percentage,
            'amount' => 15,
            'is_active' => true,
        ]);

        $template = DiscountEmailTemplate::query()->first()
            ?? DiscountEmailTemplate::query()->create([
                'name' => 'Default',
                'slug' => 'default-'.fake()->unique()->numerify('###'),
                'subject' => ['en' => 'Offer {{code}}'],
                'body' => ['en' => 'Code: {{code}}'],
            ]);

        return DiscountEmailCampaign::query()->create(array_merge([
            'discount_code_id' => $code->id,
            'discount_email_template_id' => $template->id,
            'audience' => DiscountEmailAudience::UnpaidVerified,
            'status' => DiscountEmailCampaignStatus::Draft,
        ], $overrides));
    }
}
