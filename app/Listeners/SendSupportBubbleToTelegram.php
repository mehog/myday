<?php

namespace App\Listeners;

use App\Support\AdminNotifier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\SupportBubble\Events\SupportBubbleSubmittedEvent;
use Spatie\SupportBubble\Notifications\BubbleResponseNotification;

class SendSupportBubbleToTelegram
{
    public function handle(SupportBubbleSubmittedEvent $event): void
    {
        $this->enrichFromAuthenticatedUser($event);

        try {
            $this->sendToTelegram($event);
        } catch (\Throwable $e) {
            Log::warning('Support bubble Telegram delivery failed, falling back to email', [
                'error' => $e->getMessage(),
            ]);

            AdminNotifier::notify(BubbleResponseNotification::fromEvent($event));
        }
    }

    private function enrichFromAuthenticatedUser(SupportBubbleSubmittedEvent $event): void
    {
        $user = $event->request->user();

        $event->name = $event->name ?: $user?->name;
        $event->email = $event->email ?: $user?->email;
        $event->subject = filled($event->subject) ? $event->subject : 'Novi upit';
    }

    private function sendToTelegram(SupportBubbleSubmittedEvent $event): void
    {
        $token = config('services.telegram.support_bot_token');
        $chatId = config('services.telegram.support_chat_id');

        if (! $token || ! $chatId) {
            throw new \RuntimeException('Telegram support bot is not configured.');
        }

        $response = Http::asForm()
            ->timeout(5)
            ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $this->formatMessage($event),
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Telegram API responded with an error: '.$response->body());
        }
    }

    private function formatMessage(SupportBubbleSubmittedEvent $event): string
    {
        $name = e($event->name ?? 'Unknown');
        $email = e($event->email ?? 'Unknown');
        $subject = e($event->subject ?? 'Novi upit');
        $message = e($event->message ?? '');
        $url = e($event->url ?? 'Unknown');
        $ip = e($event->ip ?? 'Unknown');
        $userAgent = e($event->userAgent ?? 'Unknown');

        return implode("\n", [
            '<b>Support bubble message</b>',
            '',
            "<b>From:</b> {$name} ({$email})",
            "<b>Subject:</b> {$subject}",
            '',
            $message,
            '',
            "<b>URL:</b> {$url}",
            "<b>IP:</b> {$ip}",
            "<b>User agent:</b> {$userAgent}",
        ]);
    }
}
