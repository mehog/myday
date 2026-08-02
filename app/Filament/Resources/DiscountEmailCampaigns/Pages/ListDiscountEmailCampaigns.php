<?php

namespace App\Filament\Resources\DiscountEmailCampaigns\Pages;

use App\Filament\Resources\DiscountEmailCampaigns\DiscountEmailCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiscountEmailCampaigns extends ListRecords
{
    protected static string $resource = DiscountEmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
