<?php

namespace App\Filament\App\Widgets;

use App\Models\Guest;
use App\Models\WeddingEvent;
use App\Models\WeddingMenuOption;
use App\RsvpStatus;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class MenuAccommodationSummaryWidget extends Widget
{
    protected string $view = 'filament.app.widgets.menu-accommodation-summary';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $wedding = auth()->user()?->weddingEvent;

        return $wedding instanceof WeddingEvent && ! $wedding->isArchived();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding instanceof WeddingEvent) {
            return [
                'wedding' => null,
                'menuGroups' => collect(),
                'accommodationTotal' => 0,
                'accommodationGroups' => collect(),
            ];
        }

        $guests = $wedding->guests()
            ->with(['menuOption', 'plusOneMenuOption', 'children.menuOption'])
            ->where('rsvp_status', RsvpStatus::Yes)
            ->orderBy('name')
            ->get();

        $menuOptions = $wedding->menuOptions()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        /** @var Collection<int, array{option: WeddingMenuOption, count: int, names: list<string>}> $menuGroups */
        $menuGroups = $menuOptions->map(function (WeddingMenuOption $option) use ($guests): array {
            $names = [];

            foreach ($guests as $guest) {
                if ($guest->menu_option_id === $option->id) {
                    $names[] = $guest->name;
                }

                if (
                    filled($guest->plus_one_name)
                    && $guest->plus_one_menu_option_id === $option->id
                ) {
                    $names[] = $guest->plus_one_name;
                }

                foreach ($guest->children as $child) {
                    if ($child->menu_option_id === $option->id) {
                        $names[] = $child->displayName();
                    }
                }
            }

            return [
                'option' => $option,
                'count' => count($names),
                'names' => $names,
            ];
        });

        $accommodationGroups = $guests
            ->filter(fn (Guest $guest): bool => ($guest->accommodation_count ?? 0) > 0)
            ->map(fn (Guest $guest): array => [
                'name' => $guest->name,
                'count' => (int) $guest->accommodation_count,
            ])
            ->values();

        $accommodationTotal = (int) $accommodationGroups->sum('count');

        return [
            'wedding' => $wedding,
            'menuGroups' => $menuGroups,
            'accommodationTotal' => $accommodationTotal,
            'accommodationGroups' => $accommodationGroups,
        ];
    }
}
