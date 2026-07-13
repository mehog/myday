<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\Guest;
use App\Models\PushNotificationLog;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Notifications\AdminEnquiryFollowUpNotification;
use App\Notifications\AdminInactiveWeddingReminderNotification;
use App\Notifications\AdminNewEnquiryNotification;
use App\Notifications\AdminNewSignupNotification;
use App\Notifications\CoupleActivationReminderNotification;
use App\Notifications\CoupleOnboardingTipNotification;
use App\Notifications\DispatchScheduledGuestPushNotification;
use App\Notifications\GuestPhotoUploadReminderNotification;
use App\Notifications\GuestPreWeddingReminderNotification;
use App\Notifications\GuestRsvpReminderNotification;
use App\PushNotificationStatus;
use App\ScheduledNotificationType;
use App\Support\AdminNotifier;
use Carbon\CarbonInterface;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Thomasjohnkane\Snooze\Exception\SchedulingFailedException;
use Thomasjohnkane\Snooze\Models\ScheduledNotification as ScheduledNotificationModel;
use Thomasjohnkane\Snooze\ScheduledNotification;

class WeddingScheduledNotificationService
{
    public function syncEvent(WeddingEvent $event): void
    {
        $event->loadMissing('user');

        if (! $event->is_active || $event->is_demo) {
            $this->cancelAllForEvent($event);

            return;
        }

        $event->guests()->each(function (Guest $guest): void {
            $this->syncGuest($guest);
        });
    }

    public function syncGuest(Guest $guest): void
    {
        $guest->loadMissing('weddingEvent');

        $event = $guest->weddingEvent;

        if ($event === null || ! $event->is_active || $event->is_demo) {
            $this->cancelPendingForGuest($guest);

            return;
        }

        $this->cancelPendingForGuest($guest);
        $this->scheduleRsvpReminders($guest, $event);
        $this->schedulePreWeddingReminders($guest, $event);
        $this->schedulePhotoReminders($guest, $event);
    }

    public function cancelRsvpRemindersForGuest(Guest $guest): void
    {
        $this->cancelPendingForGuest($guest, ScheduledNotificationType::RsvpReminder7Days);
        $this->cancelPendingForGuest($guest, ScheduledNotificationType::RsvpReminder1Day);
    }

    public function cancelAllForEvent(WeddingEvent $event): void
    {
        $this->cancelPendingByMeta('wedding_event_id', $event->id);

        $event->pushNotificationLogs()
            ->where('status', PushNotificationStatus::Scheduled)
            ->each(function (PushNotificationLog $log): void {
                $this->cancelScheduledPush($log);
            });
    }

    public function cancelScheduledPush(PushNotificationLog $log, bool $markCancelled = true): void
    {
        $pending = ScheduledNotificationModel::query()
            ->whereNull('sent_at')
            ->whereNull('cancelled_at')
            ->where('meta->push_notification_log_id', $log->id)
            ->where('meta->type', ScheduledNotificationType::ScheduledPush->value)
            ->get();

        foreach ($pending as $model) {
            ScheduledNotification::find($model->id)?->cancel();
        }

        if ($markCancelled && $log->status === PushNotificationStatus::Scheduled) {
            $log->update(['status' => PushNotificationStatus::Failed, 'failed_reason' => __('app.push_notifications_cancelled')]);
        }
    }

    public function scheduleGuestPush(
        PushNotificationLog $log,
        User $user,
        CarbonInterface $sendAt,
        array $guestIds,
    ): void {
        $event = $log->weddingEvent;

        if ($event === null) {
            return;
        }

        $log->update([
            'guest_ids' => $guestIds,
            'scheduled_at' => $sendAt,
            'status' => PushNotificationStatus::Scheduled,
        ]);

        $this->scheduleIfFuture(
            notifiable: $user,
            notification: new DispatchScheduledGuestPushNotification($log->id),
            sendAt: $sendAt,
            meta: ScheduledNotificationType::ScheduledPush->meta(
                weddingEventId: $event->id,
                pushNotificationLogId: $log->id,
            ),
        );
    }

    public function cancelPendingForGuest(Guest $guest, ?ScheduledNotificationType $type = null): void
    {
        $query = ScheduledNotificationModel::query()
            ->whereNull('sent_at')
            ->whereNull('cancelled_at')
            ->where('target_type', Guest::class)
            ->where('target_id', $guest->id);

        if ($type !== null) {
            $query->where('meta->type', $type->value);
        }

        foreach ($query->get() as $model) {
            ScheduledNotification::find($model->id)?->cancel();
        }
    }

