<?php

namespace App\Filament\Resources\ReferralPayouts;

use App\Filament\Resources\ReferralPayouts\Pages\CreateReferralPayout;
use App\Filament\Resources\ReferralPayouts\Pages\EditReferralPayout;
use App\Filament\Resources\ReferralPayouts\Pages\ListReferralPayouts;
use App\Filament\Resources\ReferralPayouts\Pages\ViewReferralPayout;
use App\Filament\Resources\ReferralPayouts\Schemas\ReferralPayoutForm;
use App\Filament\Resources\ReferralPayouts\Schemas\ReferralPayoutInfolist;
use App\Filament\Resources\ReferralPayouts\Tables\ReferralPayoutsTable;
use App\Models\ReferralPayout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReferralPayoutResource extends Resource
{
    protected static ?string $model = ReferralPayout::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static ?string $recordTitleAttribute = 'period';

    public static function getNavigationLabel(): string
    {
        return __('referrals.admin_nav_label');
    }

    public static function getModelLabel(): string
    {
        return __('referrals.admin_model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('referrals.admin_plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return ReferralPayoutForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReferralPayoutInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReferralPayoutsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReferralPayouts::route('/'),
            'create' => CreateReferralPayout::route('/create'),
            'view' => ViewReferralPayout::route('/{record}'),
            'edit' => EditReferralPayout::route('/{record}/edit'),
        ];
    }
}
