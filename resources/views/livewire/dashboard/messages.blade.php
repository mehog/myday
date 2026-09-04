@php
    use App\GuestMessageType;
    use App\Services\GuestMessageMediaGallery;

    $gallery = app(GuestMessageMediaGallery::class);
@endphp

<div
    @class([
        'space-y-5 lg:space-y-6',
        'flex min-h-full flex-col' => $wedding && $messages->isEmpty(),
    ])
    x-data="{ messagesMenuOpen: false }"
>
    <div class="hidden items-center justify-between gap-3 lg:flex">
        <h2 class="text-xl font-semibold">{{ __('dashboard.messages_title') }}</h2>
        @if ($wedding)
            <div class="flex flex-wrap gap-2">
                @if ($hasPhotos)
                    <x-dashboard.button variant="secondary" href="{{ route('dashboard.messages.photos') }}">
                        {{ __('app.guest_messages_view_all_photos') }}
                    </x-dashboard.button>
                @endif
                @if ($hasVideos)
                    <x-dashboard.button variant="secondary" href="{{ route('dashboard.messages.videos') }}">
                        {{ __('app.guest_messages_view_all_videos') }}
                    </x-dashboard.button>
                @endif
                @if ($hasPhotos)
                    <x-dashboard.button variant="outline" href="{{ route('guest-messages.photos.download') }}" target="_blank">
                        {{ __('dashboard.messages_download_zip') }}
                    </x-dashboard.button>
                @endif
            </div>
        @endif
    </div>

    @if ($wedding && ($hasPhotos || $hasVideos))
        <div class="relative flex justify-end lg:hidden">
            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-border bg-card"
                @click="messagesMenuOpen = !messagesMenuOpen"
                aria-label="{{ __('dashboard.actions') }}"
            >
                <x-dashboard.icon name="ellipsis" class="h-5 w-5" />
            </button>
            <div
                x-show="messagesMenuOpen"
                @click.outside="messagesMenuOpen = false"
                x-cloak
                class="absolute right-0 top-11 z-20 w-56 overflow-hidden rounded-xl border border-border bg-popover shadow-lg"
            >
                @if ($hasPhotos)
                    <a href="{{ route('dashboard.messages.photos') }}" class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-accent" @click="messagesMenuOpen = false">
                        <x-dashboard.icon name="photo" class="h-4 w-4 shrink-0" />
                        {{ __('app.guest_messages_view_all_photos') }}
                    </a>
                @endif
                @if ($hasVideos)
                    <a href="{{ route('dashboard.messages.videos') }}" class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-accent" @click="messagesMenuOpen = false">
                        <x-dashboard.icon name="photo" class="h-4 w-4 shrink-0" />
                        {{ __('app.guest_messages_view_all_videos') }}
                    </a>
                @endif
                @if ($hasPhotos)
                    <a href="{{ route('guest-messages.photos.download') }}" target="_blank" class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-accent" @click="messagesMenuOpen = false">
                        <x-dashboard.icon name="external" class="h-4 w-4 shrink-0" />
                        {{ __('dashboard.messages_download_zip') }}
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if (! $wedding)
        <x-dashboard.card>
            <p class="text-sm text-muted-foreground">{{ __('dashboard.no_wedding') }}</p>
        </x-dashboard.card>
    @elseif ($messages->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center px-6 py-12 text-center min-h-[calc(100dvh-5rem-env(safe-area-inset-bottom,0px))] lg:min-h-[calc(100dvh-9rem)]">
            <span class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-muted">
                <x-dashboard.icon name="message" class="h-7 w-7 text-muted-foreground" />
            </span>
            <p class="text-lg font-semibold">{{ __('app.guest_messages_empty_heading') }}</p>
            <p class="mt-2 max-w-sm text-sm text-muted-foreground">{{ __('app.guest_messages_empty_desc') }}</p>
        </div>
    @else
        {{-- Mobile grouped list --}}
        <x-dashboard.list-group class="lg:hidden">
            @foreach ($messages as $message)
                <article class="dashboard-list-row" wire:key="message-m-{{ $message->id }}">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-muted text-sm font-semibold">
                        {{ strtoupper(mb_substr($message->sender_name ?: '?', 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="truncate text-[15px] font-semibold">{{ $message->sender_name }}</p>
                            <p class="shrink-0 text-[11px] text-muted-foreground">{{ $message->created_at?->diffForHumans() }}</p>
                        </div>
                        <p class="mt-0.5 text-xs text-muted-foreground">{{ $message->type?->label() }}</p>
                        @if ($message->type === GuestMessageType::Text)
                            <p class="mt-1 line-clamp-3 whitespace-pre-wrap text-sm leading-relaxed text-foreground/90">{{ $message->content }}</p>
                        @elseif ($message->type === GuestMessageType::Photo)
                            <div class="mt-2">
                                <x-dashboard.guest-message-photo-gallery
                                    :photos="$gallery->photosForLightbox($message)"
                                    :download-url="route('guest-messages.photos.download', ['message' => $message->id])"
                                />
                            </div>
                        @elseif ($message->type === GuestMessageType::Audio)
                            @if ($message->fileUrl())
                                <audio controls class="mt-2 w-full" src="{{ $message->fileUrl() }}"></audio>
                            @endif
                        @elseif ($message->type === GuestMessageType::Video)
                            <div class="mt-2 space-y-2">
                                @foreach ($message->fileUrls() as $url)
                                    <video controls class="w-full rounded-lg border border-border" src="{{ $url }}"></video>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </x-dashboard.list-group>

        {{-- Desktop cards --}}
        <div class="hidden space-y-3 lg:block">
            @foreach ($messages as $message)
                <article class="dashboard-message-row" wire:key="message-{{ $message->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium">{{ $message->sender_name }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ $message->type?->label() }}
                                · {{ $message->created_at?->diffForHumans() }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-muted px-2.5 py-1 text-[11px] font-medium">{{ $message->type?->label() }}</span>
                    </div>

                    <div class="mt-3">
                        @if ($message->type === GuestMessageType::Text)
                            <p class="whitespace-pre-wrap text-sm leading-relaxed">{{ $message->content }}</p>
                        @elseif ($message->type === GuestMessageType::Photo)
                            <x-dashboard.guest-message-photo-gallery
                                :photos="$gallery->photosForLightbox($message)"
                                :download-url="route('guest-messages.photos.download', ['message' => $message->id])"
                            />
                            @if (! empty($message->file_paths))
                                <div class="mt-3">
                                    <a href="{{ route('guest-messages.photos.download', ['message' => $message->id]) }}" class="text-sm text-primary underline" target="_blank" rel="noopener noreferrer">
                                        {{ __('dashboard.messages_download_zip') }}
                                    </a>
                                </div>
                            @endif
                        @elseif ($message->type === GuestMessageType::Audio)
                            @if ($message->fileUrl())
                                <audio controls class="w-full max-w-md" src="{{ $message->fileUrl() }}"></audio>
                            @endif
                        @elseif ($message->type === GuestMessageType::Video)
                            <div class="space-y-3">
                                @foreach ($message->fileUrls() as $url)
                                    <div class="space-y-2">
                                        <video controls class="w-full max-w-xs rounded-md border border-border" src="{{ $url }}"></video>
                                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-primary underline">
                                            {{ __('app.guest_messages_watch') }} ↗
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
