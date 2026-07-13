<?php

namespace App\Console\Commands;

use App\Services\NotificationPreviewService;
use App\Support\Locale;
use Illuminate\Console\Command;

class PreviewNotificationsCommand extends Command
{
    protected $signature = 'notifications:preview
                            {--to= : Inbox address for real email delivery}
                            {--list : List previewable notification scenarios}
                            {--only= : Comma-separated groups: onboarding, activation, rsvp, pre-wedding, photo, guest-push, couple-rsvp, admin, scheduled-push, all}
                            {--locale= : Locale override (bs, en, de)}
                            {--wedding-id= : Wedding event ID for fixture data}
                            {--guest-id= : Guest ID for fixture data}
                            {--user-id= : User ID for fixture data}
                            {--enquiry-id= : Enquiry ID for fixture data}
                            {--log-id= : Push notification log ID for scheduled-push preview}
                            {--delay= : Seconds to wait between sends (default: config notifications.preview_delay_seconds)}
                            {--force : Allow running in production}';

    protected $description = 'Send all notification types to a real inbox for local preview';

    public function handle(NotificationPreviewService $preview): int
    {
        if ($this->option('list')) {
            $this->table(
                ['ID', 'Label', 'Group', 'Channel', 'Skipped', 'Note'],
                collect($preview->listScenarios())->map(fn (array $row): array => [
                    $row['id'],
                    $row['label'],
                    $row['group'],
                    $row['channel'],
                    ($row['skipped'] ?? false) ? 'yes' : 'no',
                    $row['skip_reason'] ?? '',
                ])->all(),
            );

            return self::SUCCESS;
        }

        if (! $this->option('force') && app()->environment('production')) {
            $this->error('Refusing to send preview notifications in production. Use --force to override.');

            return self::FAILURE;
        }

        $to = $this->option('to');

        if (! is_string($to) || $to === '') {
            $this->error('Pass --to=your@email.com or use --list to see scenarios.');

            return self::FAILURE;
        }

        $locale = $this->option('locale');

        if (is_string($locale) && $locale !== '' && ! Locale::isSupported($locale)) {
            $this->error('Unsupported locale. Use one of: '.implode(', ', Locale::supported()));

            return self::FAILURE;
        }

        $fixtureIds = array_filter([
            'wedding_id' => $this->option('wedding-id') !== null ? (int) $this->option('wedding-id') : null,
            'guest_id' => $this->option('guest-id') !== null ? (int) $this->option('guest-id') : null,
            'user_id' => $this->option('user-id') !== null ? (int) $this->option('user-id') : null,
            'enquiry_id' => $this->option('enquiry-id') !== null ? (int) $this->option('enquiry-id') : null,
        ], fn (mixed $value): bool => $value !== null);

        $logId = $this->option('log-id') !== null ? (int) $this->option('log-id') : null;

        $delaySeconds = $this->option('delay') !== null
            ? max(0, (int) $this->option('delay'))
            : (int) config('notifications.preview_delay_seconds', 2);

        if ($delaySeconds > 0) {
            $this->line("Waiting {$delaySeconds}s between sends to avoid mail rate limits.");
        }

        try {
            $sent = $preview->sendAll(
                to: $to,
                only: is_string($this->option('only')) ? $this->option('only') : null,
                fixtureIds: $fixtureIds,
                locale: is_string($locale) && $locale !== '' ? $locale : null,
                logId: $logId,
                delaySeconds: $delaySeconds,
            );
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        foreach ($sent as $label) {
            $this->line("Sent: {$label}");
        }

        $this->newLine();
        $this->info('Sent '.count($sent).' notification preview(s) to '.$to.'.');

        return self::SUCCESS;
    }
}
