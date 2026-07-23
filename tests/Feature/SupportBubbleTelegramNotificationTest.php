<?php

namespace Tests\Feature;

use App\Listeners\SendSupportBubbleToTelegram;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Spatie\SupportBubble\Events\SupportBubbleSubmittedEvent;
use Spatie\SupportBubble\Notifications\BubbleResponseNotification;
use Tests\TestCase;

class SupportBubbleTelegramNotificationTest extends TestCase
{
    public function test_listener_runs_synchronously(): void
    {
        $this->assertFalse(
            is_subclass_of(SendSupportBubbleToTelegram::class, ShouldQueue::class)
        );
    }

    public function test_successful_telegram_delivery_does_not_send_fallback_email(): void
    {
        config([
            'services.telegram.support_bot_token' => 'test-token',
            'services.telegram.support_chat_id' => '12345',
        ]);

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        Notification::fake();
        Queue::fake();

        User::factory()->create(['is_admin' => true]);

        event($this->makeEvent());

        Queue::assertNothingPushed();

        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.telegram.org/bottest-token/sendMessage')
                && $request['chat_id'] === '12345'
                && str_contains($request['text'], 'Support bubble message')
                && str_contains($request['text'], 'Ada Lovelace')
                && str_contains($request['text'], 'Need help')
                && $request['parse_mode'] === 'HTML';
        });

        Notification::assertNothingSent();
    }

    public function test_uses_authenticated_user_name_when_form_omits_name(): void
    {
        config([
            'services.telegram.support_bot_token' => 'test-token',
            'services.telegram.support_chat_id' => '12345',
        ]);

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $user = User::factory()->create([
            'name' => 'Marko Marković',
            'email' => 'marko@example.com',
        ]);

        $request = Request::create('/support-bubble', 'POST');
        $request->setUserResolver(fn () => $user);

        event(new SupportBubbleSubmittedEvent(
            subject: null,
            message: 'Need help with seating',
            email: 'marko@example.com',
            name: null,
            url: 'https://example.com/app',
            ip: '203.0.113.10',
            userAgent: 'PHPUnit',
            request: $request,
        ));

        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'Marko Marković')
                && str_contains($request['text'], 'marko@example.com')
                && str_contains($request['text'], 'Novi upit')
                && ! str_contains($request['text'], 'Unknown');
        });
    }

    public function test_falls_back_to_novi_upit_when_subject_is_empty(): void
    {
        config([
            'services.telegram.support_bot_token' => 'test-token',
            'services.telegram.support_chat_id' => '12345',
        ]);

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        event($this->makeEvent(subject: ''));

        Http::assertSent(fn ($request) => str_contains($request['text'], 'Novi upit'));
    }

    public function test_telegram_failure_falls_back_to_admin_email(): void
    {
        config([
            'services.telegram.support_bot_token' => 'test-token',
            'services.telegram.support_chat_id' => '12345',
        ]);

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'Forbidden'], 403),
        ]);

        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);

        event($this->makeEvent());

        Notification::assertSentTo($admin, BubbleResponseNotification::class);
    }

    public function test_missing_telegram_config_falls_back_to_admin_email(): void
    {
        config([
            'services.telegram.support_bot_token' => null,
            'services.telegram.support_chat_id' => null,
        ]);

        Http::fake();
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);

        event($this->makeEvent());

        Http::assertNothingSent();
        Notification::assertSentTo($admin, BubbleResponseNotification::class);
    }

    public function test_support_bubble_http_submission_delivers_to_telegram_synchronously(): void
    {
        config([
            'honeypot.enabled' => false,
            'support-bubble.fields.name' => false,
            'support-bubble.fields.subject' => true,
            'support-bubble.fields.email' => true,
            'support-bubble.fields.message' => true,
            'services.telegram.support_bot_token' => 'test-token',
            'services.telegram.support_chat_id' => '12345',
        ]);

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        Notification::fake();
        Queue::fake();

        $user = User::factory()->create([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ]);

        $response = $this->actingAs($user)->post('/support-bubble', [
            'email' => 'ada@example.com',
            'subject' => 'Seating plan help',
            'message' => 'End-to-end support test',
            'url' => 'https://example.com/app',
        ]);

        $response->assertOk();
        Queue::assertNothingPushed();

        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.telegram.org/bottest-token/sendMessage')
                && str_contains($request['text'], 'End-to-end support test')
                && str_contains($request['text'], 'Ada Lovelace')
                && str_contains($request['text'], 'ada@example.com')
                && str_contains($request['text'], 'Seating plan help');
        });

        Notification::assertNothingSent();
    }

    public function test_support_bubble_event_has_single_listener(): void
    {
        $listeners = collect(app('events')->getRawListeners()[SupportBubbleSubmittedEvent::class] ?? [])
            ->map(function ($listener) {
                if (is_string($listener)) {
                    return $listener;
                }

                if (is_array($listener) && is_string($listener[0] ?? null)) {
                    return $listener[0].'@'.($listener[1] ?? 'handle');
                }

                return is_object($listener) ? $listener::class : (string) $listener;
            })
            ->filter(fn (string $listener) => str_contains($listener, 'SendSupportBubbleToTelegram'))
            ->values();

        $this->assertCount(1, $listeners);
    }

    private function makeEvent(?string $subject = 'Need help'): SupportBubbleSubmittedEvent
    {
        return new SupportBubbleSubmittedEvent(
            subject: $subject,
            message: 'Something is broken',
            email: 'ada@example.com',
            name: 'Ada Lovelace',
            url: 'https://example.com/app',
            ip: '203.0.113.10',
            userAgent: 'PHPUnit',
            request: Request::create('/support-bubble', 'POST'),
        );
    }
}