    public function syncCoupleOnboarding(WeddingEvent $event): void
    {
        $event->loadMissing('user');

        $user = $event->user;

        if ($user === null) {
            return;
        }

        $this->cancelCoupleOnboarding($user);

        if ($event->is_active || $event->is_demo) {
            return;
        }

        $anchor = $user->created_at->copy()->startOfDay();

        foreach (config('notifications.couple_onboarding_days', [1, 3, 7]) as $day) {
            $type = match ($day) {
                1 => ScheduledNotificationType::CoupleOnboardingDay1,
                3 => ScheduledNotificationType::CoupleOnboardingDay3,
                7 => ScheduledNotificationType::CoupleOnboardingDay7,
                default => null,
            };

            if ($type === null) {
                continue;
            }

            $this->scheduleIfFuture(
                notifiable: $user,
                notification: new CoupleOnboardingTipNotification("day{$day}"),
                sendAt: $this->daysAfter($anchor, $day),
                meta: $type->meta(weddingEventId: $event->id, userId: $user->id),
            );
        }

        $activationDay = (int) config('notifications.couple_activation_reminder_day', 10);

        $this->scheduleIfFuture(
            notifiable: $user,
            notification: new CoupleActivationReminderNotification,
            sendAt: $this->daysAfter($anchor, $activationDay),
            meta: ScheduledNotificationType::CoupleActivationReminder->meta(
                weddingEventId: $event->id,
                userId: $user->id,
            ),
        );
    }

    public function cancelCoupleOnboarding(User $user): void
    {
        foreach (ScheduledNotificationType::coupleOnboardingTypes() as $type) {
            $this->cancelPendingForUser($user, $type);
        }
    }

    public function notifyAdminsOfNewSignup(WeddingEvent $event): void
    {
        if (! AdminNotifier::hasRecipients() || $event->is_active || $event->is_demo) {
            return;
        }

        AdminNotifier::notify(new AdminNewSignupNotification($event));
    }

    public function syncAdminAlertsForEvent(WeddingEvent $event): void
    {
        $this->cancelAdminInactiveWeddingReminder($event);

        if ($event->is_active || $event->is_demo || ! AdminNotifier::hasRecipients()) {
            return;
        }

        $daysBefore = (int) config('notifications.admin_inactive_wedding_days_before', 14);
        $sendAt = $this->reminderAt($event->wedding_date->copy()->startOfDay(), daysBefore: $daysBefore);

        $notification = new AdminInactiveWeddingReminderNotification($event->id);
        $meta = ScheduledNotificationType::AdminInactiveWedding14Days->meta(weddingEventId: $event->id);

        $this->scheduleForAdmins($notification, $sendAt, $meta);
    }

    public function notifyAdminsOfNewEnquiry(Enquiry $enquiry): void
    {
        if (! AdminNotifier::hasRecipients()) {
            return;
        }

        AdminNotifier::notify(new AdminNewEnquiryNotification($enquiry));
    }

    public function scheduleEnquiryFollowUp(Enquiry $enquiry): void
    {
        if (! AdminNotifier::hasRecipients()) {
            return;
        }

        $this->cancelEnquiryFollowUp($enquiry);

        $days = (int) config('notifications.admin_enquiry_follow_up_days', 3);
        $sendAt = $this->daysAfter($enquiry->created_at->copy()->startOfDay(), $days);
        $notification = new AdminEnquiryFollowUpNotification($enquiry->id);
        $meta = ScheduledNotificationType::AdminEnquiryFollowUp->meta(enquiryId: $enquiry->id);

        $this->scheduleForAdmins($notification, $sendAt, $meta);
    }

    public function cancelAdminInactiveWeddingReminder(WeddingEvent $event): void
    {
        $this->cancelPendingByMeta('wedding_event_id', $event->id, ScheduledNotificationType::AdminInactiveWedding14Days);
    }

    public function cancelEnquiryFollowUp(Enquiry $enquiry): void
    {
        $this->cancelPendingByMeta('enquiry_id', $enquiry->id, ScheduledNotificationType::AdminEnquiryFollowUp);
    }

    private function cancelPendingForUser(User $user, ?ScheduledNotificationType $type = null): void
    {
        $query = ScheduledNotificationModel::query()
            ->whereNull('sent_at')
            ->whereNull('cancelled_at')
            ->where('target_type', User::class)
            ->where('target_id', $user->id);

        if ($type !== null) {
            $query->where('meta->type', $type->value);
        }

        foreach ($query->get() as $model) {
            ScheduledNotification::find($model->id)?->cancel();
        }
    }

    private function scheduleForAdmins(Notification $notification, CarbonInterface $sendAt, array $meta): void
    {
        foreach (AdminNotifier::recipients() as $recipient) {
            $this->scheduleIfFuture($recipient, $notification, $sendAt, $meta);
        }
    }

