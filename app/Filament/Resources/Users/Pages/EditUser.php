<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\ReferralPayouts\ReferralPayoutResource;
use App\Filament\Resources\Users\UserResource;
use App\Support\AdminUserVerification;
use App\Traits\Referrable;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $referralCodeToSave = null;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['referral_code'] = $this->record->getReferralCode() ?? '';

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! $this->record->is_admin) {
            $this->referralCodeToSave = isset($data['referral_code'])
                ? trim((string) $data['referral_code'])
                : null;
        }

        unset($data['referral_code']);

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record->is_admin || $this->referralCodeToSave === null) {
            return;
        }

        if ($this->referralCodeToSave === '') {
            return;
        }

        $normalizedCode = Referrable::normalizeReferralCode($this->referralCodeToSave);

        if ($normalizedCode === $this->record->getReferralCode()) {
            return;
        }

        try {
            $this->record->setReferralCode($this->referralCodeToSave);
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'referral_code' => $exception->getMessage(),
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verifyEmail')
                ->label('Verify email')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Manually verify email?')
                ->modalDescription(fn (): string => "Mark {$this->record->email} as verified without the user clicking the link.")
                ->visible(fn (): bool => ! $this->record->hasVerifiedEmail())
                ->action(function (): void {
                    AdminUserVerification::verify($this->record);

                    Notification::make()
                        ->title('Email verified')
                        ->success()
                        ->send();
                }),
            Action::make('resendVerification')
                ->label('Resend verification')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Resend verification email?')
                ->modalDescription(fn (): string => "Send a new verification link to {$this->record->email}.")
                ->visible(fn (): bool => ! $this->record->hasVerifiedEmail())
                ->action(function (): void {
                    AdminUserVerification::resend($this->record);

                    Notification::make()
                        ->title('Verification email sent')
                        ->success()
                        ->send();
                }),
            Action::make('createPayout')
                ->label(__('referrals.admin_create_payout'))
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->url(fn (): string => ReferralPayoutResource::getUrl('create').'?'.http_build_query([
                    'referrer_id' => $this->record->getKey(),
                ]))
                ->visible(fn (): bool => ! $this->record->is_admin),
            Action::make('resetReferralCode')
                ->label(__('referrals.admin_reset_referral_code'))
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading(__('referrals.admin_reset_referral_code_heading'))
                ->modalDescription(__('referrals.admin_reset_referral_code_warning'))
                ->action(function (): void {
                    $code = $this->record->resetReferralCode();

                    $this->form->fill([
                        ...$this->form->getState(),
                        'referral_code' => $code,
                    ]);

                    Notification::make()
                        ->title(__('referrals.admin_reset_referral_code_success'))
                        ->body($this->record->fresh()->getReferralLink())
                        ->success()
                        ->send();
                })
                ->visible(fn (): bool => ! $this->record->is_admin),
            DeleteAction::make(),
        ];
    }
}
