@php
    /** @var \App\Models\WeddingEvent|null $wedding */
    /** @var \Illuminate\Support\Collection<int, array{option: \App\Models\WeddingMenuOption, count: int, names: list<string>}> $menuGroups */
    /** @var int $accommodationTotal */
    /** @var \Illuminate\Support\Collection<int, array{name: string, count: int}> $accommodationGroups */
@endphp

@if ($wedding)
    <div class="space-y-6">
        <x-filament::section
            :heading="__('app.menu_summary_heading')"
            :description="__('app.menu_summary_description')"
        >
            @if ($menuGroups->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('app.menu_summary_empty_options') }}
                </p>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($menuGroups as $group)
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-950 dark:text-white">
                                        {{ $group['option']->displayLabel() }}
                                    </p>
                                    <p class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">
                                        {{ $group['count'] }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('app.menu_summary_people') }}
                                    </p>
                                </div>
                            </div>

                            @if ($group['count'] === 0)
                                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('app.menu_summary_no_selections') }}
                                </p>
                            @else
                                <ul class="mt-4 space-y-1.5 border-t border-gray-200 pt-3 dark:border-white/10">
                                    @foreach ($group['names'] as $name)
                                        <li class="text-sm text-gray-700 dark:text-gray-300">{{ $name }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        <x-filament::section
            :heading="__('app.accommodation_summary_heading')"
            :description="__('app.accommodation_summary_description')"
        >
            @if (! $wedding->accommodation_enabled)
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('app.accommodation_summary_disabled') }}
                </p>
            @elseif ($accommodationTotal === 0)
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('app.accommodation_summary_empty') }}
                </p>
            @else
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
                    <p class="text-sm font-medium text-gray-950 dark:text-white">
                        {{ __('app.accommodation_summary_total_label') }}
                    </p>
                    <p class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">
                        {{ $accommodationTotal }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('app.accommodation_summary_total_people') }}
                    </p>

                    <ul class="mt-4 space-y-2 border-t border-gray-200 pt-3 dark:border-white/10">
                        @foreach ($accommodationGroups as $group)
                            <li class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-gray-700 dark:text-gray-300">{{ $group['name'] }}</span>
                                <span class="font-medium text-gray-950 dark:text-white">
                                    {{ __('app.accommodation_summary_group_count', ['count' => $group['count']]) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-filament::section>
    </div>
@endif
