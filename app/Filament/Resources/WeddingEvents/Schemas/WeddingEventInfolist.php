<?php

namespace App\Filament\Resources\WeddingEvents\Schemas;

use App\BudgetGuestMode;
use App\LinkType;
use App\Models\WeddingEvent;
use App\Support\MediaDisk;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class WeddingEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('groom_name'),
                        TextEntry::make('bride_name'),
                        TextEntry::make('slug'),
                        TextEntry::make('wedding_date')
                            ->dateTime(),
                        TextEntry::make('theme'),
                        TextEntry::make('link_mode'),
                        TextEntry::make('public_url')
                            ->label('Public link')
                            ->copyable(),
                        IconEntry::make('is_active')
                            ->boolean(),
                        IconEntry::make('is_demo')
                            ->label('Demo invitation')
                            ->boolean(),
                        IconEntry::make('is_marketing')
                            ->label('Marketing wedding')
                            ->boolean(),
                    ]),
                Section::make('Design')
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('hero_image')
                            ->label('Cover photo')
                            ->disk(MediaDisk::name())
                            ->height(160)
                            ->columnSpanFull()
                            ->placeholder('—'),
                        TextEntry::make('motto')
                            ->label('Wedding motto')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make('Link Visits')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('link_visits_total')
                            ->label('Total views')
                            ->getStateUsing(fn (WeddingEvent $record): int => $record->linkVisits()->count()),
                        TextEntry::make('link_visits_public')
                            ->label('Public link views')
                            ->getStateUsing(fn (WeddingEvent $record): int => $record->linkVisits()->where('link_type', LinkType::Public)->count()),
                        TextEntry::make('link_visits_personal')
                            ->label('Personal link views')
                            ->getStateUsing(fn (WeddingEvent $record): int => $record->linkVisits()->where('link_type', LinkType::Personal)->count()),
                        TextEntry::make('link_visits_last_opened')
                            ->label('Last opened')
                            ->getStateUsing(function (WeddingEvent $record): ?string {
                                $lastVisitedAt = $record->linkVisits()->max('visited_at');

                                return $lastVisitedAt
                                    ? Carbon::parse($lastVisitedAt)->diffForHumans()
                                    : null;
                            })
                            ->placeholder('—'),
                    ]),
                Section::make('Planning')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('budget_currency')
                            ->label(__('budget.currency'))
                            ->getStateUsing(fn (WeddingEvent $record): string => $record->budgetCurrency())
                            ->placeholder('—'),
                        TextEntry::make('budget_guest_mode')
                            ->label(__('budget.guest_mode'))
                            ->formatStateUsing(fn (?BudgetGuestMode $state): string => match ($state) {
                                BudgetGuestMode::Manual => __('budget.guest_mode_manual'),
                                BudgetGuestMode::Confirmed => __('budget.guest_mode_confirmed'),
                                BudgetGuestMode::Invited => __('budget.guest_mode_invited'),
                                default => __('budget.guest_mode_confirmed'),
                            }),
                        TextEntry::make('budget_guest_count_display')
                            ->label(__('budget.stat_guests'))
                            ->getStateUsing(fn (WeddingEvent $record): int => $record->budgetGuestCount()),
                        TextEntry::make('budget_target')
                            ->label(__('budget.stat_target'))
                            ->getStateUsing(fn (WeddingEvent $record): ?string => filled($record->budget_target)
                                ? number_format((float) $record->budget_target, 2, '.', '').' '.$record->budgetCurrency()
                                : null)
                            ->placeholder('—'),
                        TextEntry::make('budget_total')
                            ->label(__('budget.stat_total'))
                            ->getStateUsing(fn (WeddingEvent $record): string => number_format(
                                (float) $record->budgetTotals()['total'],
                                2,
                                '.',
                                '',
                            ).' '.$record->budgetCurrency()),
                        TextEntry::make('budget_paid')
                            ->label(__('budget.stat_paid'))
                            ->getStateUsing(fn (WeddingEvent $record): string => number_format(
                                (float) $record->budgetTotals()['paid'],
                                2,
                                '.',
                                '',
                            ).' '.$record->budgetCurrency()),
                        TextEntry::make('budget_unpaid')
                            ->label(__('budget.stat_unpaid'))
                            ->getStateUsing(fn (WeddingEvent $record): string => number_format(
                                (float) $record->budgetTotals()['unpaid'],
                                2,
                                '.',
                                '',
                            ).' '.$record->budgetCurrency()),
                        TextEntry::make('budget_items_count')
                            ->label('Budget items')
                            ->getStateUsing(fn (WeddingEvent $record): int => (int) ($record->budget_items_count ?? $record->budgetItems()->count())),
                        TextEntry::make('tasks_summary')
                            ->label(__('checklist.summary_label'))
                            ->getStateUsing(function (WeddingEvent $record): string {
                                $total = (int) ($record->tasks_count ?? $record->tasks()->count());
                                $completed = $record->completedTasksCount();

                                return __('checklist.summary_value', [
                                    'completed' => $completed,
                                    'total' => $total,
                                ]);
                            }),
                        TextEntry::make('seating_tables_count')
                            ->label('Seating tables')
                            ->getStateUsing(fn (WeddingEvent $record): int => $record->seatingTablesCount()),
                        TextEntry::make('seating_assigned_count')
                            ->label('Assigned seats')
                            ->getStateUsing(fn (WeddingEvent $record): int => $record->assignedSeatingCount()),
                    ]),
            ]);
    }
}
