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

    protected static ?string $navigationLabel = 'Moje vjenčanje';

    protected static ?string $modelLabel = 'Vjenčanje';

    protected static ?string $pluralModelLabel = 'Moje vjenčanje';

    protected static ?string $recordTitleAttribute = 'couple_names';

    protected static ?string $slug = 'moje-vjencanje';

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
            ScheduleItemsRelationManager::class,
            EventPhotosRelationManager::class,
            GuestsRelationManager::class,
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
