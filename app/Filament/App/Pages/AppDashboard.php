<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Resources\MyWeddingResource;
use App\Filament\App\Widgets\MenuAccommodationSummaryWidget;
use App\Filament\App\Widgets\RecentGuestMessagesWidget;
use App\Filament\App\Widgets\VisitChartWidget;
use App\Filament\App\Widgets\VisitStatsWidget;
use App\Filament\App\Widgets\WeddingMemoriesWidget;
use App\Filament\App\Widgets\WeddingOverviewWidget;
use App\Support\Clipboard;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
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
        $wedding = auth()->user()?->weddingEvent;

        if ($wedding?->isArchived()) {
            return __('app.memories_dashboard_title');
        }

        return __('app.dashboard_title');
    }

    /**
     * @return int | array<string, ?int>
     */
    public function getColumns(): int|array
    {
        if (auth()->user()?->weddingEvent?->isArchived()) {
            return 1;
        }

        return 2;
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

    public function content(Schema $schema): Schema
    {
        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding) {
            return $schema->components([]);
        }

        if ($wedding->isArchived()) {
            return $schema->components([
                Grid::make($this->getColumns())
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getWidgets())),
            ]);
        }

        return $schema->components([
            Tabs::make(__('app.dashboard_tabs_label'))
                ->persistTabInQueryString('tab')
                ->contained(false)
                ->tabs([
                    Tab::make(__('app.dashboard_tab_overview'))
                        ->icon(Heroicon::OutlinedHome)
                        ->schema([
                            Grid::make(2)
                                ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                                    RecentGuestMessagesWidget::class,
                                    WeddingOverviewWidget::class,
                                ])),
                        ]),
                    Tab::make(__('app.dashboard_tab_menu_accommodation'))
                        ->icon(Heroicon::OutlinedCake)
                        ->schema([
                            Grid::make(1)
                                ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                                    MenuAccommodationSummaryWidget::class,
                                ])),
                        ]),
                    Tab::make(__('app.dashboard_tab_statistics'))
                        ->icon(Heroicon::OutlinedChartBar)
                        ->schema([
                            Grid::make(2)
                                ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                                    VisitStatsWidget::class,
                                    VisitChartWidget::class,
                                ])),
                        ]),
                ]),
        ]);
    }

    public function getWidgets(): array
    {
        $wedding = auth()->user()?->weddingEvent;

        if (! $wedding) {
            return [];
        }

        if ($wedding->isArchived()) {
            return [
                WeddingMemoriesWidget::class,
            ];
        }

        return [
            RecentGuestMessagesWidget::class,
            WeddingOverviewWidget::class,
            MenuAccommodationSummaryWidget::class,
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
                ->label($wedding->isArchived()
                    ? __('app.view_invitation')
                    : __('app.edit_invitation'))
                ->icon($wedding->isArchived() ? 'heroicon-o-eye' : 'heroicon-o-pencil-square')
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

        if ($wedding->isArchived()) {
            return $wedding->couple_names.' — '.__('app.wedding_archived_badge');
        }

        if (! $wedding->is_active) {
            return $wedding->couple_names.' '.__('app.invitation_inactive_suffix');
        }

        return $wedding->couple_names;
    }
}
