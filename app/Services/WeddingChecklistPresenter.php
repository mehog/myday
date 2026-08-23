<?php

namespace App\Services;

use App\Models\WeddingEvent;
use App\Models\WeddingTask;
use App\WeddingTaskPeriod;
use Illuminate\Support\Collection;

class WeddingChecklistPresenter
{
    public function __construct(
        private readonly EnsureWeddingTasks $ensureWeddingTasks,
        private readonly WeddingTaskProgress $progress,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function rows(WeddingEvent $event): Collection
    {
        $this->ensureWeddingTasks->handle($event);

        return $event->tasks()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (WeddingTask $task): array => $this->row($event, $task))
            ->values();
    }

    /**
     * @return array{total: int, completed: int, percent: int, next: Collection<int, array<string, mixed>>}
     */
    public function summary(WeddingEvent $event, int $nextLimit = 3): array
    {
        return $this->summarize($this->rows($event), $nextLimit);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array{total: int, completed: int, percent: int, next: Collection<int, array<string, mixed>>}
     */
    public function summarize(Collection $rows, int $nextLimit = 3): array
    {
        $total = $rows->count();
        $completed = $rows->filter(fn (array $row): bool => $row['task']->isCompleted())->count();

        $next = $rows
            ->reject(fn (array $row): bool => $row['task']->isCompleted())
            ->sortBy(function (array $row): array {
                $due = $row['task']->due_date;

                return [$due?->timestamp ?? PHP_INT_MAX, $row['task']->sort_order, $row['task']->id];
            })
            ->values()
            ->take($nextLimit);

        return [
            'total' => $total,
            'completed' => $completed,
            'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'next' => $next,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    public function groupByPeriod(Collection $rows): Collection
    {
        return $rows
            ->groupBy(fn (array $row): string => $row['task']->period->value)
            ->sortBy(function (Collection $group, string $period): int {
                return WeddingTaskPeriod::from($period)->sortOrder();
            });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array{total: int, completed: int}
     */
    public function periodSummary(Collection $rows): array
    {
        $total = $rows->count();
        $completed = $rows->filter(fn (array $row): bool => $row['task']->isCompleted())->count();

        return [
            'total' => $total,
            'completed' => $completed,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function row(WeddingEvent $event, WeddingTask $task): array
    {
        return [
            'task' => $task,
            'title' => $task->displayTitle(),
            'description' => $task->displayDescription(),
            'progress' => $this->progress->for($event, $task),
            'action_url' => $task->actionUrl(),
            'action_label' => $task->actionLabel(),
            'due_label' => $this->dueLabel($task),
        ];
    }

    protected function dueLabel(WeddingTask $task): ?string
    {
        if ($task->due_date === null) {
            return null;
        }

        $days = (int) now()->startOfDay()->diffInDays($task->due_date->copy()->startOfDay(), false);

        if ($days === 0) {
            return __('checklist.due_today');
        }

        if ($days > 0) {
            return __('checklist.due_in_days', ['days' => $days]);
        }

        return __('checklist.overdue', ['date' => $task->due_date->format('d.m.Y.')]);
    }
}
