<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Notifications\AdminEnquiryFollowUpNotification;
use App\Notifications\AdminInactiveWeddingReminderNotification;
use App\Notifications\AdminNewEnquiryNotification;
use App\Notifications\AdminNewSignupNotification;
use App\Notifications\CoupleActivationReminderNotification;
use App\Notifications\CoupleOnboardingTipNotification;
use App\ScheduledNotificationType;
use App\Services\WeddingScheduledNotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;
use Thomasjohnkane\Snooze\Models\ScheduledNotification as ScheduledNotificationModel;

class CoupleAndAdminScheduledNotificationTest extends TestCase
{
    use RefreshInMemoryDatabase;

    private WeddingScheduledNotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(WeddingScheduledNotificationService::class);
        Carbon::setTestNow('2026-07-01 09:00:00');

        User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_schedules_couple_onboarding_drip_for_inactive_wedding(): void
    {
        $user = User::factory()->create(['created_at' => now()]);
        $event = WeddingEvent::withoutEvents(fn () => WeddingEvent::factory()->for($user)->create([
            'is_active' => false,
            'wedding_date' => now()->addMonths(4),
        ]));

        $this->service->syncCoupleOnboarding($event);

        $scheduled = ScheduledNotificationModel::query()
            ->where('target_type', User::class)
            ->where('target_id', $user->id)
            ->get();

        $this->assertCount(4, $scheduled);
        $this->assertTrue($scheduled->contains(
            fn (ScheduledNotificationModel $row): bool => $row->notification_type === CoupleOnboardingTipNotification::class
        ));
        $this->assertTrue($scheduled->contains(
            fn (ScheduledNotificationModel $row): bool => $row->notification_type === CoupleActivationReminderNotification::class
        ));
    }

    public function test_it_cancels_couple_onboarding_when_wedding_is_activated(): void
    {
        $user = User::factory()->create(['created_at' => now()]);
        $event = WeddingEvent::withoutEvents(fn () => WeddingEvent::factory()->for($user)->create([
            'is_active' => false,
            'wedding_date' => now()->addMonths(4),
        ]));

        $this->service->syncCoupleOnboarding($event);
        $this->assertSame(4, $this->pendingCountForUser($user));

        $event->update(['is_active' => true]);

        $this->assertSame(0, $this->pendingCountForUser($user));
    }

    public function test_it_schedules_admin_inactive_wedding_reminder(): void
    {
        $event = WeddingEvent::withoutEvents(fn () => WeddingEvent::factory()->create([
            'is_active' => false,
            'wedding_date' => '2026-10-15 16:00:00',
        ]));

        $this->service->syncAdminAlertsForEvent($event);

        $this->assertTrue(
            ScheduledNotificationModel::query()
                ->where('notification_type', AdminInactiveWeddingReminderNotification::class)
                ->where('meta->type', ScheduledNotificationType::AdminInactiveWedding14Days->value)
                ->exists()
        );
    }

    public function test_it_schedules_admin_enquiry_follow_up(): void
    {
        $enquiry = Enquiry::withoutEvents(fn () => Enquiry::factory()->create([
            'created_at' => now(),
        ]));

        $this->service->scheduleEnquiryFollowUp($enquiry);

        $this->assertTrue(
            ScheduledNotificationModel::query()
                ->where('notification_type', AdminEnquiryFollowUpNotification::class)
                ->where('meta->enquiry_id', $enquiry->id)
                ->exists()
        );
    }

    public function test_enquiry_observer_notifies_admins_instantly_and_schedules_follow_up(): void
    {
        Notification::fake();

        $admin = User::query()->where('is_admin', true)->firstOrFail();
        $enquiry = Enquiry::factory()->create();

        Notification::assertSentTo($admin, AdminNewEnquiryNotification::class);

        $this->assertTrue(
            ScheduledNotificationModel::query()
                ->where('notification_type', AdminEnquiryFollowUpNotification::class)
                ->where('meta->enquiry_id', $enquiry->id)
                ->exists()
        );
    }

    public function test_wedding_event_observer_notifies_admins_of_new_signup(): void
    {
        Notification::fake();

        $admin = User::query()->where('is_admin', true)->firstOrFail();
        $user = User::factory()->create();
        WeddingEvent::factory()->for($user)->create([
            'is_active' => false,
            'wedding_date' => now()->addMonths(4),
        ]);

        Notification::assertSentTo($admin, AdminNewSignupNotification::class);
    }

    private function pendingCountForUser(User $user): int
    {
        return ScheduledNotificationModel::query()
            ->whereNull('sent_at')
            ->whereNull('cancelled_at')
            ->where('target_type', User::class)
            ->where('target_id', $user->id)
            ->count();
    }
}
