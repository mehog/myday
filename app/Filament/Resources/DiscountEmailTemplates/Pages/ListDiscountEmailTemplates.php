<?php

namespace App\Filament\Resources\DiscountEmailTemplates\Pages;

use App\Filament\Resources\DiscountEmailTemplates\DiscountEmailTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiscountEmailTemplates extends ListRecords
{
    protected static string $resource = DiscountEmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
