<?php

namespace App\Livewire;

use App\Filament\App\Pages\PricingPage;
use Livewire\Attributes\On;
use Livewire\Component;

class UpgradeRequiredModal extends Component
{
    public bool $show = false;

    #[On('open-upgrade-modal')]
    public function open(): void
    {
        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
    }

    public function pricingUrl(): string
    {
        return PricingPage::getUrl();
    }

    public function render()
    {
        return view('livewire.upgrade-required-modal');
    }
}
