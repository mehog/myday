<?php

namespace App\Filament\Resources\DiscountEmailCampaigns\Schemas;

use App\DiscountEmailAudience;
use App\DiscountEmailCampaignStatus;
use App\Models\DiscountEmailCampaign;
use App\Support\Locale;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class DiscountEmailCampaignInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('discounts.section_campaign'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('discountCode.code')
                            ->label(__('discounts.field_code'))
                            ->placeholder('—')
                            ->copyable(),
                        TextEntry::make('template.name')
                            ->label(__('discounts.field_template'))
                            ->placeholder('—'),
                        TextEntry::make('status')
                            ->label(__('discounts.field_status'))
                            ->badge()
                            ->formatStateUsing(fn (?DiscountEmailCampaignStatus $state): ?string => $state?->label())
                            ->color(fn (?DiscountEmailCampaignStatus $state): string => match ($state) {
                                DiscountEmailCampaignStatus::Sent => 'success',
                                DiscountEmailCampaignStatus::Sending => 'info',
                                DiscountEmailCampaignStatus::Failed => 'danger',
                                DiscountEmailCampaignStatus::Cancelled => 'gray',
                                default => 'warning',
                            }),
                        TextEntry::make('audience')
                            ->label(__('discounts.field_audience'))
                            ->formatStateUsing(fn (?DiscountEmailAudience $state): ?string => $state?->label()),
                        TextEntry::make('send_locale')
                            ->label(__('discounts.field_send_locale'))
                            ->formatStateUsing(fn (?string $state): string => $state
                                ? (Locale::options()[$state] ?? $state)
                                : __('discounts.send_locale_inherit')),
                        TextEntry::make('resolved_subject')
                            ->label(__('discounts.field_subject'))
                            ->state(fn (DiscountEmailCampaign $record): string => $record->renderedSubject())
                            ->columnSpanFull(),
                        TextEntry::make('resolved_body')
                            ->label(__('discounts.field_body'))
                            ->state(function (DiscountEmailCampaign $record): HtmlString {
                                $body = e($record->renderedBody());
                                $body = nl2br($body);

                                return new HtmlString(
                                    '<div class="rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">'
                                    .$body
                                    .'</div>'
                                );
                            })
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('previewed_at')
                            ->label(__('discounts.field_previewed_at'))
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('sent_at')
                            ->label(__('discounts.field_sent_at'))
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('sent_count')
                            ->label(__('discounts.field_emails_sent'))
                            ->state(fn (DiscountEmailCampaign $record): int => $record->sentRecipientsCount()),
                        TextEntry::make('failed_count')
                            ->label(__('discounts.recipient_status_failed'))
                            ->state(fn (DiscountEmailCampaign $record): int => $record->failedRecipientsCount()),
                    ]),
            ]);
    }
}
