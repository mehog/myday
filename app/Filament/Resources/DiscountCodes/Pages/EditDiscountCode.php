<?php

namespace App\Filament\Resources\DiscountCodes\Pages;

use App\Filament\Resources\DiscountCodes\DiscountCodeResource;
use App\Filament\Resources\DiscountEmailCampaigns\DiscountEmailCampaignResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditDiscountCode extends EditRecord
{
    protected static string $resource = DiscountCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendEmail')
                ->label(__('discounts.action_send_email'))
                ->icon('heroicon-o-envelope')
                ->url(fn (): string => DiscountEmailCampaignResource::getUrl('create', [
                    'discount_code_id' => $this->record->id,
                ])),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['type'] ?? null) !== 'flat') {
            $data['currency'] = null;
        }

        return $data;
    }
}
