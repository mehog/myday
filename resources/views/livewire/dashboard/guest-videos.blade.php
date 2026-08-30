<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="hidden text-xl font-semibold lg:block">{{ __('app.guest_messages_all_videos_title') }}</h2>
        <x-dashboard.button variant="outline" href="{{ route('dashboard.messages') }}">
            {{ __('app.guest_messages_back') }}
        </x-dashboard.button>
    </div>

    @if ($totalVideoCount === 0)
        <x-dashboard.card>
            <p class="text-sm text-muted-foreground">{{ __('app.guest_messages_videos_empty') }}</p>
        </x-dashboard.card>
    @else
        <p class="text-sm text-muted-foreground">
            {{ __('app.guest_messages_col_video_count', ['count' => $totalVideoCount]) }}
        </p>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($videos as $video)
                <x-dashboard.card wire:key="video-{{ $video['key'] }}">
                    <p class="mb-2 text-sm font-medium">{{ $video['sender_name'] }}</p>
                    <video controls class="mb-2 w-full rounded-md border border-border" src="{{ $video['url'] }}"></video>
                    <a
                        href="{{ $video['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-sm text-primary underline"
                    >
                        {{ __('app.guest_messages_watch') }} ↗
                    </a>
                </x-dashboard.card>
            @endforeach
        </div>

        @if ($hasMore)
            <div class="flex justify-center">
                <x-dashboard.button
                    type="button"
                    variant="secondary"
                    wire:click="loadMore"
                    wire:loading.attr="disabled"
                    wire:target="loadMore"
                >
                    <span wire:loading.remove wire:target="loadMore">{{ __('app.guest_messages_all_videos_load_more') }}</span>
                    <span wire:loading wire:target="loadMore">{{ __('app.guest_messages_all_videos_loading') }}</span>
                </x-dashboard.button>
            </div>
        @else
            <p class="text-center text-sm text-muted-foreground">{{ __('app.guest_messages_all_videos_end') }}</p>
        @endif
    @endif
</div>
