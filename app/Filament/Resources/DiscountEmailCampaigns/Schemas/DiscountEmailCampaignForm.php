<?php

namespace App\Filament\Resources\DiscountEmailCampaigns\Schemas;

use App\DiscountEmailAudience;
use App\Models\DiscountCode;
use App\Models\DiscountEmailTemplate;
use App\Models\User;
use App\Support\DiscountEmailPlaceholders;
use App\Support\Locale;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class DiscountEmailCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('discounts.section_campaign'))
                    ->columns(2)
                    ->schema([
                        Select::make('discount_code_id')
                            ->label(__('discounts.model_code'))
                            ->relationship('discountCode', 'code')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->helperText(__('discounts.helper_optional_code'))
                            ->default(fn (): ?int => request()->integer('discount_code_id') ?: null),
                        Select::make('discount_email_template_id')
                            ->label(__('discounts.field_template'))
                            ->options(fn (): array => DiscountEmailTemplate::query()
                                ->active()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required()
                            ->live()
                            ->helperText(__('discounts.helper_template')),
                        Select::make('audience')
                            ->label(__('discounts.field_audience'))
                            ->options(collect(DiscountEmailAudience::cases())
                                ->mapWithKeys(fn (DiscountEmailAudience $audience) => [$audience->value => $audience->label()]))
                            ->default(DiscountEmailAudience::UnpaidVerified->value)
                            ->required()
                            ->live(),
                        Select::make('user_ids')
                            ->label(__('discounts.field_users'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => User::query()
                                ->where('is_admin', false)
                                ->orderBy('name')
                                ->limit(200)
                                ->get()
                                ->mapWithKeys(fn (User $user): array => [
                                    $user->id => trim($user->name.' <'.$user->email.'>'),
                                ])
                                ->all())
                            ->getSearchResultsUsing(function (string $search): array {
                                return User::query()
                                    ->where('is_admin', false)
                                    ->where(function ($query) use ($search): void {
                                        $query->where('name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%");
                                    })
                                    ->orderBy('name')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn (User $user): array => [
                                        $user->id => trim($user->name.' <'.$user->email.'>'),
                                    ])
                                    ->all();
                            })
                            ->getOptionLabelsUsing(function (array $values): array {
                                return User::query()
                                    ->whereIn('id', $values)
                                    ->get()
                                    ->mapWithKeys(fn (User $user): array => [
                                        $user->id => trim($user->name.' <'.$user->email.'>'),
                                    ])
                                    ->all();
                            })
                            ->visible(fn (callable $get): bool => $get('audience') === DiscountEmailAudience::Manual->value)
                            ->required(fn (callable $get): bool => $get('audience') === DiscountEmailAudience::Manual->value)
                            ->helperText(__('discounts.helper_manual_users'))
                            ->columnSpanFull(),
                        Select::make('send_locale')
                            ->label(__('discounts.field_send_locale'))
                            ->options([
                                'inherit' => __('discounts.send_locale_inherit'),
                                ...Locale::options(),
                            ])
                            ->default('inherit')
                            ->live()
                            ->dehydrated()
                            ->dehydrateStateUsing(fn (?string $state): ?string => ($state === null || $state === '' || $state === 'inherit')
                                ? null
                                : $state)
                            ->required(),
                        Placeholder::make('template_help')
                            ->label('')
                            ->content(new HtmlString(
                                '<p class="text-sm text-gray-500 dark:text-gray-400">'
                                .e(__('discounts.helper_placeholders'))
                                .'<br>'
                                .e(__('discounts.helper_template_manage'))
                                .'</p>'
                            ))
                            ->columnSpanFull(),
                    ]),
                Section::make(__('discounts.section_email_preview'))
                    ->description(__('discounts.helper_email_preview'))
                    ->schema([
                        Placeholder::make('email_preview')
                            ->hiddenLabel()
                            ->content(fn (Get $get): HtmlString => static::previewHtml($get))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected static function previewHtml(Get $get): HtmlString
    {
        $templateId = $get('discount_email_template_id');
        $codeId = $get('discount_code_id');
        $sendLocale = $get('send_locale');

        if (! filled($templateId)) {
            return new HtmlString(
                '<p class="text-sm text-gray-500 dark:text-gray-400">'.e(__('discounts.preview_select_template')).'</p>'
            );
        }

        $template = DiscountEmailTemplate::query()->find($templateId);

        if ($template === null) {
            return new HtmlString(
                '<p class="text-sm text-gray-500 dark:text-gray-400">'.e(__('discounts.preview_select_template')).'</p>'
            );
        }

        $inheritsLocale = ! filled($sendLocale) || $sendLocale === 'inherit';
        $locale = Locale::resolve(
            $inheritsLocale
                ? (auth()->user() instanceof User ? auth()->user()->preferredLocale() : Locale::default())
                : (string) $sendLocale
        );

        $code = filled($codeId) ? DiscountCode::query()->find($codeId) : null;
        $sampleName = auth()->user()?->name ?: 'Ana';
        $replacements = DiscountEmailPlaceholders::for($code, $sampleName, $locale);

        $previousLocale = app()->getLocale();
        Locale::apply($locale);

        try {
            $subject = DiscountEmailPlaceholders::apply($template->subjectFor($locale), $replacements);
            $body = DiscountEmailPlaceholders::apply($template->bodyFor($locale), $replacements);
            $greeting = __('notifications.discount_email_greeting', ['name' => $sampleName]);
            $codeLine = $code !== null
                ? __('notifications.discount_email_code_line', [
                    'code' => $code->code,
                ])
                : null;
            $action = __('notifications.discount_email_action');
        } finally {
            Locale::apply($previousLocale);
        }

        $localeLabel = Locale::options()[$locale] ?? $locale;
        $localeNote = $inheritsLocale
            ? __('discounts.preview_locale_inherit_note', ['locale' => $localeLabel])
            : __('discounts.preview_locale_forced_note', ['locale' => $localeLabel]);

        $bodyLines = collect(preg_split("/\r\n|\n|\r/", $body) ?: [])
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->map(fn (string $line) => '<p class="m-0 text-sm text-gray-700 dark:text-gray-200">'.e($line).'</p>')
            ->implode('');

        return new HtmlString(
            '<div class="space-y-3">'
            .'<p class="text-xs text-gray-500 dark:text-gray-400">'.e($localeNote).'</p>'
            .'<div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">'
            .'<p class="mb-3 text-xs font-medium uppercase tracking-wide text-gray-400">'.e(__('discounts.field_subject')).'</p>'
            .'<p class="mb-4 text-base font-semibold text-gray-900 dark:text-white">'.e($subject).'</p>'
            .'<div class="space-y-2 border-t border-gray-100 pt-4 dark:border-gray-800">'
            .'<p class="m-0 text-sm font-medium text-gray-900 dark:text-white">'.e($greeting).'</p>'
            .$bodyLines
            .($codeLine !== null
                ? '<p class="m-0 text-sm text-gray-700 dark:text-gray-200">'.e($codeLine).'</p>'
                : '')
            .'<div class="pt-2">'
            .'<span class="inline-flex rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white">'
            .e($action)
            .'</span>'
            .'</div>'
            .'</div>'
            .'</div>'
            .'</div>'
        );
    }
}
