<?php

namespace App\Console\Commands;

use App\Models\WeddingEvent;
use App\Services\EnsureWeddingTasks;
use Illuminate\Console\Command;

class SyncWeddingTasksCommand extends Command
{
    protected $signature = 'wedding-tasks:sync';

    protected $description = 'Seed and refresh system wedding checklist tasks for all events';

    public function handle(EnsureWeddingTasks $ensureWeddingTasks): int
    {
        $count = 0;

        WeddingEvent::query()->each(function (WeddingEvent $event) use ($ensureWeddingTasks, &$count): void {
            $ensureWeddingTasks->handle($event);
            $count++;
        });

        $this->info("Synced wedding tasks for {$count} events.");

        return self::SUCCESS;
    }
}
