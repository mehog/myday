@php
    /** @var \App\Models\WeddingEvent|null $wedding */
@endphp

<div class="space-y-6">
    @if (! $wedding)
        <x-dashboard.card>
            <p class="text-sm text-muted-foreground">{{ __('dashboard.no_wedding') }}</p>
        </x-dashboard.card>
    @else
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight">
                    {{ $wedding->isArchived() ? __('app.memories_dashboard_title') : __('app.dashboard_title') }}
                </h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    @if ($wedding->isArchived())
                        {{ $wedding->couple_names }} — {{ __('app.wedding_archived_badge') }}
                    @elseif (! $wedding->is_active)
                        {{ $wedding->couple_names }} {{ __('app.invitation_inactive_suffix') }}
                    @else
                        {{ $wedding->couple_names }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-dashboard.button variant="secondary" :href="route('dashboard.wedding')">
                    <x-dashboard.icon name="pencil" class="h-4 w-4" />
                    {{ $wedding->isArchived() ? __('app.view_invitation') : __('app.edit_invitation') }}
                </x-dashboard.button>
                <x-dashboard.button variant="outline" :href="$wedding->public_url" target="_blank" rel="noopener">
                    <x-dashboard.icon name="external" class="h-4 w-4" />
                    {{ __('app.preview_invitation') }}
                </x-dashboard.button>
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
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
            </div>
        @else
            <div class="flex justify-center border-b border-border pb-3">
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
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
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

                <x-dashboard.card>
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
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
