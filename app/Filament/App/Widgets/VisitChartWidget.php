<?php

namespace App\Filament\App\Widgets;

use App\Models\LinkVisit;
use Filament\Widgets\ChartWidget;

class VisitChartWidget extends ChartWidget
{
    protected ?string $heading = 'Otvoreni linkovi';

    protected ?string $description = 'Posjete pozivnici u posljednih 30 dana';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $startDate = now()->subDays(29)->startOfDay();
        $endDate = now()->endOfDay();

        $visitsByDay = LinkVisit::query()
            ->where('wedding_event_id', $wedding->id)
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->selectRaw('DATE(visited_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $labels = [];
        $data = [];

        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateKey = $date->format('Y-m-d');
            $labels[] = $date->translatedFormat('d. M');
            $data[] = $visitsByDay[$dateKey] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Otvorenja',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
