<?php

namespace App\Filament\Widgets;

use App\Models\LinkVisit;
use Filament\Widgets\ChartWidget;

class PlatformVisitChartWidget extends ChartWidget
{
    protected ?string $heading = 'Platform link visits';

    protected ?string $description = 'All weddings combined, last 30 days';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $startDate = now()->subDays(29)->startOfDay();
        $endDate = now()->endOfDay();

        $visitsByDay = LinkVisit::query()
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
            $labels[] = $date->format('M j');
            $data[] = $visitsByDay[$dateKey] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Visits',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
