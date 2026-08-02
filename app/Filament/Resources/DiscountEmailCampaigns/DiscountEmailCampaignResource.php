<?php

namespace App\Filament\Resources\DiscountEmailCampaigns;

use App\Filament\Resources\DiscountEmailCampaigns\Pages\CreateDiscountEmailCampaign;
use App\Filament\Resources\DiscountEmailCampaigns\Pages\EditDiscountEmailCampaign;
use App\Filament\Resources\DiscountEmailCampaigns\Pages\ListDiscountEmailCampaigns;
use App\Filament\Resources\DiscountEmailCampaigns\Pages\ViewDiscountEmailCampaign;
use App\Filament\Resources\DiscountEmailCampaigns\RelationManagers\RecipientsRelationManager;
use App\Filament\Resources\DiscountEmailCampaigns\Schemas\DiscountEmailCampaignForm;
use App\Filament\Resources\DiscountEmailCampaigns\Schemas\DiscountEmailCampaignInfolist;
use App\Filament\Resources\DiscountEmailCampaigns\Tables\DiscountEmailCampaignsTable;
use App\Models\DiscountEmailCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class DiscountEmailCampaignResource extends Resource
{
    protected static ?string $model = DiscountEmailCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = 41;

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        if (! $record instanceof DiscountEmailCampaign) {
            return parent::getRecordTitle($record);
        }

        $title = $record->renderedSubject();

        return $title !== '' ? $title : (string) ($record->template?->name ?? __('discounts.model_campaign'));
    }

    public static function getNavigationLabel(): string
    {
        return __('discounts.nav_campaigns');
    }

    public static function getModelLabel(): string
    {
        return __('discounts.model_campaign');
    }

    public static function getPluralModelLabel(): string
    {
        return __('discounts.plural_campaigns');
    }

    public static function form(Schema $schema): Schema
    {
        return DiscountEmailCampaignForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DiscountEmailCampaignInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiscountEmailCampaignsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RecipientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiscountEmailCampaigns::route('/'),
            'create' => CreateDiscountEmailCampaign::route('/create'),
            'view' => ViewDiscountEmailCampaign::route('/{record}'),
            'edit' => EditDiscountEmailCampaign::route('/{record}/edit'),
        ];
    }
}
