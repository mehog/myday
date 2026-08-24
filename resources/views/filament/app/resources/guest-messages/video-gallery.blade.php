@php
    /** @var \App\Models\GuestMessage $record */

    $videos = collect($record->file_paths ?? [])
        ->values()
        ->map(fn (string $path, int $index): array => [
            'index' => $index,
            'url' => \App\Support\MediaDisk::url($path),
            'name' => basename($path),
        ])
        ->filter(fn (array $video): bool => filled($video['url']))
        ->values()
        ->all();
@endphp

@if ($videos === [])
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ __('app.guest_messages_videos_empty') }}
    </p>
@else
    <div class="grid gap-4">
        @foreach ($videos as $video)
            <div class="space-y-2">
                <video controls class="w-full max-w-2xl rounded-xl" src="{{ $video['url'] }}"></video>
                <a
                    href="{{ $video['url'] }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400"
                >
                    {{ __('app.guest_messages_watch') }} ↗
                </a>
            </div>
        @endforeach
    </div>
@endif
