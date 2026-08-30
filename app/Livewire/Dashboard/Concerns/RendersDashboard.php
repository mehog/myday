<?php

namespace App\Livewire\Dashboard\Concerns;

use Illuminate\Contracts\View\View;

trait RendersDashboard
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<array{label: string, url: ?string}>|null  $breadcrumbs
     */
    protected function dashboardView(
        string $view,
        array $data = [],
        ?string $title = null,
        ?array $breadcrumbs = null,
        ?string $backUrl = null,
        bool $largeTitle = false,
    ): View {
        return view($view, $data)
            ->layout('layouts.dashboard', array_filter([
                'title' => $title,
                'breadcrumbs' => $breadcrumbs,
                'backUrl' => $backUrl,
                'largeTitle' => $largeTitle,
            ], fn ($value) => $value !== null && $value !== false));
    }
}