    private function daysAfter(CarbonInterface $anchor, int $days): Carbon
    {
        return $anchor->copy()->addDays($days)->setTime(10, 0);
    }

    private function scheduleRsvpReminders(Guest $guest, WeddingEvent $event): void
    {
        if ($event->rsvp_deadline === null || $guest->hasResponded()) {
            return;
        }

        if (! $this->guestHasDeliveryChannel($guest)) {
            return;
        }

        $deadline = $event->rsvp_deadline->copy()->startOfDay();

        $this->scheduleIfFuture(
            notifiable: $guest,
            notification: new GuestRsvpReminderNotification(7),
            sendAt: $this->reminderAt($deadline, daysBefore: 7),
            meta: ScheduledNotificationType::RsvpReminder7Days->meta($event->id, $guest->id),
        );

        $this->scheduleIfFuture(
            notifiable: $guest,
            notification: new GuestRsvpReminderNotification(1),
            sendAt: $this->reminderAt($deadline, daysBefore: 1),
            meta: ScheduledNotificationType::RsvpReminder1Day->meta($event->id, $guest->id),
        );
    }

    private function schedulePreWeddingReminders(Guest $guest, WeddingEvent $event): void
    {
        if (! $this->guestHasDeliveryChannel($guest)) {
            return;
        }

        $weddingDay = $event->wedding_date->copy()->startOfDay();

        $this->scheduleIfFuture(
            notifiable: $guest,
            notification: new GuestPreWeddingReminderNotification('week'),
            sendAt: $this->reminderAt($weddingDay, daysBefore: 7),
            meta: ScheduledNotificationType::PreWedding1Week->meta($event->id, $guest->id),
        );

        $this->scheduleIfFuture(
            notifiable: $guest,
            notification: new GuestPreWeddingReminderNotification('day'),
            sendAt: $this->reminderAt($weddingDay, daysBefore: 1),
            meta: ScheduledNotificationType::PreWedding1Day->meta($event->id, $guest->id),
        );
    }

    private function schedulePhotoReminders(Guest $guest, WeddingEvent $event): void
    {
        if (! $guest->pushSubscriptions()->exists()) {
            return;
        }

        $weddingDay = $event->wedding_date->copy()->startOfDay();

        $this->scheduleIfFuture(
            notifiable: $guest,
            notification: new GuestPhotoUploadReminderNotification('day1'),
            sendAt: $this->reminderAt($weddingDay, daysBefore: -1),
            meta: ScheduledNotificationType::PhotoDay1->meta($event->id, $guest->id),
        );

        $this->scheduleIfFuture(
            notifiable: $guest,
            notification: new GuestPhotoUploadReminderNotification('day7'),
            sendAt: $this->reminderAt($weddingDay, daysBefore: -7),
            meta: ScheduledNotificationType::PhotoDay7->meta($event->id, $guest->id),
        );

        $this->scheduleIfFuture(
            notifiable: $guest,
            notification: new GuestPhotoUploadReminderNotification('day25'),
            sendAt: $this->reminderAt($weddingDay, daysBefore: -25),
            meta: ScheduledNotificationType::PhotoDay25->meta($event->id, $guest->id),
        );
    }

    private function guestHasDeliveryChannel(Guest $guest): bool
    {
        return filled($guest->email) || $guest->pushSubscriptions()->exists();
    }

    private function reminderAt(CarbonInterface $anchorDate, int $daysBefore): Carbon
    {
        if ($daysBefore >= 0) {
            return $anchorDate->copy()->subDays($daysBefore)->setTime(10, 0);
        }

        return $anchorDate->copy()->addDays(abs($daysBefore))->setTime(10, 0);
    }

    private function scheduleIfFuture(object $notifiable, Notification $notification, CarbonInterface $sendAt, array $meta): void
    {
        if ($sendAt <= now()) {
            return;
        }

        try {
            if (method_exists($notifiable, 'notifyAt')) {
                $notifiable->notifyAt($notification, $sendAt, $meta);
            } else {
                ScheduledNotification::create($notifiable, $notification, $sendAt, $meta);
            }
        } catch (SchedulingFailedException) {
            //
        }
    }

    private function cancelPendingByMeta(string $key, int|string $value, ?ScheduledNotificationType $type = null): void
    {
        foreach (ScheduledNotification::findByMeta($key, $value) as $scheduled) {
            if ($scheduled->isSent() || $scheduled->isCancelled()) {
                continue;
            }

            if ($type !== null && $scheduled->getMeta('type') !== $type->value) {
                continue;
            }

            $scheduled->cancel();
        }
    }
}
