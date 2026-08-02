<?php

namespace App\Filament\Resources\DiscountEmailCampaigns\Pages;

use App\DiscountEmailAudience;
use App\DiscountEmailCampaignStatus;
use App\Filament\Resources\DiscountEmailCampaigns\DiscountEmailCampaignResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDiscountEmailCampaign extends EditRecord
{
    protected static string $resource = DiscountEmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->status !== DiscountEmailCampaignStatus::Draft) {
            $this->redirect(DiscountEmailCampaignResource::getUrl('view', ['record' => $this->record]));
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['send_locale'] = $data['send_locale'] ?? 'inherit';

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['audience'] ?? null) !== DiscountEmailAudience::Manual->value) {
            $data['user_ids'] = null;
        }

        if (in_array($data['send_locale'] ?? null, [null, '', 'inherit'], true)) {
            $data['send_locale'] = null;
        }

        return $data;
    }
}
