<?php

namespace App\Filament\App\Resources\MyWeddingResource\Pages;

use App\Filament\App\Resources\MyWeddingResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMyWedding extends EditRecord
{
    protected static string $resource = MyWeddingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->isArchived()) {
            return [];
        }

        return $data;
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->visible(fn (): bool => ! $this->record->isArchived());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label(__('app.preview_invitation'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => $this->record->public_url)
                ->openUrlInNewTab(),
        ];
    }
}
