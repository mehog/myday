<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Support\DashboardNav;
use Livewire\Component;

class More extends Component
{
    use RendersDashboard;

    public function render()
    {
        return $this->dashboardView('livewire.dashboard.more', [
            'items' => DashboardNav::moreItems(),
        ], __('dashboard.more_title'), [
            ['label' => __('dashboard.more_title'), 'url' => null],
        ]);
    }
}
