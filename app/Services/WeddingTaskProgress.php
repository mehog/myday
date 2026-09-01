<?php

namespace App\Services;

use App\Models\WeddingEvent;
use App\Models\WeddingTask;
use App\RsvpStatus;
use App\Support\WeddingTaskCatalog;

class WeddingTaskProgress
{
    /**
     * @return array{current: int, target: int, label: string}|null
     */
    public function for(WeddingEvent $event, WeddingTask $task): ?array
    {
        $definition = $task->catalogDefinition() ?? WeddingTaskCatalog::definition((string) $task->system_key);

        if ($definition === null || $definition['progress'] === null) {
            return null;
        }

        return match ($definition['progress']) {
            'send_invitations' => $this->sendInvitations($event),
            'track_rsvp' => $this->trackRsvp($event),
            'finish_seating' => $this->finishSeating($event),
            'confirm_menu' => $this->confirmMenu($event),
            'review_budget' => $this->reviewBudget($event),
            'add_guests' => $this->addGuests($event),
            'set_schedule' => $this->setSchedule($event),
            'set_location' => $this->setLocation($event),
            'add_photos' => $this->addPhotos($event),
            default => null,
        };
    }

    /**
     * @return array{current: int, target: int, label: string}
     */
    protected function sendInvitations(WeddingEvent $event): array
    {
        $total = $event->guests()->count();
        $sent = $event->guests()->whereNotNull('invite_sent_at')->count();

        return [
            'current' => $sent,
            'target' => max($total, 1),
            'label' => __('checklist.progress.send_invitations', [
                'current' => $sent,
                'total' => $total,
            ]),
        ];
    }

    /**
     * @return array{current: int, target: int, label: string}
     */
    protected function trackRsvp(WeddingEvent $event): array
    {
        $total = $event->guests()->count();
        $responded = $event->guests()->whereNotNull('rsvp_status')->count();

        return [
            'current' => $responded,
            'target' => max($total, 1),
            'label' => __('checklist.progress.track_rsvp', [
                'current' => $responded,
                'total' => $total,
            ]),
        ];
    }

    /**
     * @return array{current: int, target: int, label: string}
     */
    protected function finishSeating(WeddingEvent $event): array
    {
        $assigned = $event->assignedSeatingCount();
        $confirmed = $event->confirmedHeadcount();

        return [
            'current' => $assigned,
            'target' => max($confirmed, 1),
            'label' => __('checklist.progress.finish_seating', [
                'current' => $assigned,
                'total' => $confirmed,
            ]),
        ];
    }

    /**
     * @return array{current: int, target: int, label: string}
     */
    protected function confirmMenu(WeddingEvent $event): array
    {
        $total = $event->guests()->where('rsvp_status', RsvpStatus::Yes)->count();
        $withMenu = $event->guests()
            ->where('rsvp_status', RsvpStatus::Yes)
            ->whereNotNull('menu_option_id')
            ->count();

        return [
            'current' => $withMenu,
            'target' => max($total, 1),
            'label' => __('checklist.progress.confirm_menu', [
                'current' => $withMenu,
                'total' => $total,
            ]),
        ];
    }

    /**
     * @return array{current: int, target: int, label: string}
     */
    protected function reviewBudget(WeddingEvent $event): array
    {
        $totals = $event->budgetTotals();
        $paid = $totals['paid'];
        $target = filled($event->budget_target) && (float) $event->budget_target > 0
            ? (string) $event->budget_target
            : $totals['total'];

        if ((float) $target <= 0) {
            $target = '0.00';
        }

        return [
            'current' => (int) round((float) $paid * 100),
            'target' => max((int) round((float) $target * 100), 1),
            'label' => __('checklist.progress.review_budget', [
                'paid' => $this->formatMoney($paid, $event),
                'total' => $this->formatMoney($target, $event),
            ]),
        ];
    }

    /**
     * @return array{current: int, target: int, label: string}
     */
    protected function addGuests(WeddingEvent $event): array
    {
        $count = $event->guests()->count();

        return [
            'current' => $count > 0 ? 1 : 0,
            'target' => 1,
            'label' => __('checklist.progress.add_guests', ['count' => $count]),
        ];
    }

    /**
     * @return array{current: int, target: int, label: string}
     */
    protected function setSchedule(WeddingEvent $event): array
    {
        $count = $event->scheduleItems()->count();

        return [
            'current' => $count > 0 ? 1 : 0,
            'target' => 1,
            'label' => __('checklist.progress.set_schedule', ['count' => $count]),
        ];
    }

    /**
     * @return array{current: int, target: int, label: string}
     */
    protected function setLocation(WeddingEvent $event): array
    {
        $hasLocation = $event->hasLocations() || filled($event->location_name);

        return [
            'current' => $hasLocation ? 1 : 0,
            'target' => 1,
            'label' => $hasLocation
                ? __('checklist.progress.set_location_yes')
                : __('checklist.progress.set_location_no'),
        ];
    }

    /**
     * @return array{current: int, target: int, label: string}
     */
    protected function addPhotos(WeddingEvent $event): array
    {
        $count = $event->eventPhotos()->count();

        return [
            'current' => $count > 0 ? 1 : 0,
            'target' => 1,
            'label' => __('checklist.progress.add_photos', ['count' => $count]),
        ];
    }

    public function assignedSeatingCount(WeddingEvent $event): int
    {
        return $event->assignedSeatingCount();
    }

    protected function formatMoney(string|float|int $amount, WeddingEvent $event): string
    {
        return number_format((float) $amount, 2, ',', '.').' '.$event->budgetCurrency();
    }
}
