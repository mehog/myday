<?php

namespace App\Filament\App\Pages;

use App\Models\Guest;
use App\Models\WeddingEvent;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class SeatingPlan extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected static ?string $slug = 'raspored-sjedenja';

    protected static ?int $navigationSort = 25;

    protected string $view = 'filament.app.pages.seating-plan';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $seatingPlan = null;

    public static function getNavigationLabel(): string
    {
        return __('seating.nav_label');
    }

    public function getTitle(): string
    {
        return __('seating.page_title');
    }

    public function getHeading(): ?string
    {
        return null;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->weddingEvent !== null;
    }

    public function mount(): void
    {
        $wedding = auth()->user()?->weddingEvent;

        $this->seatingPlan = $wedding?->seating_plan ?? ['tables' => []];
    }

    /**
     * @return Collection<int, array{id: int|string, name: string, is_plus_one: bool, is_couple: bool}>
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
                'is_plus_one' => false,
                'is_couple' => true,
            ],
            [
                'id' => 'groom',
                'name' => $wedding->groom_name,
                'is_plus_one' => false,
                'is_couple' => true,
            ],
        ]);

        return $couple->concat(
            $wedding->guests()
                ->orderBy('name')
                ->get(['id', 'name', 'plus_one_name'])
                ->flatMap(function (Guest $guest): array {
                    $entries = [
                        [
                            'id' => $guest->id,
                            'name' => $guest->name,
                            'is_plus_one' => false,
                            'is_couple' => false,
                        ],
                    ];

                    if (filled($guest->plus_one_name)) {
                        $entries[] = [
                            'id' => -$guest->id,
                            'name' => $guest->plus_one_name.' ('.$guest->name.')',
                            'is_plus_one' => true,
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
            Notification::make()
                ->title(__('seating.saved'))
                ->success()
                ->send();
        }
    }

    public function exportPdf(string $imageDataUrl): void
    {
        $wedding = auth()->user()?->weddingEvent;

        abort_unless($wedding instanceof WeddingEvent, 404);

        $imageDataUri = str_starts_with($imageDataUrl, 'data:image/')
            ? $imageDataUrl
            : 'data:image/png;base64,'.$imageDataUrl;

        session(['seating_plan_pdf_image' => $imageDataUri]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('seating.save'))
                ->icon('heroicon-o-check')
                ->color('primary')
                ->alpineClickHandler('window.seatingPlanEditor?.save(true)'),
            Action::make('exportPdf')
                ->label(__('seating.export_pdf'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->extraAttributes([
                    'id' => 'seating-export-pdf-btn',
                    'wire:loading.attr' => 'disabled',
                    'wire:target' => 'exportPdf',
                ])
                ->alpineClickHandler('window.seatingPlanEditor?.exportPdf()'),
        ];
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
        ];
    }
}
