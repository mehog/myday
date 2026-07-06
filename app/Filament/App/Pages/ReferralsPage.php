<?php

namespace App\Filament\App\Pages;

use App\Support\Clipboard;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ReferralsPage extends Page
{
    use CanUseDatabaseTransactions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected static ?string $slug = 'referrals';

    protected static ?int $navigationSort = 50;

    protected string $view = 'filament.app.pages.referrals-page';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $payoutData = [];

    public static function getNavigationLabel(): string
    {
        return __('referrals.nav_label');
    }

    public function getTitle(): string
    {
        return __('referrals.page_title');
    }

    public function mount(): void
    {
        $user = auth()->user();

        if ($user !== null && ! $user->hasReferralAccount()) {
            $user->createReferralAccount();
        }

        $this->fillPayoutForm();
    }

    public function getReferralLink(): string
    {
        return auth()->user()?->getReferralLink() ?? '';
    }

    public function getReferralFeePercentage(): float
    {
        return auth()->user()?->referralFeePercentage() ?? (float) config('referral.default_fee', 10);
    }

    protected function getHeaderActions(): array
    {
        $link = $this->getReferralLink();

        if ($link === '') {
            return [];
        }

        return [
            ActionGroup::make([
                Action::make('qrA4')
                    ->label(__('referrals.qr_format_a4'))
                    ->icon('heroicon-o-document')
                    ->url(route('referrals.qr-code.download', ['format' => 'a4']), shouldOpenInNewTab: true),
                Action::make('qrA5')
                    ->label(__('referrals.qr_format_a5'))
                    ->icon('heroicon-o-document')
                    ->url(route('referrals.qr-code.download', ['format' => 'a5']), shouldOpenInNewTab: true),
                Action::make('qrLetter')
                    ->label(__('referrals.qr_format_letter'))
                    ->icon('heroicon-o-document')
                    ->url(route('referrals.qr-code.download', ['format' => 'letter']), shouldOpenInNewTab: true),
            ])
                ->label(__('referrals.download_qr'))
                ->icon('heroicon-o-qr-code')
                ->color('primary')
                ->button(),
            Action::make('downloadBrochure')
                ->label(__('referrals.download_brochure'))
                ->icon('heroicon-o-newspaper')
                ->color('gray')
                ->url(route('referrals.brochure.download'), shouldOpenInNewTab: true),
            Action::make('copyLink')
                ->label(__('referrals.copy_link'))
                ->icon('heroicon-o-clipboard')
                ->color('gray')
                ->alpineClickHandler(fn (): string => Clipboard::alpineCopy($link, __('referrals.link_copied'))),
        ];
    }

    public function getSubheading(): ?string
    {
        return __('referrals.page_subheading', [
            'fee' => number_format($this->getReferralFeePercentage(), 0),
        ]);
    }

    public function defaultPayoutForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('payoutData')
            ->model(auth()->user());
    }

    public function payoutForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('paypal_email')
                    ->label(__('referrals.paypal_email'))
                    ->email()
                    ->maxLength(255),
                Textarea::make('bank_account_info')
                    ->label(__('referrals.bank_account_info'))
                    ->rows(4)
                    ->maxLength(2000)
                    ->helperText(__('referrals.bank_account_helper')),
            ]);
    }

    public function savePayoutInfo(): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        $data = $this->payoutForm->getState();

        $user->update([
            'paypal_email' => $data['paypal_email'] ?? null,
            'bank_account_info' => $data['bank_account_info'] ?? null,
        ]);

        Notification::make()
            ->title(__('referrals.payout_details_saved'))
            ->success()
            ->send();
    }

    protected function fillPayoutForm(): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        $this->payoutForm->fill([
            'paypal_email' => $user->paypal_email,
            'bank_account_info' => $user->bank_account_info,
        ]);
    }
}
