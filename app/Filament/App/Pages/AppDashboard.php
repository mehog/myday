<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Resources\MyWeddingResource;
use App\Filament\App\Widgets\WeddingOverviewWidget;
use App\Support\Clipboard;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class AppDashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Pregled';

    protected static ?string $title = 'Pregled';

    public function getWidgets(): array
    {
        if (! auth()->user()?->weddingEvent) {
            return [];
        }

        return [
            WeddingOverviewWidget::class,
        ];
    }

    public function getHeaderActions(): array
    {
        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding) {
            return [];
        }

        return [
            Action::make('edit')
                ->label('Uredi pozivnicu')
                ->icon('heroicon-o-pencil-square')
                ->url(MyWeddingResource::getUrl('edit', ['record' => $wedding])),
            Action::make('preview')
                ->label('Pregled pozivnice')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url($wedding->public_url)
                ->openUrlInNewTab(),
            Action::make('copyLink')
                ->label('Kopiraj link')
                ->icon('heroicon-o-clipboard')
                ->color('gray')
                ->alpineClickHandler(fn (): string => Clipboard::alpineCopy($wedding->public_url, 'Link kopiran')),
        ];
    }

    public function getSubheading(): ?string
    {
        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding) {
            return 'Vaša pozivnica nije još kreirana. Kontaktirajte NasDan tim da vam je postavi.';
        }

        return $wedding->couple_names;
    }
}
