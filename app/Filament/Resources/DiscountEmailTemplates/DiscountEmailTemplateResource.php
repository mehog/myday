<?php

namespace App\Filament\Resources\DiscountEmailTemplates;

use App\Filament\Resources\DiscountEmailTemplates\Pages\CreateDiscountEmailTemplate;
use App\Filament\Resources\DiscountEmailTemplates\Pages\EditDiscountEmailTemplate;
use App\Filament\Resources\DiscountEmailTemplates\Pages\ListDiscountEmailTemplates;
use App\Filament\Resources\DiscountEmailTemplates\Schemas\DiscountEmailTemplateForm;
use App\Filament\Resources\DiscountEmailTemplates\Tables\DiscountEmailTemplatesTable;
use App\Models\DiscountEmailTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DiscountEmailTemplateResource extends Resource
{
    protected static ?string $model = DiscountEmailTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 39;

    public static function getNavigationLabel(): string
    {
        return __('discounts.nav_templates');
    }

    public static function getModelLabel(): string
    {
        return __('discounts.model_template');
    }

    public static function getPluralModelLabel(): string
    {
        return __('discounts.plural_templates');
    }

    public static function form(Schema $schema): Schema
    {
        return DiscountEmailTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiscountEmailTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiscountEmailTemplates::route('/'),
            'create' => CreateDiscountEmailTemplate::route('/create'),
            'edit' => EditDiscountEmailTemplate::route('/{record}/edit'),
        ];
    }
}
