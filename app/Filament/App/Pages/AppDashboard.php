<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Resources\MyWeddingResource;
use App\Filament\App\Widgets\VisitChartWidget;
use App\Filament\App\Widgets\VisitStatsWidget;
use App\Filament\App\Widgets\WeddingOverviewWidget;
use App\Support\Clipboard;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class AppDashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Pregled';

    protected static ?string $title = 'Pregled';

    public function mount(): void
    {
        $wedding = auth()->user()?->weddingEvent;

        if ($wedding && ! $wedding->is_active) {
            Notification::make()
                ->title('Pozivnica čeka aktivaciju')
                ->body('Link će biti aktivan nakon potvrde uplate i odobrenja administratora. Do tada možete pregledati pozivnicu.')
                ->warning()
                ->persistent()
                ->send();
        }
    }

    public function getWidgets(): array
    {
        if (! auth()->user()?->weddingEvent) {
            return [];
        }

        return [
            WeddingOverviewWidget::class,
            VisitStatsWidget::class,
            VisitChartWidget::class,
        ];
    }

    public function getHeaderActions(): array
    {
        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding) {
            return [];
        }

        $actions = [
            Action::make('edit')
                ->label('Uredi pozivnicu')
                ->icon('heroicon-o-pencil-square')
                ->url(MyWeddingResource::getUrl('edit', ['record' => $wedding])),
            Action::make('preview')
                ->label('Pregled pozivnice')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url($wedding->public_url)
                ->openUrlInNewTab(),
        ];

        if ($wedding->is_active) {
            $actions[] = Action::make('copyLink')
                ->label('Kopiraj link')
                ->icon('heroicon-o-clipboard')
                ->color('gray')
                ->alpineClickHandler(fn (): string => Clipboard::alpineCopy($wedding->public_url, 'Link kopiran'));
        }

        return $actions;
    }

    public function getSubheading(): ?string
    {
        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding) {
            return 'Vaša pozivnica nije još kreirana. Kontaktirajte NasDan tim da vam je postavi.';
        }

        if (! $wedding->is_active) {
            return $wedding->couple_names.' — link nije još aktivan';
        }

        return $wedding->couple_names;
    }
}
