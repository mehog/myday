<?php

namespace App\Filament\Resources\DiscountEmailTemplates\Pages;

use App\Filament\Resources\DiscountEmailTemplates\DiscountEmailTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscountEmailTemplate extends CreateRecord
{
    protected static string $resource = DiscountEmailTemplateResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
