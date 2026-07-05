<?php

namespace App\Filament\Resources\ReferralPayouts\Pages;

use App\Filament\Resources\ReferralPayouts\ReferralPayoutResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditReferralPayout extends EditRecord
{
    protected static string $resource = ReferralPayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
