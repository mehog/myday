<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Notifications\Preview\PushMirrorPreviewNotification;
use App\Services\NotificationPreviewFixtures;
use App\Services\NotificationPreviewService;
use App\Support\Locale;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Symfony\Component\Mime\Email;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class NotificationPreviewSmokeTest extends TestCase
{
    use RefreshInMemoryDatabase;

    private NotificationPreviewService $preview;

    private NotificationPreviewFixtures $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->preview = app(NotificationPreviewService::class);
        $this->fixtures = $this->createFixtures();
    }

    public function test_every_preview_scenario_builds_mail_or_push_mirror_without_error(): void
    {
        foreach ($this->preview->previewableScenarioIds() as $id) {
            if ($id === 'scheduled-guest-push') {
                continue;
            }

            $notification = $this->preview->buildNotification($id, $this->fixtures);
            $notifiable = $this->notifiableFor($id);
            $channel = $this->preview->channelFor($id);

            if (in_array($channel, ['mail', 'admin'], true) && method_exists($notification, 'toMail')) {
                $mail = $notification->toMail($notifiable);

                $this->assertInstanceOf(MailMessage::class, $mail);
                $this->assertNotEmpty($mail->subject);
            }

            if ($channel === 'push-mirror' && method_exists($notification, 'toWebPush')) {
                $message = $notification->toWebPush($notifiable, $notification);
                $payload = $message->toArray();

                $this->assertNotEmpty($payload['title'] ?? null);
                $this->assertNotEmpty($payload['body'] ?? null);

                $mirror = new PushMirrorPreviewNotification($id, $message);
                $mirrorMail = $mirror->toMail(new AnonymousNotifiable);

                $this->assertStringContainsString('[Push preview]', $mirrorMail->subject);
            }
        }
    }

    public function test_preview_service_sends_onboarding_emails_to_given_inbox(): void
    {
        $sent = $this->preview->sendAll(
            to: 'preview@example.com',
            only: 'onboarding',
            fixtureIds: [
                'wedding_id' => $this->fixtures->wedding->id,
                'guest_id' => $this->fixtures->guest->id,
                'user_id' => $this->fixtures->user->id,
                'enquiry_id' => $this->fixtures->enquiry->id,
            ],
            locale: 'en',
            delaySeconds: 0,
        );

        $this->assertCount(3, $sent);

        $messages = app('mail.manager')->mailer()->getSymfonyTransport()->messages();

        $this->assertGreaterThanOrEqual(3, count($messages));

        $email = $messages->last()->getOriginalMessage();
        $this->assertInstanceOf(Email::class, $email);
        $this->assertNotEmpty($email->getSubject());
    }

    public function test_admin_enquiry_follow_up_preview_works_without_persisted_enquiry(): void
    {
        $user = User::factory()->create(['locale' => Locale::default()]);
        $wedding = WeddingEvent::withoutEvents(fn () => WeddingEvent::factory()->for($user)->create([
            'rsvp_deadline' => now()->addDays(7),
            'is_active' => false,
        ]));
        $guest = Guest::withoutEvents(fn () => Guest::factory()->for($wedding)->create([
            'email' => 'guest@example.com',
        ]));

        $fixtures = NotificationPreviewFixtures::resolve([
            'wedding_id' => $wedding->id,
            'guest_id' => $guest->id,
            'user_id' => $user->id,
        ]);

        $notification = $this->preview->buildNotification('admin-enquiry-follow-up', $fixtures);
        $mail = $notification->toMail(new AnonymousNotifiable);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertNotEmpty($mail->subject);
    }

    public function test_preview_command_lists_scenarios(): void
    {
        $this->artisan('notifications:preview', ['--list' => true])
            ->assertSuccessful();
    }

    public function test_list_includes_skipped_database_notification(): void
    {
        $skipped = collect($this->preview->listScenarios())
            ->firstWhere('id', 'new-guest-message');

        $this->assertNotNull($skipped);
        $this->assertTrue($skipped['skipped']);
    }

    private function notifiableFor(string $id): object
    {
        return match ($this->preview->targetFor($id)) {
            'user' => $this->fixtures->user,
            'guest' => $this->fixtures->guest,
            'admin' => new AnonymousNotifiable,
            default => new AnonymousNotifiable,
        };
    }

    private function createFixtures(): NotificationPreviewFixtures
    {
        $user = User::factory()->create(['locale' => Locale::default()]);
        $wedding = WeddingEvent::withoutEvents(fn () => WeddingEvent::factory()->for($user)->create([
            'rsvp_deadline' => now()->addDays(7),
            'is_active' => false,
        ]));
        $guest = Guest::withoutEvents(fn () => Guest::factory()->for($wedding)->create([
            'email' => 'guest@example.com',
        ]));
        $enquiry = Enquiry::factory()->create();

        return NotificationPreviewFixtures::resolve([
            'wedding_id' => $wedding->id,
            'guest_id' => $guest->id,
            'user_id' => $user->id,
            'enquiry_id' => $enquiry->id,
        ]);
    }
}
