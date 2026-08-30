<?php

namespace App\Livewire\Dashboard;

use App\GuestMessageType;
use App\LinkType;
use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\Guest;
use App\Models\LinkVisit;
use App\Models\WeddingEvent;
use App\Models\WeddingMenuOption;
use App\RsvpStatus;
use App\Services\GuestMessageMediaGallery;
use App\Services\WeddingChecklistPresenter;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class Home extends Component
{
    use RendersDashboard;

    #[Url]
    public string $tab = 'overview';

    public function mount(): void
    {
        if (! in_array($this->tab, ['overview', 'menu', 'stats'], true)) {
            $this->tab = 'overview';
        }
    }

    public function render(WeddingChecklistPresenter $presenter)
    {
        $wedding = auth()->user()?->weddingEvent;
        $title = $wedding?->isArchived()
            ? __('app.memories_dashboard_title')
            : __('app.dashboard_title');

        return $this->dashboardView('livewire.dashboard.home', [
            'wedding' => $wedding,
            'checklist' => $this->checklistSummary($wedding, $presenter),
            'overviewStats' => $this->overviewStats($wedding),
            'recentNotes' => $this->recentNotes($wedding),
            'menuData' => $this->menuData($wedding),
            'visitStats' => $this->visitStats($wedding),
            'visitChart' => $this->visitChart($wedding),
            'memories' => $this->memories($wedding),
        ], $title, [
            ['label' => $title, 'url' => null],
        ], largeTitle: true);
    }

    /**
     * @return list<array{label: string, value: string, description: string, tone: ?string, href: ?string}>
     */
    protected function overviewStats(?WeddingEvent $wedding): array
    {
        if (! $wedding || $wedding->isArchived()) {
            return [];
        }

        $guestCount = $wedding->guests()->count();
        $plusOneInvitees = $wedding->guests()->where('plus_one_allowed', true)->count();
        $breakdown = $wedding->confirmedHeadcountBreakdown();
        $confirmed = $breakdown['total'];
        $plusOnes = $breakdown['plus_ones'];
        $children = $breakdown['children'];
        $responded = $wedding->guests()->whereNotNull('rsvp_status')->count();
        $responseRate = $guestCount > 0 ? round(($responded / $guestCount) * 100) : 0;
        $daysUntil = (int) now()->startOfDay()->diffInDays($wedding->wedding_date->copy()->startOfDay(), false);
        $messageCount = $wedding->guestMessages()->count();
        $unseenCount = $wedding->guestMessages()->whereNull('seen_at')->count();

        $confirmedDescription = match (true) {
            $plusOnes > 0 && $children > 0 => __('app.stat_confirmed_desc_plus_ones_children', [
                'plus_ones' => $plusOnes,
                'children' => $children,
            ]),
            $plusOnes > 0 => __('app.stat_confirmed_desc_plus_ones', ['count' => $plusOnes]),
            $children > 0 => __('app.stat_confirmed_desc_children', ['count' => $children]),
            default => __('app.stat_confirmed_desc'),
        };

        return [
            [
                'label' => __('app.stat_guests'),
                'value' => (string) $guestCount,
                'description' => $plusOneInvitees > 0
                    ? __('app.stat_guests_desc_plus_ones', ['count' => $plusOneInvitees])
                    : __('app.stat_guests_desc'),
                'tone' => null,
                'href' => route('dashboard.guests'),
            ],
            [
                'label' => __('app.stat_confirmed'),
                'value' => (string) $confirmed,
                'description' => $confirmedDescription,
                'tone' => 'success',
                'href' => null,
            ],
            [
                'label' => __('app.stat_responded'),
                'value' => "{$responseRate}%",
                'description' => __('app.stat_responded_desc', ['responded' => $responded, 'total' => $guestCount]),
                'tone' => null,
                'href' => null,
            ],
            [
                'label' => __('app.stat_days_until'),
                'value' => $daysUntil >= 0
                    ? __('app.stat_days_value', ['days' => $daysUntil])
                    : __('app.stat_days_passed'),
                'description' => $wedding->wedding_date->translatedFormat('d. F Y.'),
                'tone' => null,
                'href' => null,
            ],
            [
                'label' => __('app.stat_messages'),
                'value' => (string) $messageCount,
                'description' => $unseenCount > 0
                    ? __('app.stat_messages_unseen', ['count' => $unseenCount])
                    : __('app.stat_messages_desc'),
                'tone' => $unseenCount > 0 ? 'warning' : null,
                'href' => route('dashboard.messages'),
            ],
        ];
    }

    /**
     * @return array{total: int, completed: int, percent: int, next: Collection<int, array<string, mixed>>}|null
     */
    protected function checklistSummary(?WeddingEvent $wedding, WeddingChecklistPresenter $presenter): ?array
    {
        if (! $wedding || $wedding->isArchived()) {
            return null;
        }

        return $presenter->summary($wedding);
    }

    /**
     * @return Collection<int, Guest>
     */
    protected function recentNotes(?WeddingEvent $wedding): Collection
    {
        if (! $wedding || $wedding->isArchived()) {
            return collect();
        }

        return $wedding->guests()
            ->whereNotNull('rsvp_note')
            ->where('rsvp_note', '!=', '')
            ->whereNotNull('rsvp_responded_at')
            ->latest('rsvp_responded_at')
            ->limit(5)
            ->get();
    }

    /**
     * @return array{menuGroups: Collection, accommodationTotal: int, accommodationGroups: Collection}
     */
    protected function menuData(?WeddingEvent $wedding): array
    {
        if (! $wedding || $wedding->isArchived()) {
            return [
                'menuGroups' => collect(),
                'accommodationTotal' => 0,
                'accommodationGroups' => collect(),
            ];
        }

        $guests = $wedding->guests()
            ->with(['menuOption', 'plusOneMenuOption', 'children.menuOption'])
            ->where('rsvp_status', RsvpStatus::Yes)
            ->orderBy('name')
            ->get();

        $menuGroups = $wedding->menuOptions()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (WeddingMenuOption $option) use ($guests): array {
                $names = [];

                foreach ($guests as $guest) {
                    if ($guest->menu_option_id === $option->id) {
                        $names[] = $guest->name;
                    }

                    if (filled($guest->plus_one_name) && $guest->plus_one_menu_option_id === $option->id) {
                        $names[] = $guest->plus_one_name;
                    }

                    foreach ($guest->children as $child) {
                        if ($child->menu_option_id === $option->id) {
                            $names[] = $child->displayName();
                        }
                    }
                }

                return [
                    'option' => $option,
                    'count' => count($names),
                    'names' => $names,
                ];
            });

        $accommodationGroups = $guests
            ->filter(fn (Guest $guest): bool => ($guest->accommodation_count ?? 0) > 0)
            ->map(fn (Guest $guest): array => [
                'name' => $guest->name,
                'count' => (int) $guest->accommodation_count,
            ])
            ->values();

        return [
            'menuGroups' => $menuGroups,
            'accommodationTotal' => (int) $accommodationGroups->sum('count'),
            'accommodationGroups' => $accommodationGroups,
        ];
    }

    /**
     * @return list<array{label: string, value: string, description: string}>
     */
    protected function visitStats(?WeddingEvent $wedding): array
    {
        if (! $wedding || $wedding->isArchived()) {
            return [];
        }

        $baseQuery = LinkVisit::query()->where('wedding_event_id', $wedding->id);
        $totalViews = (clone $baseQuery)->count();
        $thisMonthViews = (clone $baseQuery)->where('visited_at', '>=', now()->startOfMonth())->count();
        $uniqueVisitorsThisMonth = (clone $baseQuery)
            ->where('visited_at', '>=', now()->startOfMonth())
            ->whereNotNull('ip_hash')
            ->distinct('ip_hash')
            ->count('ip_hash');
        $personalOpens = (clone $baseQuery)->where('link_type', LinkType::Personal)->count();

        return [
            [
                'label' => __('app.stat_total_opens'),
                'value' => (string) $totalViews,
                'description' => __('app.stat_total_opens_desc'),
            ],
            [
                'label' => __('app.stat_this_month'),
                'value' => (string) $thisMonthViews,
                'description' => __('app.stat_this_month_desc'),
            ],
            [
                'label' => __('app.stat_unique_visitors'),
                'value' => (string) $uniqueVisitorsThisMonth,
                'description' => __('app.stat_unique_visitors_desc'),
            ],
            [
                'label' => __('app.stat_personal_opens'),
                'value' => (string) $personalOpens,
                'description' => __('app.stat_personal_opens_desc'),
            ],
        ];
    }

    /**
     * @return list<array{date: string, count: int}>
     */
    protected function visitChart(?WeddingEvent $wedding): array
    {
        if (! $wedding || $wedding->isArchived()) {
            return [];
        }

        $days = collect(range(29, 0))->map(fn (int $i) => now()->subDays($i)->startOfDay());
        $counts = LinkVisit::query()
            ->where('wedding_event_id', $wedding->id)
            ->where('visited_at', '>=', now()->subDays(29)->startOfDay())
            ->get()
            ->groupBy(fn (LinkVisit $visit) => $visit->visited_at->toDateString())
            ->map->count();

        return $days->map(fn ($day) => [
            'date' => $day->format('d.m'),
            'count' => (int) ($counts[$day->toDateString()] ?? 0),
        ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function memories(?WeddingEvent $wedding): array
    {
        if (! $wedding?->isArchived()) {
            return [];
        }

        $breakdown = $wedding->confirmedHeadcountBreakdown();
        $daysSince = (int) $wedding->wedding_date->copy()->startOfDay()->diffInDays(now()->startOfDay());

        return [
            'days_since' => max(0, $daysSince),
            'invited' => $wedding->guests()->count(),
            'responded' => $wedding->guests()->whereNotNull('rsvp_status')->count(),
            'confirmed' => $breakdown['total'],
            'breakdown' => $breakdown,
            'schedule' => $wedding->scheduleItems()->orderBy('sort_order')->get(),
            'textMessages' => $wedding->guestMessages()
                ->where('type', GuestMessageType::Text)
                ->latest()
                ->limit(5)
                ->get(),
            'audioMessages' => $wedding->guestMessages()
                ->where('type', GuestMessageType::Audio)
                ->latest()
                ->limit(5)
                ->get(),
            'photoMessages' => $wedding->guestMessages()
                ->where('type', GuestMessageType::Photo)
                ->whereNotNull('file_paths')
                ->latest()
                ->limit(8)
                ->get(),
            'photoCount' => app(GuestMessageMediaGallery::class)->countPhotos($wedding),
            'videoMessages' => $wedding->guestMessages()
                ->where('type', GuestMessageType::Video)
                ->whereNotNull('file_paths')
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }
}
