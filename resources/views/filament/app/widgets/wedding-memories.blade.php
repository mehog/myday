@php
    /** @var \App\Models\WeddingEvent|null $wedding */
@endphp

@if ($wedding)
    <x-filament::section
        :heading="__('app.memories_heading')"
        :description="__('app.memories_subheading', [
            'couple' => $wedding->couple_names,
            'date' => $wedding->wedding_date->translatedFormat('d. F Y.'),
            'days' => $daysSince,
        ])"
    >
        <div class="space-y-6 sm:space-y-8">
            <div class="grid grid-cols-2 gap-2 sm:gap-3">
                <x-filament::button
                    tag="a"
                    :href="$previewUrl"
                    target="_blank"
                    color="gray"
                    icon="heroicon-o-arrow-top-right-on-square"
                    class="w-full justify-center"
                >
                    {{ __('app.preview_invitation') }}
                </x-filament::button>
                <x-filament::button
                    tag="a"
                    :href="$weddingUrl"
                    color="gray"
                    icon="heroicon-o-eye"
                    class="w-full justify-center"
                >
                    {{ __('app.view_invitation') }}
                </x-filament::button>
            </div>

            <div class="grid grid-cols-2 gap-3 xl:grid-cols-4 xl:gap-4">
                <div class="rounded-xl bg-gray-50 p-3 sm:p-4 dark:bg-white/5">
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">{{ __('app.memories_stat_invited') }}</p>
                    <p class="mt-1 text-xl sm:text-2xl font-semibold text-gray-950 dark:text-white">{{ $guestCount }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3 sm:p-4 dark:bg-white/5">
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">{{ __('app.memories_stat_responded') }}</p>
                    <p class="mt-1 text-xl sm:text-2xl font-semibold text-gray-950 dark:text-white">{{ $responded }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3 sm:p-4 dark:bg-white/5">
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">{{ __('app.memories_stat_confirmed') }}</p>
                    <p class="mt-1 text-xl sm:text-2xl font-semibold text-gray-950 dark:text-white">{{ $confirmedTotal }}</p>
                    <p class="mt-1 text-[11px] sm:text-xs leading-snug text-gray-500 dark:text-gray-400">
                        {{ __('app.memories_stat_confirmed_breakdown', [
                            'guests' => $confirmedGuests,
                            'plus_ones' => $plusOnes,
                            'children' => $children,
                        ]) }}
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3 sm:p-4 dark:bg-white/5">
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">{{ __('app.memories_stat_days_since') }}</p>
                    <p class="mt-1 text-xl sm:text-2xl font-semibold text-gray-950 dark:text-white">
                        {{ __('app.stat_days_value', ['days' => $daysSince]) }}
                    </p>
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-gray-950 dark:text-white">
                    {{ __('app.memories_schedule_heading') }}
                </h3>

                @if ($scheduleItems->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.memories_schedule_empty') }}</p>
                @else
                    <div class="divide-y divide-gray-100 overflow-hidden rounded-xl border border-gray-200 dark:divide-white/10 dark:border-white/10">
                        @foreach ($scheduleItems as $item)
                            <div class="flex gap-3 px-3 py-3 sm:gap-4 sm:px-4">
                                <div class="w-12 shrink-0 text-sm font-semibold text-primary-600 sm:w-16 dark:text-primary-400">
                                    {{ \Illuminate\Support\Carbon::parse($item->time)->format('H:i') }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $item->title }}</p>
                                    @if (filled($item->description))
                                        <p class="mt-1 text-sm break-words text-gray-500 dark:text-gray-400">{{ $item->description }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="min-w-0">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="min-w-0 text-sm font-semibold text-gray-950 dark:text-white">
                            {{ __('app.memories_wishes_heading') }}
                            <span class="font-normal text-gray-500 dark:text-gray-400">({{ $textCount }})</span>
                        </h3>
                        <x-filament::link :href="$messagesUrl" size="sm" class="shrink-0">
                            {{ __('app.memories_view_all_messages') }}
                        </x-filament::link>
                    </div>

                    @if ($textMessages->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.memories_wishes_empty') }}</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($textMessages as $message)
                                <div class="rounded-xl border border-gray-200 p-3 sm:p-4 dark:border-white/10">
                                    <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $message->sender_name }}</p>
                                    <p class="mt-2 text-sm break-words text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($message->content, 160) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="min-w-0">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="min-w-0 text-sm font-semibold text-gray-950 dark:text-white">
                            {{ __('app.memories_audio_heading') }}
                            <span class="font-normal text-gray-500 dark:text-gray-400">({{ $audioCount }})</span>
                        </h3>
                        <x-filament::link :href="$messagesUrl" size="sm" class="shrink-0">
                            {{ __('app.memories_view_all_messages') }}
                        </x-filament::link>
                    </div>

                    @if ($audioMessages->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.memories_audio_empty') }}</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($audioMessages as $message)
                                <div class="rounded-xl border border-gray-200 p-3 sm:p-4 dark:border-white/10">
                                    <p class="mb-2 text-sm font-medium text-gray-950 dark:text-white">{{ $message->sender_name }}</p>
                                    @if ($message->fileUrl())
                                        <audio controls class="w-full max-w-full" src="{{ $message->fileUrl() }}"></audio>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-gray-950 dark:text-white">
                    {{ __('app.memories_photos_heading') }}
                    <span class="font-normal text-gray-500 dark:text-gray-400">({{ $photoCount }})</span>
                </h3>

                <div @class([
                    'mb-4 grid gap-2 sm:gap-3',
                    'grid-cols-1' => $photoCount === 0,
                    'grid-cols-2' => $photoCount > 0,
                ])>
                    @if ($photoCount > 0)
                        <x-filament::button
                            tag="a"
                            :href="$photosDownloadUrl"
                            color="gray"
                            icon="heroicon-o-arrow-down-tray"
                            class="w-full justify-center"
                        >
                            {{ __('app.guest_messages_download_photos') }}
                        </x-filament::button>
                    @endif
                    <x-filament::button
                        tag="a"
                        :href="$photosUrl"
                        color="gray"
                        icon="heroicon-o-photo"
                        class="w-full justify-center"
                    >
                        {{ __('app.guest_messages_view_all_photos') }}
                    </x-filament::button>
                </div>

                @if ($photoPreviews === [])
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.memories_photos_empty') }}</p>
                @else
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3">
                        @foreach ($photoPreviews as $photo)
                            <a
                                href="{{ $photosUrl }}"
                                class="group relative aspect-square overflow-hidden rounded-xl bg-gray-100 dark:bg-white/5"
                            >
                                <img
                                    src="{{ $photo['url'] }}"
                                    alt="{{ $photo['sender_name'] }}"
                                    class="h-full w-full object-cover transition group-hover:scale-105"
                                >
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-filament::section>
@endif
