<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Services\SeedPartnerDemoWedding;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected bool $isPartner = false;

    protected ?string $referralCodeToSave = null;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->isPartner = (bool) ($data['is_partner'] ?? false)
            && ! (bool) ($data['is_admin'] ?? false);

        $rawCode = $data['referral_code'] ?? null;
        $this->referralCodeToSave = is_string($rawCode) ? trim($rawCode) : null;

        if ($this->referralCodeToSave === '') {
            $this->referralCodeToSave = null;
        }

        unset($data['referral_code'], $data['is_partner']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->markEmailAsVerified();

        if (! $this->isPartner) {
            return;
        }

        if ($this->referralCodeToSave !== null) {
            try {
                $this->record->setReferralCode($this->referralCodeToSave);
            } catch (\InvalidArgumentException $exception) {
                throw ValidationException::withMessages([
                    'data.referral_code' => $exception->getMessage(),
                ]);
            }
        } else {
            $this->record->createReferralAccount();
        }

        app(SeedPartnerDemoWedding::class)->handle($this->record);

        $this->record->refresh();

        $link = $this->record->getReferralLink();

        Notification::make()
            ->title(__('referrals.admin_partner_created_title'))
            ->body(__('referrals.admin_partner_created_body', [
                'link' => $link !== '' ? $link : '—',
            ]))
            ->success()
            ->persistent()
            ->send();
    }
}
