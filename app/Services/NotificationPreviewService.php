<?php

namespace App\Services;

use App\Models\PushNotificationLog;
use App\Notifications\AdminEnquiryFollowUpNotification;
use App\Notifications\AdminInactiveWeddingReminderNotification;
use App\Notifications\AdminNewEnquiryNotification;
use App\Notifications\AdminNewSignupNotification;
use App\Notifications\CoupleActivationReminderNotification;
use App\Notifications\CoupleOnboardingTipNotification;
use App\Notifications\CoupleRsvpPushNotification;
use App\Notifications\GuestPhotoUploadReminderNotification;
use App\Notifications\GuestPreWeddingReminderNotification;
use App\Notifications\GuestPushNotification;
use App\Notifications\GuestRsvpReminderNotification;
use App\Notifications\Preview\PushMirrorPreviewNotification;
use App\PushNotificationRecipientType;
use App\PushNotificationStatus;
use App\RsvpStatus;
use App\Support\Locale;
use Closure;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use NotificationChannels\WebPush\WebPushChannel;
use RuntimeException;

final class NotificationPreviewService
{
    /**
     * @return array<int, array{id: string, label: string, group: string, channel: string, skipped: bool, skip_reason: string|null}>
     */
    public function listScenarios(): array
    {
        return array_map(
            fn (array $scenario): array => [
                'id' => $scenario['id'],
                'label' => $scenario['label'],
                'group' => $scenario['group'],
                'channel' => $scenario['channel'],
                'skipped' => $scenario['skipped'] ?? false,
                'skip_reason' => $scenario['skip_reason'] ?? null,
            ],
            $this->scenarios(),
        );
    }

    /**
     * @param  array<string, int|null>  $fixtureIds
     * @return array<int, string> Sent scenario labels
     */
    public function sendAll(
        string $to,
        ?string $only = null,
        array $fixtureIds = [],
        ?string $locale = null,
        ?int $logId = null,
        int $delaySeconds = 0,
    ): array {
        $fixtures = NotificationPreviewFixtures::resolve($fixtureIds);
        $fixtures->applyLocale($locale);

        if ($locale !== null && Locale::isSupported($locale)) {
            Locale::apply($locale);
        }

        $groups = $this->parseGroups($only);
        $sent = [];

        foreach ($this->scenarios() as $scenario) {
            if ($scenario['skipped'] ?? false) {
                continue;
            }

            if (! in_array($scenario['group'], $groups, true) && ! in_array('all', $groups, true)) {
                continue;
            }

            if ($sent !== [] && $delaySeconds > 0) {
                sleep($delaySeconds);
            }

            if ($scenario['id'] === 'scheduled-guest-push') {
                $this->sendScheduledGuestPush($fixtures, $to, $logId);
                $sent[] = $scenario['label'];

                continue;
            }

            $notification = ($scenario['factory'])($fixtures);

            match ($scenario['channel']) {
                'mail' => $this->sendMail($fixtures, $to, $notification, $scenario['target']),
                'push-mirror' => $this->sendPushMirror($to, $notification, $fixtures, $scenario['label'], $scenario['target']),
                'admin' => NotificationFacade::route('mail', $to)->notifyNow($notification),
                default => null,
            };

            $sent[] = $scenario['label'];
        }

        return $sent;
    }

    /**
     * @return array<int, array{id: string, label: string, group: string, channel: string, target: string, skipped?: bool, skip_reason?: string, factory?: Closure}>
     */
    public function scenarios(): array
    {
        return [
            ...$this->onboardingScenarios(),
            ...$this->activationScenarios(),
            ...$this->rsvpScenarios(),
            ...$this->preWeddingScenarios(),
            ...$this->photoScenarios(),
            ...$this->guestPushScenarios(),
            ...$this->coupleRsvpScenarios(),
            ...$this->adminScenarios(),
            ...$this->scheduledPushScenarios(),
            [
                'id' => 'new-guest-message',
                'label' => 'New guest message (Filament inbox)',
                'group' => 'database',
                'channel' => 'database',
                'target' => 'user',
                'skipped' => true,
                'skip_reason' => 'Database channel only — visible in Filament app inbox, not email.',
            ],
        ];
    }

    /**
     * Build a notification instance for smoke tests or previews.
     */
    public function buildNotification(string $id, NotificationPreviewFixtures $fixtures): Notification
    {
        foreach ($this->scenarios() as $scenario) {
            if ($scenario['id'] !== $id) {
                continue;
            }

            if ($scenario['skipped'] ?? false) {
                throw new RuntimeException("Scenario [{$id}] is not previewable.");
            }

            if (! isset($scenario['factory'])) {
                throw new RuntimeException("Scenario [{$id}] has no factory.");
            }

            return ($scenario['factory'])($fixtures);
        }

        throw new RuntimeException("Unknown preview scenario [{$id}].");
    }

