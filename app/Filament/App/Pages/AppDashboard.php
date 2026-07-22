<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Resources\MyWeddingResource;
use App\Filament\App\Widgets\RecentGuestMessagesWidget;
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

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    public static function getNavigationLabel(): string
    {
        return __('app.dashboard_label');
    }

    public function getTitle(): string
    {
        return __('app.dashboard_title');
    }

    public function mount(): void
    {
        $wedding = auth()->user()?->weddingEvent;

        if ($wedding && ! $wedding->is_active) {
            Notification::make()
                ->title(__('app.pending_activation_title'))
                ->body(__('app.pending_activation_body'))
                ->warning()
                ->persistent()
                ->actions([
                    Action::make('pricing')
                        ->label(__('pricing.pending_activation_cta'))
                        ->url(PricingPage::getUrl())
                        ->button(),
                ])
                ->send();
        }
    }

    public function getWidgets(): array
    {
        if (! auth()->user()?->weddingEvent) {
            return [];
        }

        return [
            RecentGuestMessagesWidget::class,
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
                ->label(__('app.edit_invitation'))
                ->icon('heroicon-o-pencil-square')
                ->url(MyWeddingResource::getUrl('edit', ['record' => $wedding])),
            Action::make('preview')
                ->label(__('app.preview_invitation'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url($wedding->public_url)
                ->openUrlInNewTab(),
        ];

        if ($wedding->is_active) {
            $actions[] = Action::make('copyLink')
                ->label(__('guests.copy_link'))
                ->icon('heroicon-o-clipboard')
                ->color('gray')
                ->alpineClickHandler(fn (): string => Clipboard::alpineCopy($wedding->public_url, __('guests.link_copied')));
        }

        return $actions;
    }

    public function getSubheading(): ?string
    {
        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding) {
            return __('app.no_invitation');
        }

        if (! $wedding->is_active) {
            return $wedding->couple_names.' '.__('app.invitation_inactive_suffix');
        }

        return $wedding->couple_names;
    }
}
