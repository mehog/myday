<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeddingEvent;
use App\Notifications\AdminInactiveWeddingReminderNotification;
use App\Notifications\AdminNewSignupNotification;
use App\Notifications\CoupleActivationReminderNotification;
use App\Notifications\CoupleOnboardingTipNotification;
use App\ScheduledNotificationType;
use App\Services\WeddingScheduledNotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
        $registeredAt = Carbon::parse('2026-07-01 09:00:00');
        $user = User::factory()->create(['created_at' => $registeredAt]);
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

        $this->assertScheduledAt(
            $scheduled,
            ScheduledNotificationType::CoupleOnboardingDay1,
            $registeredAt->copy()->addHours(6),
        );
        $this->assertScheduledAt(
            $scheduled,
            ScheduledNotificationType::CoupleOnboardingDay3,
            $registeredAt->copy()->addHours(18),
        );
        $this->assertScheduledAt(
            $scheduled,
            ScheduledNotificationType::CoupleOnboardingDay7,
            $registeredAt->copy()->addHours(30),
        );
        $this->assertScheduledAt(
            $scheduled,
            ScheduledNotificationType::CoupleActivationReminder,
            $registeredAt->copy()->addHours(42),
        );
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

    private function assertScheduledAt(
        Collection $scheduled,
        ScheduledNotificationType $type,
        Carbon $expectedSendAt,
    ): void {
        $row = $scheduled->first(
            fn (ScheduledNotificationModel $model): bool => data_get($model->meta, 'type') === $type->value
        );

        $this->assertNotNull($row, "Expected scheduled notification of type [{$type->value}]");
        $this->assertTrue(
            $expectedSendAt->equalTo(Carbon::parse($row->send_at)),
            "Expected [{$type->value}] at {$expectedSendAt}, got {$row->send_at}",
        );
    }
}
