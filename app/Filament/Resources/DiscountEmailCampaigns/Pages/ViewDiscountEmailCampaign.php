<?php

namespace App\Filament\Resources\DiscountEmailCampaigns\Pages;

use App\DiscountEmailCampaignStatus;
use App\Filament\Resources\DiscountEmailCampaigns\DiscountEmailCampaignResource;
use App\Models\DiscountEmailCampaign;
use App\Services\DiscountCampaignSender;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Throwable;

class ViewDiscountEmailCampaign extends ViewRecord
{
    protected static string $resource = DiscountEmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => $this->record->status === DiscountEmailCampaignStatus::Draft),
            Action::make('preview')
                ->label(__('discounts.action_preview'))
                ->icon('heroicon-o-eye')
                ->visible(fn (): bool => $this->record->canSend())
                ->action(function (DiscountCampaignSender $sender): void {
                    $email = auth()->user()?->email;

                    if (! filled($email)) {
                        Notification::make()
                            ->title('No admin email available')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        $sender->preview($this->getRecord(), $email);
                        $this->record->refresh();

                        Notification::make()
                            ->title(__('discounts.preview_success', ['email' => $email]))
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('send')
                ->label(__('discounts.action_send'))
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->visible(fn (): bool => $this->record->canSend())
                ->requiresConfirmation()
                ->modalHeading(__('discounts.confirm_send_heading'))
                ->modalDescription(function (DiscountCampaignSender $sender): string {
                    $count = $sender->audienceCount($this->getRecord());

                    return __('discounts.confirm_send_description', ['count' => $count]);
                })
                ->action(function (DiscountCampaignSender $sender): void {
                    /** @var DiscountEmailCampaign $campaign */
                    $campaign = $this->getRecord();

                    if ($campaign->previewed_at === null) {
                        Notification::make()
                            ->title(__('discounts.preview_required'))
                            ->warning()
                            ->send();

                        return;
                    }

                    try {
                        $count = $sender->audienceCount($campaign);
                        $sender->send($campaign);

                        Notification::make()
                            ->title(__('discounts.send_success', ['count' => $count]))
                            ->success()
                            ->send();

                        $this->redirect(DiscountEmailCampaignResource::getUrl('view', ['record' => $campaign]));
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('resendFailed')
                ->label(__('discounts.action_resend_failed'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (): bool => $this->record->canResendFailed())
                ->requiresConfirmation()
                ->modalHeading(__('discounts.confirm_resend_heading'))
                ->modalDescription(__('discounts.confirm_resend_description'))
                ->action(function (DiscountCampaignSender $sender): void {
                    try {
                        $sender->resendFailed($this->getRecord());

                        Notification::make()
                            ->title(__('discounts.resend_success'))
                            ->success()
                            ->send();

                        $this->redirect(DiscountEmailCampaignResource::getUrl('view', ['record' => $this->record]));
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('cancel')
                ->label(__('discounts.action_cancel'))
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->visible(fn (): bool => $this->record->status === DiscountEmailCampaignStatus::Draft)
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->update([
                        'status' => DiscountEmailCampaignStatus::Cancelled,
                    ]);

                    Notification::make()
                        ->title(__('discounts.cancel_success'))
                        ->success()
                        ->send();

                    $this->redirect(DiscountEmailCampaignResource::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}
