<div>
    @if ($isPreview)
        <div class="fixed top-0 inset-x-0 z-50 bg-[#c9a227] text-[#1a1208] px-4 py-3 text-sm flex items-center justify-center gap-2 shadow-md">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-center">{{ __('invitation.preview_banner') }}</p>
        </div>
    @endif

    <x-theme :theme="$event->theme">
        <div @class(['invitation-page', 'pt-12' => $isPreview])>
            @include('components.invitation.hero', ['event' => $event])

            @include('components.invitation.countdown', ['event' => $event])

            @if ($event->scheduleItems->isNotEmpty())
                @include('components.invitation.schedule', ['event' => $event])
            @endif

            @if ($event->location_name || $event->location_address)
                @include('components.invitation.location', ['event' => $event])
            @endif

            @if ($event->eventPhotos->isNotEmpty())
                @include('components.invitation.gallery', ['event' => $event])
            @endif

            @if ($event->youtube_embed_url)
                @include('components.invitation.music-player', ['event' => $event])
            @endif

            @include('components.invitation.rsvp', [
                'event' => $event,
                'guest' => $guest,
            ])
        </div>
    </x-theme>
</div>
