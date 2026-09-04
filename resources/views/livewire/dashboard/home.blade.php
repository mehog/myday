@php
    /** @var \App\Models\WeddingEvent|null $wedding */
    $daysUntilLabel = __('app.stat_days_until');
    $daysStat = collect($overviewStats ?? [])->first(
        fn (array $stat): bool => ($stat['label'] ?? null) === $daysUntilLabel
    );

    $daysLeft = null;
    $countdownProgress = 0;
    $countdownCircumference = 2 * M_PI * 88;

    if ($wedding && ! $wedding->isArchived() && $wedding->wedding_date) {
        $daysLeft = max(
            0,
            (int) now()->startOfDay()->diffInDays($wedding->wedding_date->copy()->startOfDay(), false)
        );
        $totalDays = max(
            1,
            (int) $wedding->created_at->copy()->startOfDay()->diffInDays($wedding->wedding_date->copy()->startOfDay())
        );
        $countdownProgress = min(100, max(0, ($daysLeft / $totalDays) * 100));
    }

    $countdownDashoffset = $countdownCircumference * (1 - ($countdownProgress / 100));
@endphp

<div class="space-y-5 lg:space-y-6">
    @if (! $wedding)
        <x-dashboard.card>
            <p class="text-sm text-muted-foreground">{{ __('dashboard.no_wedding') }}</p>
        </x-dashboard.card>
    @else
        <div class="dashboard-home-hero">
            @if ($daysLeft !== null)
                <div class="dashboard-countdown" aria-label="{{ $daysLeft }} {{ __('app.stat_days_left') }}">
                    <svg class="dashboard-countdown-ring" viewBox="0 0 200 200" aria-hidden="true">
                        <circle class="dashboard-countdown-track-outer" cx="100" cy="100" r="96" />
                        <circle class="dashboard-countdown-track" cx="100" cy="100" r="88" />
                        <circle
                            class="dashboard-countdown-progress"
                            cx="100"
                            cy="100"
                            r="88"
                            style="stroke-dasharray: {{ $countdownCircumference }}; stroke-dashoffset: {{ $countdownDashoffset }};"
                        />
                    </svg>
                    <div class="dashboard-countdown-content">
                        <p class="dashboard-countdown-value">{{ $daysLeft }}</p>
                        <p class="dashboard-countdown-label">{{ __('app.stat_days_left') }}</p>
                    </div>
                </div>
            @endif

            <div class="text-center">
                <h2 class="dashboard-home-names">
                    {{ $wedding->couple_names }}
                </h2>
                @if ($wedding->wedding_date)
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ $wedding->wedding_date->translatedFormat('d F Y') }}
                    </p>
                @endif
                @if ($wedding->isArchived())
                    <p class="mt-1 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                        {{ __('app.wedding_archived_badge') }}
                    </p>
                @elseif (! $wedding->is_active)
                    <p class="mt-1 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                        {{ __('app.invitation_inactive_suffix') }}
                    </p>
                @endif
            </div>
        </div>

        @if (! $wedding->is_active && ! $wedding->isArchived())
            <div class="flex flex-col gap-3 rounded-xl border border-amber-300/60 bg-amber-50 p-4 text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex gap-3">
                    <x-dashboard.icon name="warning" class="mt-0.5 h-5 w-5 shrink-0" />
                    <div>
                        <p class="font-medium">{{ __('app.pending_activation_title') }}</p>
                        <p class="mt-1 text-sm opacity-90">{{ __('app.pending_activation_body') }}</p>
                    </div>
                </div>
                <x-dashboard.button :href="route('dashboard.pricing')">
                    {{ __('pricing.pending_activation_cta') }}
                </x-dashboard.button>
            </div>
        @endif

        @if ($wedding->isArchived())
            <div class="dashboard-stat-scroll lg:hidden">
                <div class="dashboard-stat-chip">
                    <p class="label">{{ __('app.memories_stat_invited') }}</p>
                    <p class="value">{{ $memories['invited'] }}</p>
                </div>
                <div class="dashboard-stat-chip">
                    <p class="label">{{ __('app.memories_stat_responded') }}</p>
                    <p class="value">{{ $memories['responded'] }}</p>
                </div>
                <div class="dashboard-stat-chip">
                    <p class="label">{{ __('app.memories_stat_confirmed') }}</p>
                    <p class="value text-emerald-600 dark:text-emerald-400">{{ $memories['confirmed'] }}</p>
                </div>
                <div class="dashboard-stat-chip">
                    <p class="label">{{ __('app.memories_stat_days_since') }}</p>
                    <p class="value">{{ $memories['days_since'] }}</p>
                </div>
            </div>

            <div class="hidden gap-4 sm:grid-cols-2 xl:grid-cols-4 lg:grid">
                <x-dashboard.stat :label="__('app.memories_stat_invited')" :value="(string) $memories['invited']" />
                <x-dashboard.stat :label="__('app.memories_stat_responded')" :value="(string) $memories['responded']" />
                <x-dashboard.stat
                    :label="__('app.memories_stat_confirmed')"
                    :value="(string) $memories['confirmed']"
                    :description="__('app.memories_stat_confirmed_breakdown', [
                        'guests' => $memories['breakdown']['guests'],
                        'plus_ones' => $memories['breakdown']['plus_ones'],
                        'children' => $memories['breakdown']['children'],
                    ])"
                    tone="success"
                />
                <x-dashboard.stat :label="__('app.memories_stat_days_since')" :value="(string) $memories['days_since']" />
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <x-dashboard.card>
                    <x-slot:header>
                        <h3 class="font-medium">{{ __('app.memories_schedule_heading') }}</h3>
                    </x-slot:header>
                    @forelse ($memories['schedule'] as $item)
                        <div class="border-b border-border py-2 last:border-0">
                            <p class="text-sm font-medium">{{ $item->title }}</p>
                            @if ($item->time)
                                <p class="text-xs text-muted-foreground">{{ \Illuminate\Support\Carbon::parse($item->time)->format('H:i') }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground">{{ __('app.memories_schedule_empty') }}</p>
                    @endforelse
                </x-dashboard.card>

                <x-dashboard.card>
                    <x-slot:header>
                        <div class="flex w-full items-center justify-between gap-2">
                            <h3 class="font-medium">{{ __('app.memories_wishes_heading') }}</h3>
                            <a href="{{ route('dashboard.messages') }}" class="text-xs font-medium text-primary hover:underline">{{ __('app.memories_view_all_messages') }}</a>
                        </div>
                    </x-slot:header>
                    @forelse ($memories['textMessages'] as $message)
                        <div class="border-b border-border py-2 last:border-0">
                            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                <p class="text-sm font-medium">{{ $message->sender_name }}</p>
                                <p class="text-sm text-muted-foreground">{{ \Illuminate\Support\Str::limit($message->content, 120) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground">{{ __('app.memories_wishes_empty') }}</p>
                    @endforelse
                </x-dashboard.card>

                <x-dashboard.card>
                    <x-slot:header>
                        <div class="flex w-full items-center justify-between gap-2">
                            <h3 class="font-medium">{{ __('app.memories_videos_heading') }}</h3>
                            <a href="{{ route('dashboard.messages.videos') }}" class="text-xs font-medium text-primary hover:underline">{{ __('app.guest_messages_view_all_videos') }}</a>
                        </div>
                    </x-slot:header>
                    @forelse ($memories['videoMessages'] as $message)
                        <div class="border-b border-border py-3 last:border-0">
                            <p class="mb-2 text-sm font-medium">{{ $message->sender_name }}</p>
                            @foreach ($message->fileUrls() as $url)
                                <video controls class="w-full max-w-xs rounded-md border border-border" src="{{ $url }}"></video>
                            @endforeach
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground">{{ __('app.memories_videos_empty') }}</p>
                    @endforelse
                </x-dashboard.card>

                <x-dashboard.card>
                    <x-slot:header>
                        <div class="flex w-full items-center justify-between gap-2">
                            <h3 class="font-medium">
                                {{ __('app.memories_photos_heading') }}
                                @if ($memories['photoCount'] > 0)
                                    <span class="font-normal text-muted-foreground">({{ $memories['photoCount'] }})</span>
                                @endif
                            </h3>
                            @if ($memories['photoCount'] > 0)
                                <a href="{{ route('dashboard.messages.photos') }}" class="text-xs font-medium text-primary hover:underline">{{ __('app.guest_messages_view_all_photos') }}</a>
                            @endif
                        </div>
                    </x-slot:header>

                    @if ($memories['photoCount'] > 0)
                        <div class="mb-4 flex flex-wrap gap-2">
                            <x-dashboard.button variant="secondary" href="{{ route('guest-messages.photos.download') }}" target="_blank">
                                {{ __('app.guest_messages_download_photos') }}
                            </x-dashboard.button>
                            <x-dashboard.button variant="outline" href="{{ route('dashboard.messages.photos') }}">
                                {{ __('app.guest_messages_view_all_photos') }}
                            </x-dashboard.button>
                        </div>
                    @endif

                    @php
                        $photoPreviews = collect($memories['photoMessages'])
                            ->flatMap(fn ($message) => collect($message->fileUrls())->map(fn ($url) => [
                                'url' => $url,
                                'sender_name' => $message->sender_name,
                            ]))
                            ->take(8);
                    @endphp

                    @if ($photoPreviews->isEmpty())
                        <p class="text-sm text-muted-foreground">{{ __('app.memories_photos_empty') }}</p>
                    @else
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach ($photoPreviews as $photo)
                                <a
                                    href="{{ route('dashboard.messages.photos') }}"
                                    class="group relative aspect-square overflow-hidden rounded-xl border border-border bg-muted"
                                >
                                    <img
                                        src="{{ $photo['url'] }}"
                                        alt="{{ $photo['sender_name'] }}"
                                        class="h-full w-full object-cover transition group-hover:scale-105"
                                        loading="lazy"
                                    >
                                </a>
                            @endforeach
                        </div>
                    @endif
                </x-dashboard.card>
            </div>
        @else
            <div class="flex w-full min-w-0 justify-center border-b border-border pb-3">
                <x-dashboard.pills
                    name="tab"
                    :selected="$tab"
                    :options="[
                        'overview' => ['label' => __('app.dashboard_tab_overview'), 'icon' => 'home'],
                        'menu' => ['label' => __('app.dashboard_tab_menu_accommodation'), 'icon' => 'cake'],
                        'stats' => ['label' => __('app.dashboard_tab_statistics'), 'icon' => 'chart'],
                    ]"
                    :label="__('app.dashboard_tabs_label')"
                />
            </div>

            @if ($tab === 'overview')
                {{-- Mobile snap stats (exclude days — shown in hero) --}}
                <div class="dashboard-stat-scroll lg:hidden">
                    @foreach ($overviewStats as $stat)
                        @continue($daysStat && $stat['label'] === $daysStat['label'])
                        @php
                            $toneClass = match ($stat['tone'] ?? null) {
                                'success' => 'text-emerald-600 dark:text-emerald-400',
                                'warning' => 'text-amber-600 dark:text-amber-400',
                                default => '',
                            };
                            $chipTag = ($stat['href'] ?? null) ? 'a' : 'div';
                        @endphp
                        <{{ $chipTag }}
                            @if ($stat['href'] ?? null) href="{{ $stat['href'] }}" @endif
                            class="dashboard-stat-chip"
                        >
                            <p class="label">{{ $stat['label'] }}</p>
                            <p class="value {{ $toneClass }}">{{ $stat['value'] }}</p>
                        </{{ $chipTag }}>
                    @endforeach
                </div>

                <div class="hidden gap-4 sm:grid-cols-2 xl:grid-cols-3 lg:grid">
                    @foreach ($overviewStats as $stat)
                        <x-dashboard.stat
                            :label="$stat['label']"
                            :value="$stat['value']"
                            :description="$stat['description']"
                            :tone="$stat['tone']"
                            :href="$stat['href']"
                        />
                    @endforeach
                </div>

                @if ($checklist)
                    <x-dashboard.list-group
                        :title="__('checklist.summary_label')"
                        :action="__('checklist.view_all')"
                        :action-href="route('dashboard.checklist')"
                        class="lg:hidden"
                    >
                        <div class="dashboard-list-row items-center">
                            <div class="checklist-ring" style="--progress: {{ $checklist['percent'] }}" aria-hidden="true">
                                <span class="checklist-ring-inner">{{ $checklist['percent'] }}%</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-base font-semibold tabular-nums">
                                    {{ __('checklist.summary_value', ['completed' => $checklist['completed'], 'total' => $checklist['total']]) }}
                                </p>
                                <p class="mt-0.5 text-xs text-muted-foreground">{{ __('checklist.summary_percent', ['percent' => $checklist['percent']]) }}</p>
                            </div>
                        </div>
                        @forelse ($checklist['next'] as $row)
                            <div class="dashboard-list-row flex-col gap-0.5">
                                <p class="text-sm font-medium">{{ $row['title'] }}</p>
                                <p class="text-xs text-muted-foreground">
                                    @if ($row['due_label'])
                                        {{ $row['due_label'] }}
                                    @endif
                                    @if ($row['due_label'] && $row['progress'])
                                        ·
                                    @endif
                                    @if ($row['progress'])
                                        {{ $row['progress']['label'] }}
                                    @endif
                                </p>
                            </div>
                        @empty
                            <div class="dashboard-list-row">
                                <p class="text-sm text-muted-foreground">{{ __('checklist.next_empty') }}</p>
                            </div>
                        @endforelse
                    </x-dashboard.list-group>

                    <x-dashboard.card class="hidden lg:block">
                        <x-slot:header>
                            <div class="flex w-full items-center justify-between gap-2">
                                <h3 class="font-medium">{{ __('checklist.summary_label') }}</h3>
                                <a href="{{ route('dashboard.checklist') }}" class="text-xs font-medium text-primary hover:underline">{{ __('checklist.view_all') }}</a>
                            </div>
                        </x-slot:header>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="checklist-ring" style="--progress: {{ $checklist['percent'] }}" aria-hidden="true">
                                <span class="checklist-ring-inner">{{ $checklist['percent'] }}%</span>
                            </div>
                            <div>
                                <p class="text-xl font-semibold tabular-nums">
                                    {{ __('checklist.summary_value', ['completed' => $checklist['completed'], 'total' => $checklist['total']]) }}
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">{{ __('checklist.summary_percent', ['percent' => $checklist['percent']]) }}</p>
                            </div>
                        </div>
                        <div class="mt-4 border-t border-border pt-3">
                            <p class="mb-2 text-sm font-medium">{{ __('checklist.next_heading') }}</p>
                            @forelse ($checklist['next'] as $row)
                                <div class="border-b border-border py-2 last:border-0">
                                    <p class="text-sm font-medium">{{ $row['title'] }}</p>
                                    <p class="mt-0.5 text-xs text-muted-foreground">
                                        @if ($row['due_label'])
                                            {{ $row['due_label'] }}
                                        @endif
                                        @if ($row['due_label'] && $row['progress'])
                                            ·
                                        @endif
                                        @if ($row['progress'])
                                            {{ $row['progress']['label'] }}
                                        @endif
                                    </p>
                                </div>
                            @empty
                                <p class="text-sm text-muted-foreground">{{ __('checklist.next_empty') }}</p>
                            @endforelse
                        </div>
                    </x-dashboard.card>
                @endif

                <x-dashboard.list-group :title="__('app.recent_rsvp_notes_heading')" class="lg:hidden">
                    @forelse ($recentNotes as $guest)
                        <div class="dashboard-list-row flex-col gap-1">
                            <div class="flex w-full items-center justify-between gap-2">
                                <p class="text-sm font-medium">{{ $guest->name }}</p>
                                <p class="text-xs text-muted-foreground">{{ $guest->rsvp_responded_at?->diffForHumans() }}</p>
                            </div>
                            <p class="text-sm text-muted-foreground">{{ $guest->rsvp_note }}</p>
                        </div>
                    @empty
                        <div class="dashboard-list-row">
                            <p class="text-sm text-muted-foreground">{{ __('app.recent_rsvp_notes_empty') }}</p>
                        </div>
                    @endforelse
                </x-dashboard.list-group>

                <x-dashboard.card class="hidden lg:block">
                    <x-slot:header>
                        <h3 class="font-medium">{{ __('app.recent_rsvp_notes_heading') }}</h3>
                    </x-slot:header>
                    @forelse ($recentNotes as $guest)
                        <div class="border-b border-border py-3 last:border-0">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-medium">{{ $guest->name }}</p>
                                <p class="text-xs text-muted-foreground">{{ $guest->rsvp_responded_at?->diffForHumans() }}</p>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">{{ $guest->rsvp_note }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground">{{ __('app.recent_rsvp_notes_empty') }}</p>
                    @endforelse
                </x-dashboard.card>
            @endif

            @if ($tab === 'menu')
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-dashboard.card>
                        <x-slot:header>
                            <div>
                                <h3 class="font-medium">{{ __('app.menu_summary_heading') }}</h3>
                                <p class="text-sm text-muted-foreground">{{ __('app.menu_summary_description') }}</p>
                            </div>
                        </x-slot:header>
                        @if ($menuData['menuGroups']->isEmpty())
                            <p class="text-sm text-muted-foreground">{{ __('app.menu_summary_empty_options') }}</p>
                        @else
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ($menuData['menuGroups'] as $group)
                                    <div class="rounded-lg border border-border bg-muted/40 p-3">
                                        <p class="text-sm font-medium">{{ $group['option']->displayLabel() }}</p>
                                        <p class="mt-1 text-2xl font-semibold">{{ $group['count'] }}</p>
                                        <p class="text-xs text-muted-foreground">{{ __('app.menu_summary_people') }}</p>
                                        @if ($group['count'] === 0)
                                            <p class="mt-2 text-sm text-muted-foreground">{{ __('app.menu_summary_no_selections') }}</p>
                                        @else
                                            <ul class="mt-2 space-y-1 border-t border-border pt-2">
                                                @foreach ($group['names'] as $name)
                                                    <li class="text-sm">{{ $name }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </x-dashboard.card>

                    <x-dashboard.card>
                        <x-slot:header>
                            <div>
                                <h3 class="font-medium">{{ __('app.accommodation_summary_heading') }}</h3>
                                <p class="text-sm text-muted-foreground">{{ __('app.accommodation_summary_description') }}</p>
                            </div>
                        </x-slot:header>
                        @if (! $wedding->accommodation_enabled)
                            <p class="text-sm text-muted-foreground">{{ __('app.accommodation_summary_disabled') }}</p>
                        @elseif ($menuData['accommodationTotal'] === 0)
                            <p class="text-sm text-muted-foreground">{{ __('app.accommodation_summary_empty') }}</p>
                        @else
                            <p class="text-3xl font-semibold">{{ $menuData['accommodationTotal'] }}</p>
                            <p class="text-sm text-muted-foreground">{{ __('app.accommodation_summary_total_people') }}</p>
                            <ul class="mt-4 space-y-2 border-t border-border pt-3">
                                @foreach ($menuData['accommodationGroups'] as $group)
                                    <li class="flex justify-between text-sm">
                                        <span>{{ $group['name'] }}</span>
                                        <span class="tabular-nums text-muted-foreground">{{ __('app.accommodation_summary_group_count', ['count' => $group['count']]) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </x-dashboard.card>
                </div>
            @endif

            @if ($tab === 'stats')
                <div class="dashboard-stat-scroll lg:hidden">
                    @foreach ($visitStats as $stat)
                        <div class="dashboard-stat-chip">
                            <p class="label">{{ $stat['label'] }}</p>
                            <p class="value">{{ $stat['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="hidden gap-4 sm:grid-cols-2 xl:grid-cols-4 lg:grid">
                    @foreach ($visitStats as $stat)
                        <x-dashboard.stat
                            :label="$stat['label']"
                            :value="$stat['value']"
                            :description="$stat['description']"
                        />
                    @endforeach
                </div>

                <x-dashboard.card>
                    <x-slot:header>
                        <h3 class="font-medium">{{ __('app.stat_total_opens') }} — {{ __('dashboard.chart_last_30_days') }}</h3>
                    </x-slot:header>
                    @php
                        $max = max(1, collect($visitChart)->max('count') ?: 1);
                    @endphp
                    <div class="flex h-48 items-end gap-1 overflow-x-auto pb-6">
                        @foreach ($visitChart as $point)
                            <div class="flex min-w-[1.1rem] flex-1 flex-col items-center gap-1">
                                <div
                                    class="w-full rounded-t bg-primary/80"
                                    style="height: {{ max(4, (int) round(($point['count'] / $max) * 140)) }}px"
                                    title="{{ $point['count'] }}"
                                ></div>
                                <span class="rotate-[-55deg] text-[9px] text-muted-foreground">{{ $point['date'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-dashboard.card>
            @endif
        @endif
    @endif
</div>
