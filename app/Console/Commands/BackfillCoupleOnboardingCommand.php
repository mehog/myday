<?php

namespace App\Console\Commands;

use App\Models\WeddingEvent;
use App\Services\WeddingScheduledNotificationService;
use Illuminate\Console\Command;

class BackfillCoupleOnboardingCommand extends Command
{
    protected $signature = 'notifications:backfill-couple-onboarding
                            {--dry-run : List events that would be scheduled without writing}';

    protected $description = 'Re-anchor couple onboarding tip emails from now for existing non-demo, non-marketing weddings';

    public function handle(WeddingScheduledNotificationService $scheduler): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $anchor = now();

        $query = WeddingEvent::query()
            ->with('user')
            ->whereNotNull('user_id')
            ->where('is_demo', false)
            ->where('is_marketing', false)
            ->whereDate('wedding_date', '>=', now()->toDateString())
            ->whereHas('user', function ($q): void {
                $q->where('backfill_onboarding_emails', true)
                    ->where('email_notifications_enabled', true);
            })
            ->orderBy('id');

        $scheduled = 0;
        $skipped = 0;
        $rows = [];

        $query->each(function (WeddingEvent $event) use ($scheduler, $dryRun, $anchor, &$scheduled, &$skipped, &$rows): void {
            $user = $event->user;

            if ($user === null
                || $event->suppressesOutboundMail()
                || $event->isArchived()
                || ! $user->backfill_onboarding_emails
                || ! $user->wantsProductEmail()
            ) {
                $skipped++;

                return;
            }

            $day1 = $anchor->copy()->addHours((int) config('notifications.couple_onboarding_hours.day1', 6));
            $day3 = $anchor->copy()->addHours((int) config('notifications.couple_onboarding_hours.day3', 18));
            $day7 = $anchor->copy()->addHours((int) config('notifications.couple_onboarding_hours.day7', 30));

            $rows[] = [
                $event->id,
                $event->couple_names,
                $user->email,
                $day1->toDateTimeString(),
                $day3->toDateTimeString(),
                $day7->toDateTimeString(),
            ];

            if (! $dryRun) {
                $scheduler->syncCoupleOnboarding($event, $anchor);
            }

            $scheduled++;
        });

        if ($rows !== []) {
            $this->table(
                ['ID', 'Couple', 'Email', 'Day1', 'Day3', 'Day7'],
                $rows,
            );
        }

        if ($dryRun) {
            $this->info("Dry run: {$scheduled} event(s) would be scheduled, {$skipped} skipped.");
        } else {
            $this->info("Scheduled onboarding tips for {$scheduled} event(s), {$skipped} skipped.");
        }

        return self::SUCCESS;
    }
}
