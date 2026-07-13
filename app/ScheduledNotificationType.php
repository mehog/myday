<?php

namespace App;

enum ScheduledNotificationType: string
{
    case RsvpReminder7Days = 'rsvp_reminder_7d';
    case RsvpReminder1Day = 'rsvp_reminder_1d';
    case PreWedding1Week = 'pre_wedding_1w';
    case PreWedding1Day = 'pre_wedding_1d';
    case PhotoDay1 = 'photo_day_1';
    case PhotoDay7 = 'photo_day_7';
    case PhotoDay25 = 'photo_day_25';
    case ScheduledPush = 'scheduled_push';
    case CoupleOnboardingDay1 = 'couple_onboarding_day_1';
    case CoupleOnboardingDay3 = 'couple_onboarding_day_3';
    case CoupleOnboardingDay7 = 'couple_onboarding_day_7';
    case CoupleActivationReminder = 'couple_activation_reminder';
    case AdminInactiveWedding14Days = 'admin_inactive_wedding_14d';
    case AdminEnquiryFollowUp = 'admin_enquiry_follow_up';

    /**
     * @return array<string, int|string>
     */
    public function meta(
        ?int $weddingEventId = null,
        ?int $guestId = null,
        ?int $pushNotificationLogId = null,
        ?int $userId = null,
        ?int $enquiryId = null,
    ): array {
        return array_filter([
            'wedding_event_id' => $weddingEventId,
            'guest_id' => $guestId,
            'type' => $this->value,
            'push_notification_log_id' => $pushNotificationLogId,
            'user_id' => $userId,
            'enquiry_id' => $enquiryId,
        ], fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<int, self>
     */
    public static function coupleOnboardingTypes(): array
    {
        return [
            self::CoupleOnboardingDay1,
            self::CoupleOnboardingDay3,
            self::CoupleOnboardingDay7,
            self::CoupleActivationReminder,
        ];
    }

    /**
     * @return array<int, self>
     */
    public static function guestReminderTypes(): array
    {
        return [
            self::RsvpReminder7Days,
            self::RsvpReminder1Day,
            self::PreWedding1Week,
            self::PreWedding1Day,
            self::PhotoDay1,
            self::PhotoDay7,
            self::PhotoDay25,
        ];
    }
}
