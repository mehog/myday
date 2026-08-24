@php
    use App\GuestMessageType;
    use App\Services\GuestMessageMediaGallery;

    $gallery = app(GuestMessageMediaGallery::class);
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
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

    @if (! $wedding)
        <x-dashboard.card>
            <p class="text-sm text-muted-foreground">{{ __('dashboard.no_wedding') }}</p>
        </x-dashboard.card>
    @elseif ($messages->isEmpty())
        <x-dashboard.card>
            <p class="text-sm text-muted-foreground">{{ __('dashboard.empty') }}</p>
        </x-dashboard.card>
    @else
        <div class="space-y-3">
            @foreach ($messages as $message)
                <x-dashboard.card wire:key="message-{{ $message->id }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-medium">{{ $message->sender_name }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ $message->type?->label() }}
                                · {{ $message->created_at?->diffForHumans() }}
                            </p>
                        </div>
                        <span class="rounded-md border border-border px-2 py-0.5 text-xs">{{ $message->type?->label() }}</span>
                    </div>

                    <div class="mt-4">
                        @if ($message->type === GuestMessageType::Text)
                            <p class="whitespace-pre-wrap text-sm">{{ $message->content }}</p>
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
                </x-dashboard.card>
            @endforeach
        </div>
    @endif
</div>
