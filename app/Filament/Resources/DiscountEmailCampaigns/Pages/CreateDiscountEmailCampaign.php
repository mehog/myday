<?php

namespace App\Filament\Resources\DiscountEmailCampaigns\Pages;

use App\DiscountEmailAudience;
use App\DiscountEmailCampaignStatus;
use App\Filament\Resources\DiscountEmailCampaigns\DiscountEmailCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscountEmailCampaign extends CreateRecord
{
    protected static string $resource = DiscountEmailCampaignResource::class;

    protected function getRedirectUrl(): string
    {
        return DiscountEmailCampaignResource::getUrl('view', ['record' => $this->record]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] = DiscountEmailCampaignStatus::Draft->value;

        if (($data['audience'] ?? null) !== DiscountEmailAudience::Manual->value) {
            $data['user_ids'] = null;
        }

        if (in_array($data['send_locale'] ?? null, [null, '', 'inherit'], true)) {
            $data['send_locale'] = null;
        }

        unset($data['subject'], $data['body']);

        return $data;
    }
}
