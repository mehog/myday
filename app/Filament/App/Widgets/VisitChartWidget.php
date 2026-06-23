<?php

namespace App\Filament\App\Widgets;

use App\Models\LinkVisit;
use Filament\Widgets\ChartWidget;

class VisitChartWidget extends ChartWidget
{
    protected ?string $heading = null;

    protected ?string $description = null;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('app.chart_heading');
    }

    public function getDescription(): ?string
    {
        return __('app.chart_description');
    }

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
                    'label' => __('app.chart_dataset_label'),
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
