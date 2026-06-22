<?php

namespace App\Filament\App\Resources\MyWeddingResource\Pages;

use App\Filament\App\Pages\AppDashboard;
use App\Filament\App\Resources\MyWeddingResource;
use Filament\Resources\Pages\ListRecords;

class ListMyWedding extends ListRecords
{
    protected static string $resource = MyWeddingResource::class;

    public function mount(): void
    {
        $wedding = auth()->user()?->weddingEvent;

        if ($wedding) {
            $this->redirect(MyWeddingResource::getUrl('edit', ['record' => $wedding]));

            return;
        }

        $this->redirect(AppDashboard::getUrl());
    }
}
