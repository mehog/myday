<?php

namespace App\Livewire\Dashboard\Concerns;

use Illuminate\Contracts\View\View;

trait RendersDashboard
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function dashboardView(string $view, array $data = [], ?string $title = null, ?array $breadcrumbs = null): View
    {
        return view($view, $data)
            ->layout('layouts.dashboard', array_filter([
                'title' => $title,
                'breadcrumbs' => $breadcrumbs,
            ], fn ($value) => $value !== null));
    }
}
