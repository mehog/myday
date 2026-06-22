<?php

namespace App\Filament\App\Resources\MyWeddingResource\Pages;

use App\Filament\App\Resources\MyWeddingResource;
use App\Support\Clipboard;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMyWedding extends EditRecord
{
    protected static string $resource = MyWeddingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Pregled pozivnice')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => $this->record->public_url)
                ->openUrlInNewTab(),
            Action::make('copyLink')
                ->label('Kopiraj link')
                ->icon('heroicon-o-clipboard')
                ->color('gray')
                ->alpineClickHandler(fn (): string => Clipboard::alpineCopy($this->record->public_url, 'Link kopiran')),
        ];
    }
}
