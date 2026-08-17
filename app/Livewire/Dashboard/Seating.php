<?php

namespace App\Livewire\Dashboard;

use App\GuestLabel;
use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\Guest;
use App\Models\WeddingEvent;
use App\PlanFeature;
use App\RsvpStatus;
use Illuminate\Support\Collection;
use Livewire\Component;

class Seating extends Component
{
    use RendersDashboard;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $seatingPlan = null;

    public ?string $flashMessage = null;

    public function mount(): void
    {
        $wedding = auth()->user()?->weddingEvent;

        $this->seatingPlan = $wedding?->seating_plan ?? ['tables' => []];
    }

    /**
     * @return Collection<int, array{id: int|string, name: string, is_plus_one: bool, is_child: bool, is_couple: bool}>
     */
    public function getGuests(): Collection
    {
        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding instanceof WeddingEvent) {
            return collect();
        }

        $couple = collect([
            [
                'id' => 'bride',
                'name' => $wedding->bride_name,
                'labels' => [],
                'is_plus_one' => false,
                'is_child' => false,
                'is_couple' => true,
            ],
            [
                'id' => 'groom',
                'name' => $wedding->groom_name,
                'labels' => [],
                'is_plus_one' => false,
                'is_child' => false,
                'is_couple' => true,
            ],
        ]);

        return $couple->concat(
            $wedding->guests()
                ->where('rsvp_status', RsvpStatus::Yes)
                ->with('children')
                ->orderBy('name')
                ->get(['id', 'name', 'plus_one_name', 'plus_one_seating_name', 'labels'])
                ->flatMap(function (Guest $guest): array {
                    $labelNames = $guest->labels
                        ? $guest->labels->map(fn (GuestLabel $label): string => $label->label())->values()->all()
                        : [];

                    $entries = [
                        [
                            'id' => $guest->id,
                            'name' => $guest->name,
                            'labels' => $labelNames,
                            'is_plus_one' => false,
                            'is_child' => false,
                            'is_couple' => false,
                        ],
                    ];

                    $plusOneName = $guest->plusOneDisplayName();

                    if (filled($plusOneName)) {
                        $entries[] = [
                            'id' => -$guest->id,
                            'name' => $plusOneName.' ('.$guest->name.')',
                            'labels' => $labelNames,
                            'is_plus_one' => true,
                            'is_child' => false,
                            'is_couple' => false,
                        ];
                    }

                    foreach ($guest->children as $child) {
                        $entries[] = [
                            'id' => $child->seatingAssigneeKey(),
                            'name' => $child->displayName().' ('.$guest->name.')',
                            'labels' => $labelNames,
                            'is_plus_one' => false,
                            'is_child' => true,
                            'is_couple' => false,
                        ];
                    }

                    return $entries;
                }),
        );
    }

    public function save(string $json, bool $notify = true): void
    {
        $wedding = auth()->user()?->weddingEvent;

        abort_unless($wedding instanceof WeddingEvent, 404);

        $data = json_decode($json, true);

        if (! is_array($data) || ! isset($data['tables']) || ! is_array($data['tables'])) {
            abort(422, __('seating.invalid_data'));
        }

        $assignedGuestIds = collect($data['tables'])
            ->flatMap(fn (array $table): array => $table['seats'] ?? [])
            ->filter()
            ->values();

        abort_if(
            $assignedGuestIds->count() !== $assignedGuestIds->unique()->count(),
            422,
            __('seating.duplicate_assignment')
        );

        $wedding->update(['seating_plan' => $data]);

        $this->seatingPlan = $data;

        if ($notify) {
            $this->flashMessage = __('seating.saved');
        }
    }

    public function canExportSeatingPdf(): bool
    {
        $wedding = auth()->user()?->weddingEvent;

        return $wedding instanceof WeddingEvent
            && $wedding->hasFeature(PlanFeature::SeatingPdfExport);
    }

    public function render()
    {
        abort_unless(auth()->user()?->weddingEvent, 404);

        return $this->dashboardView('livewire.dashboard.seating', [], __('seating.page_title'), [
            ['label' => __('seating.nav_label'), 'url' => null],
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function getEditorLabels(): array
    {
        return [
            'guests' => __('seating.guests_heading'),
            'unassigned' => __('seating.unassigned'),
            'assigned' => __('seating.assigned'),
            'inspector' => __('seating.inspector_heading'),
            'table_label' => __('seating.table_label'),
            'chairs' => __('seating.chairs'),
            'add_chair' => __('seating.add_chair'),
            'remove_chair' => __('seating.remove_chair'),
            'delete_table' => __('seating.delete_table'),
            'add_round' => __('seating.add_round'),
            'add_rect' => __('seating.add_rect'),
            'add_head' => __('seating.add_head'),
            'zoom_in' => __('seating.zoom_in'),
            'zoom_out' => __('seating.zoom_out'),
            'reset_zoom' => __('seating.reset_zoom'),
            'duplicate_guest' => __('seating.duplicate_guest'),
            'remove_guest_confirm' => __('seating.remove_guest_confirm'),
            'remove_chair_confirm' => __('seating.remove_chair_confirm'),
            'no_guests' => __('seating.no_guests'),
            'select_table' => __('seating.select_table'),
            'remove_guest' => __('seating.remove_guest'),
            'unsaved_save_before_leave' => __('seating.unsaved_save_before_leave'),
            'unsaved_leave_without_saving' => __('seating.unsaved_leave_without_saving'),
            'default_table_head' => __('seating.default_table_head'),
            'default_table_numbered' => __('seating.default_table_numbered'),
        ];
    }
}
