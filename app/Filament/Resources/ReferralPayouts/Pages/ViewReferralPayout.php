<?php

namespace App\Filament\Resources\ReferralPayouts\Pages;

use App\Filament\Resources\ReferralPayouts\ReferralPayoutResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewReferralPayout extends ViewRecord
{
    protected static string $resource = ReferralPayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
