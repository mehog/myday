<?php

namespace App\Filament\Resources\WeddingEvents\Pages;

use App\Filament\Resources\WeddingEvents\WeddingEventResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWeddingEvent extends ViewRecord
{
    protected static string $resource = WeddingEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview invitation')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => $this->record->public_url)
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