    public function targetFor(string $id): string
    {
        foreach ($this->scenarios() as $scenario) {
            if ($scenario['id'] === $id) {
                return $scenario['target'];
            }
        }

        throw new RuntimeException("Unknown preview scenario [{$id}].");
    }

    public function channelFor(string $id): string
    {
        foreach ($this->scenarios() as $scenario) {
            if ($scenario['id'] === $id) {
                return $scenario['channel'];
            }
        }

        throw new RuntimeException("Unknown preview scenario [{$id}].");
    }

    /**
     * @return array<int, string>
     */
    public function previewableScenarioIds(): array
    {
        return array_values(array_map(
            fn (array $scenario): string => $scenario['id'],
            array_filter($this->scenarios(), fn (array $scenario): bool => ! ($scenario['skipped'] ?? false)),
        ));
    }

    /**
     * @return array<int, string>
     */
    private function parseGroups(?string $only): array
    {
        if ($only === null || $only === '' || $only === 'all') {
            return ['all'];
        }

        return array_map(trim(...), explode(',', $only));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function onboardingScenarios(): array
    {
        return array_map(
            fn (int $day): array => [
                'id' => "couple-onboarding-day{$day}",
                'label' => "Couple onboarding — day {$day}",
                'group' => 'onboarding',
                'channel' => 'mail',
                'target' => 'user',
                'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new CoupleOnboardingTipNotification("day{$day}"),
            ],
            config('notifications.couple_onboarding_days', [1, 3, 7]),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function activationScenarios(): array
    {
        return [[
            'id' => 'couple-activation',
            'label' => 'Couple activation reminder',
            'group' => 'activation',
            'channel' => 'mail',
            'target' => 'user',
            'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new CoupleActivationReminderNotification,
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rsvpScenarios(): array
    {
        return [
            [
                'id' => 'guest-rsvp-7d',
                'label' => 'Guest RSVP reminder — 7 days',
                'group' => 'rsvp',
                'channel' => 'mail',
                'target' => 'guest',
                'factory' => function (NotificationPreviewFixtures $fixtures): Notification {
                    $fixtures->wedding->rsvp_deadline = now()->addDays(7)->startOfDay();
                    $fixtures->guest->setRelation('weddingEvent', $fixtures->wedding);

                    return new GuestRsvpReminderNotification(7);
                },
            ],
            [
                'id' => 'guest-rsvp-1d',
                'label' => 'Guest RSVP reminder — 1 day',
                'group' => 'rsvp',
                'channel' => 'mail',
                'target' => 'guest',
                'factory' => function (NotificationPreviewFixtures $fixtures): Notification {
                    $fixtures->wedding->rsvp_deadline = now()->addDay()->startOfDay();
                    $fixtures->guest->setRelation('weddingEvent', $fixtures->wedding);

                    return new GuestRsvpReminderNotification(1);
                },
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function preWeddingScenarios(): array
    {
        return [
            [
                'id' => 'guest-pre-wedding-week',
                'label' => 'Guest pre-wedding — 1 week',
                'group' => 'pre-wedding',
                'channel' => 'mail',
                'target' => 'guest',
                'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new GuestPreWeddingReminderNotification('week'),
            ],
            [
                'id' => 'guest-pre-wedding-day',
                'label' => 'Guest pre-wedding — 1 day',
                'group' => 'pre-wedding',
                'channel' => 'mail',
                'target' => 'guest',
                'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new GuestPreWeddingReminderNotification('day'),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function photoScenarios(): array
    {
        return [
            [
                'id' => 'guest-photo-day1',
                'label' => 'Guest photo reminder — day 1',
                'group' => 'photo',
                'channel' => 'push-mirror',
                'target' => 'guest',
                'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new GuestPhotoUploadReminderNotification('day1'),
            ],
            [
                'id' => 'guest-photo-day7',
                'label' => 'Guest photo reminder — day 7',
                'group' => 'photo',
                'channel' => 'push-mirror',
                'target' => 'guest',
                'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new GuestPhotoUploadReminderNotification('day7'),
            ],
            [
                'id' => 'guest-photo-day25',
                'label' => 'Guest photo reminder — day 25',
                'group' => 'photo',
                'channel' => 'push-mirror',
                'target' => 'guest',
                'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new GuestPhotoUploadReminderNotification('day25'),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function guestPushScenarios(): array
    {
        return [[
            'id' => 'guest-push-composed',
            'label' => 'Guest push — couple composed',
            'group' => 'guest-push',
            'channel' => 'push-mirror',
            'target' => 'guest',
            'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new GuestPushNotification(
                title: 'Preview push title',
                body: 'Preview push body from the couple.',
                url: route('invitation.push.guest', [$fixtures->wedding->slug, $fixtures->guest->token]),
            ),
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function coupleRsvpScenarios(): array
    {
        return [[
            'id' => 'couple-rsvp-push',
            'label' => 'Couple RSVP push',
            'group' => 'couple-rsvp',
            'channel' => 'push-mirror',
            'target' => 'user',
            'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new CoupleRsvpPushNotification(
                guestName: $fixtures->guest->name,
                rsvpStatus: RsvpStatus::Yes,
                rsvpNote: 'Looking forward to it!',
            ),
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function adminScenarios(): array
    {
        return [
            [
                'id' => 'admin-new-enquiry',
                'label' => 'Admin — new enquiry',
                'group' => 'admin',
                'channel' => 'admin',
                'target' => 'admin',
                'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new AdminNewEnquiryNotification($fixtures->enquiry),
            ],
            [
                'id' => 'admin-new-signup',
                'label' => 'Admin — new signup',
                'group' => 'admin',
                'channel' => 'admin',
                'target' => 'admin',
                'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new AdminNewSignupNotification($fixtures->wedding),
            ],
            [
                'id' => 'admin-enquiry-follow-up',
                'label' => 'Admin — enquiry follow-up',
                'group' => 'admin',
                'channel' => 'admin',
                'target' => 'admin',
                'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new AdminEnquiryFollowUpNotification(
                    $fixtures->enquiry->id ?: 1,
                ),
            ],
            [
                'id' => 'admin-inactive-wedding',
                'label' => 'Admin — inactive wedding (14 days)',
                'group' => 'admin',
                'channel' => 'admin',
                'target' => 'admin',
                'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new AdminInactiveWeddingReminderNotification($fixtures->wedding->id),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function scheduledPushScenarios(): array
    {
        return [[
            'id' => 'scheduled-guest-push',
            'label' => 'Scheduled guest push blast',
            'group' => 'scheduled-push',
            'channel' => 'scheduled-push',
            'target' => 'guest',
            'factory' => fn (NotificationPreviewFixtures $fixtures): Notification => new GuestPushNotification(
                title: 'Scheduled preview title',
                body: 'Scheduled preview body.',
                url: route('invitation.push.guest', [$fixtures->wedding->slug, $fixtures->guest->token]),
            ),
        ]];
    }

    private function sendMail(
        NotificationPreviewFixtures $fixtures,
        string $to,
        Notification $notification,
        string $target,
    ): void {
        $notifiable = match ($target) {
            'user' => $fixtures->user,
            'guest' => $fixtures->guest,
            default => throw new RuntimeException("Unsupported mail target [{$target}]."),
        };

        $this->withTemporaryEmail($notifiable, $to, function () use ($notifiable, $notification): void {
            $notifiable->notifyNow($notification);
        });
    }

    private function sendPushMirror(
        string $to,
        Notification $notification,
        NotificationPreviewFixtures $fixtures,
        string $label,
        string $target,
    ): void {
        $notifiable = match ($target) {
            'user' => $fixtures->user,
            'guest' => $fixtures->guest,
            default => throw new RuntimeException("Unsupported push-mirror target [{$target}]."),
        };

        if (! in_array(WebPushChannel::class, $notification->via($notifiable), true)) {
            throw new RuntimeException("Notification does not use WebPush channel for preview [{$label}].");
        }

        $message = $notification->toWebPush($notifiable, $notification);

        NotificationFacade::route('mail', $to)->notifyNow(
            new PushMirrorPreviewNotification($label, $message),
        );
    }

    private function sendScheduledGuestPush(
        NotificationPreviewFixtures $fixtures,
        string $to,
        ?int $logId,
    ): void {
        $log = $logId !== null
            ? PushNotificationLog::query()->with('weddingEvent')->findOrFail($logId)
            : PushNotificationLog::query()->create([
                'wedding_event_id' => $fixtures->wedding->id,
                'title' => 'Scheduled preview title',
                'body' => 'Scheduled preview body.',
                'recipient_type' => PushNotificationRecipientType::Selected,
                'sent_to_count' => 1,
                'guest_ids' => [$fixtures->guest->id],
                'status' => PushNotificationStatus::Scheduled,
            ]);

        $notification = new GuestPushNotification(
            title: $log->title,
            body: $log->body,
            url: route('invitation.push.guest', [$log->weddingEvent->slug, $fixtures->guest->token]),
        );

        $this->sendPushMirror($to, $notification, $fixtures, 'Scheduled guest push blast', 'guest');

        if ($logId === null) {
            $log->delete();
        }
    }

    private function withTemporaryEmail(object $notifiable, string $to, Closure $callback): void
    {
        $originalEmail = $notifiable->email ?? null;
        $notifiable->email = $to;

        try {
            $callback();
        } finally {
            $notifiable->email = $originalEmail;
        }
    }
}
