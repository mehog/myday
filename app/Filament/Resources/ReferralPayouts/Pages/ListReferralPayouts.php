<?php

namespace App\Filament\Resources\ReferralPayouts\Pages;

use App\Filament\Resources\ReferralPayouts\ReferralPayoutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReferralPayouts extends ListRecords
{
    protected static string $resource = ReferralPayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
