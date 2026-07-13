<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Notifications\AdminNewSignupNotification;
use App\Notifications\CoupleOnboardingTipNotification;
use App\Notifications\GuestRsvpReminderNotification;
use App\Support\AdminNotifier;
use App\Support\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class NotificationLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_preferred_locale_uses_profile_or_app_default(): void
    {
        $user = User::factory()->create(['locale' => 'de']);
        $this->assertSame('de', $user->preferredLocale());

        $userWithoutLocale = User::factory()->create(['locale' => null]);
        $this->assertSame(Locale::default(), $userWithoutLocale->preferredLocale());

        $userWithInvalidLocale = User::factory()->create(['locale' => 'xx']);
        $this->assertSame(Locale::default(), $userWithInvalidLocale->preferredLocale());
    }

    public function test_guest_preferred_locale_uses_couple_profile_or_app_default(): void
    {
        $couple = User::factory()->create(['locale' => 'de']);
        $event = WeddingEvent::factory()->for($couple)->create();
        $guest = Guest::factory()->for($event)->create();

        $this->assertSame('de', $guest->preferredLocale());

        $coupleWithoutLocale = User::factory()->create(['locale' => null]);
        $eventWithoutLocale = WeddingEvent::factory()->for($coupleWithoutLocale)->create();
        $guestWithoutLocale = Guest::factory()->for($eventWithoutLocale)->create();

        $this->assertSame(Locale::default(), $guestWithoutLocale->preferredLocale());
    }

    public function test_couple_email_notification_uses_profile_locale(): void
    {
        $user = User::factory()->create([
            'locale' => 'bs',
            'name' => 'Ana',
        ]);
        WeddingEvent::withoutEvents(fn () => WeddingEvent::factory()->for($user)->create([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'is_active' => false,
        ]));

        app()->setLocale('en');
        $user->notifyNow(new CoupleOnboardingTipNotification('day1'));

        $this->assertSame('en', app()->getLocale());
        $this->assertSame(
            'Dobrodošli u NasDan — dodajte prve goste',
            $this->lastSentEmail()->getSubject(),
        );
    }

    public function test_guest_email_notification_uses_couple_locale(): void
    {
        $couple = User::factory()->create(['locale' => 'bs']);
        $event = WeddingEvent::withoutEvents(fn () => WeddingEvent::factory()->for($couple)->create([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'rsvp_deadline' => now()->addDays(7),
            'is_active' => true,
        ]));
        $guest = Guest::withoutEvents(fn () => Guest::factory()->for($event)->create([
            'name' => 'Guest',
            'email' => 'guest@example.com',
            'rsvp_status' => null,
        ]));

        app()->setLocale('en');
        $guest->notifyNow(new GuestRsvpReminderNotification(7));

        $this->assertSame('en', app()->getLocale());
        $this->assertSame(
            'Podsjetnik za potvrdu — vjenčanje '.$guest->weddingEvent->couple_names,
            $this->lastSentEmail()->getSubject(),
        );
    }

    public function test_admin_notifier_anonymous_recipients_use_app_default_locale(): void
    {
        config(['notifications.admin_emails' => ['ops@example.com']]);

        Notification::fake();

        $event = WeddingEvent::factory()->make([
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
        ]);

        AdminNotifier::notify(new AdminNewSignupNotification($event));

        Notification::assertSentOnDemand(
            AdminNewSignupNotification::class,
            function (AdminNewSignupNotification $sent, array $channels): bool {
                return $sent->locale === Locale::default()
                    && in_array('mail', $channels, true);
            },
        );
    }

    private function lastSentEmail(): Email
    {
        $messages = app('mail.manager')->mailer()->getSymfonyTransport()->messages();

        $this->assertNotEmpty($messages);

        $email = $messages->last()->getOriginalMessage();
        $this->assertInstanceOf(Email::class, $email);

        return $email;
    }
}
