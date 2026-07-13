<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\PushNotificationLog;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Notifications\DispatchScheduledGuestPushNotification;
use App\Notifications\GuestPhotoUploadReminderNotification;
use App\Notifications\GuestPreWeddingReminderNotification;
use App\Notifications\GuestRsvpReminderNotification;
use App\PushNotificationRecipientType;
use App\PushNotificationStatus;
use App\RsvpStatus;
use App\ScheduledNotificationType;
use App\Services\WeddingScheduledNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Thomasjohnkane\Snooze\Models\ScheduledNotification as ScheduledNotificationModel;

class WeddingScheduledNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private WeddingScheduledNotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(WeddingScheduledNotificationService::class);
        Carbon::setTestNow('2026-07-01 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_schedules_rsvp_reminders_for_unanswered_guests(): void
    {
        $event = WeddingEvent::factory()->create([
            'rsvp_deadline' => '2026-08-15',
            'is_active' => true,
        ]);

        $guest = Guest::withoutEvents(fn () => Guest::factory()->for($event)->create([
            'email' => 'guest@example.com',
            'rsvp_status' => null,
        ]));

        $this->service->syncGuest($guest);

        $scheduled = ScheduledNotificationModel::query()
            ->where('target_type', Guest::class)
            ->where('target_id', $guest->id)
            ->where('notification_type', GuestRsvpReminderNotification::class)
            ->get();

        $this->assertCount(2, $scheduled);
        $this->assertTrue($scheduled->contains(
            fn (ScheduledNotificationModel $row): bool => $row->meta['type'] === ScheduledNotificationType::RsvpReminder7Days->value
        ));
        $this->assertTrue($scheduled->contains(
            fn (ScheduledNotificationModel $row): bool => $row->meta['type'] === ScheduledNotificationType::RsvpReminder1Day->value
        ));
    }

    public function test_it_cancels_rsvp_reminders_when_guest_responds(): void
    {
        $event = WeddingEvent::factory()->create([
            'rsvp_deadline' => '2026-08-15',
            'is_active' => true,
        ]);

        $guest = Guest::withoutEvents(fn () => Guest::factory()->for($event)->create([
            'email' => 'guest@example.com',
        ]));

        $this->service->syncGuest($guest);
        $this->assertSame(1, $this->pendingCountForGuest($guest, ScheduledNotificationType::RsvpReminder7Days->value));
        $this->assertSame(1, $this->pendingCountForGuest($guest, ScheduledNotificationType::RsvpReminder1Day->value));

        $guest->update(['rsvp_status' => RsvpStatus::Yes, 'rsvp_responded_at' => now()]);

        $this->assertSame(0, $this->pendingCountForGuest($guest, ScheduledNotificationType::RsvpReminder7Days->value));
        $this->assertSame(0, $this->pendingCountForGuest($guest, ScheduledNotificationType::RsvpReminder1Day->value));
    }

    public function test_it_reschedules_reminders_when_rsvp_deadline_changes(): void
    {
        $event = WeddingEvent::factory()->create([
            'rsvp_deadline' => '2026-08-15',
            'is_active' => true,
        ]);

        $guest = Guest::withoutEvents(fn () => Guest::factory()->for($event)->create([
            'email' => 'guest@example.com',
        ]));

        $this->service->syncGuest($guest);
        $originalSendAt = ScheduledNotificationModel::query()
            ->where('meta->type', ScheduledNotificationType::RsvpReminder1Day->value)
            ->value('send_at');

        $event->update(['rsvp_deadline' => '2026-08-20']);

        $newSendAt = ScheduledNotificationModel::query()
            ->where('meta->type', ScheduledNotificationType::RsvpReminder1Day->value)
            ->value('send_at');

        $this->assertNotSame($originalSendAt, $newSendAt);
    }

    public function test_it_cancels_pending_notifications_when_event_is_inactive(): void
    {
        $event = WeddingEvent::factory()->create([
            'rsvp_deadline' => '2026-08-15',
            'is_active' => true,
        ]);

        $guest = Guest::withoutEvents(fn () => Guest::factory()->for($event)->create([
            'email' => 'guest@example.com',
        ]));

        $this->service->syncGuest($guest);
        $this->assertGreaterThan(0, $this->pendingCountForGuest($guest));

        $event->update(['is_active' => false]);

        $this->assertSame(0, $this->pendingCountForGuest($guest));
    }

    public function test_it_schedules_pre_wedding_reminders(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => '2026-10-01 16:00:00',
            'is_active' => true,
        ]);

        $guest = Guest::withoutEvents(fn () => Guest::factory()->for($event)->create([
            'email' => 'guest@example.com',
        ]));

        $this->service->syncGuest($guest);

        $types = ScheduledNotificationModel::query()
            ->where('target_id', $guest->id)
            ->pluck('meta')
            ->map(fn (array $meta): string => $meta['type'])
            ->all();

        $this->assertContains(ScheduledNotificationType::PreWedding1Week->value, $types);
        $this->assertContains(ScheduledNotificationType::PreWedding1Day->value, $types);
        $this->assertTrue(
            ScheduledNotificationModel::query()
                ->where('notification_type', GuestPreWeddingReminderNotification::class)
                ->exists()
        );
    }

    public function test_it_schedules_photo_reminders_for_push_subscribers(): void
    {
        $event = WeddingEvent::factory()->create([
            'wedding_date' => '2026-10-01 16:00:00',
            'is_active' => true,
        ]);

        $guest = Guest::factory()->for($event)->create();
        $guest->updatePushSubscription(
            endpoint: 'https://push.example.com/'.fake()->uuid(),
            key: base64_encode(random_bytes(32)),
            token: base64_encode(random_bytes(16)),
        );

        $this->service->syncGuest($guest->fresh());

        $photoTypes = ScheduledNotificationModel::query()
            ->where('target_id', $guest->id)
            ->where('notification_type', GuestPhotoUploadReminderNotification::class)
            ->pluck('meta')
            ->map(fn (array $meta): string => $meta['type'])
            ->all();

        $this->assertContains(ScheduledNotificationType::PhotoDay1->value, $photoTypes);
        $this->assertContains(ScheduledNotificationType::PhotoDay7->value, $photoTypes);
        $this->assertContains(ScheduledNotificationType::PhotoDay25->value, $photoTypes);
    }

    public function test_it_schedules_couple_guest_push_for_later(): void
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create(['is_active' => true]);

        $log = PushNotificationLog::query()->create([
            'wedding_event_id' => $event->id,
            'title' => 'Hello',
            'body' => 'See you soon',
            'recipient_type' => PushNotificationRecipientType::All,
            'sent_to_count' => 2,
            'guest_ids' => [1, 2],
            'status' => PushNotificationStatus::Queued,
        ]);

        $sendAt = now()->addDay();

        $this->service->scheduleGuestPush($log, $user, $sendAt, [1, 2]);

        $log->refresh();

        $this->assertSame(PushNotificationStatus::Scheduled, $log->status);
        $this->assertTrue($log->scheduled_at->equalTo($sendAt));

        $scheduled = ScheduledNotificationModel::query()
            ->where('target_type', User::class)
            ->where('target_id', $user->id)
            ->where('notification_type', DispatchScheduledGuestPushNotification::class)
            ->first();

        $this->assertNotNull($scheduled);
        $this->assertSame(ScheduledNotificationType::ScheduledPush->value, $scheduled->meta['type']);
        $this->assertSame($log->id, $scheduled->meta['push_notification_log_id']);
    }

    public function test_it_cancels_scheduled_push_when_log_is_deleted(): void
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create(['is_active' => true]);

        $log = PushNotificationLog::query()->create([
            'wedding_event_id' => $event->id,
            'title' => 'Hello',
            'body' => 'See you soon',
            'recipient_type' => PushNotificationRecipientType::All,
            'sent_to_count' => 1,
            'guest_ids' => [1],
            'status' => PushNotificationStatus::Queued,
        ]);

        $this->service->scheduleGuestPush($log, $user, now()->addDay(), [1]);

        $log->delete();

        $this->assertSame(
            1,
            ScheduledNotificationModel::query()
                ->whereNotNull('cancelled_at')
                ->where('meta->push_notification_log_id', $log->id)
                ->count()
        );
    }

    private function pendingCountForGuest(Guest $guest, ?string $type = null): int
    {
        $query = ScheduledNotificationModel::query()
            ->whereNull('sent_at')
            ->whereNull('cancelled_at')
            ->where('target_type', Guest::class)
            ->where('target_id', $guest->id);

        if ($type !== null) {
            $query->where('meta->type', $type);
        }

        return $query->count();
    }
}
