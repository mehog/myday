<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\MyWeddingResource\Pages\EditMyWedding;
use App\Filament\App\Resources\MyWeddingResource\Pages\ListMyWedding;
use App\Filament\App\Schemas\MyWeddingForm;
use App\Filament\Resources\WeddingEvents\RelationManagers\EventPhotosRelationManager;
use App\Filament\Resources\WeddingEvents\RelationManagers\GuestsRelationManager;
use App\Filament\Resources\WeddingEvents\RelationManagers\ScheduleItemsRelationManager;
use App\Models\WeddingEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyWeddingResource extends Resource
{
    protected static ?string $model = WeddingEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static ?string $recordTitleAttribute = 'couple_names';

    protected static ?string $slug = 'moje-vjencanje';

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('app.nav_my_wedding');
    }

    public static function getModelLabel(): string
    {
        return __('app.model_label_wedding');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.nav_my_wedding');
    }

    public static function form(Schema $schema): Schema
    {
        return MyWeddingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function getRelations(): array
    {
        return [
            GuestsRelationManager::class,
            ScheduleItemsRelationManager::class,
            EventPhotosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyWedding::route('/'),
            'edit' => EditMyWedding::route('/{record}/edit'),
        ];
    }
}
