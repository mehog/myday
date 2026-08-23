<?php

namespace App\Services;

use App\Models\WeddingEvent;
use App\Models\WeddingTask;
use App\Support\WeddingTaskCatalog;
use Illuminate\Support\Carbon;

class EnsureWeddingTasks
{
    public function handle(WeddingEvent $event): void
    {
        foreach (WeddingTaskCatalog::all() as $definition) {
            $attributes = [
                'period' => $definition['period'],
                'priority' => $definition['priority'],
                'sort_order' => $definition['sort_order'],
            ];

            $task = WeddingTask::query()->firstOrCreate(
                [
                    'wedding_event_id' => $event->id,
                    'system_key' => $definition['key'],
                ],
                [
                    ...$attributes,
                    'due_date' => $this->dueDate($event, $definition['due_offset_days']),
                ],
            );

            if ($task->wasRecentlyCreated || $task->completed_at !== null) {
                continue;
            }

            $task->fill([
                ...$attributes,
                'due_date' => $this->dueDate($event, $definition['due_offset_days']),
            ]);

            if ($task->isDirty()) {
                $task->save();
            }
        }
    }

    public function syncDueDates(WeddingEvent $event): void
    {
        $event->tasks()
            ->whereNotNull('system_key')
            ->whereNull('completed_at')
            ->get()
            ->each(function (WeddingTask $task) use ($event): void {
                $definition = WeddingTaskCatalog::definition((string) $task->system_key);

                if ($definition === null) {
                    return;
                }

                $dueDate = $this->dueDate($event, $definition['due_offset_days']);

                if ($task->due_date?->toDateString() === $dueDate) {
                    return;
                }

                $task->update(['due_date' => $dueDate]);
            });
    }

    protected function dueDate(WeddingEvent $event, int $offsetDays): ?string
    {
        if ($event->wedding_date === null) {
            return null;
        }

        return Carbon::parse($event->wedding_date)
            ->timezone(config('app.timezone'))
            ->startOfDay()
            ->subDays($offsetDays)
            ->toDateString();
    }
}
