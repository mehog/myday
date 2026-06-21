<div>
    <x-theme :theme="$event->theme">
        <div class="invitation-page">
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

            @if ($event->music_url)
                @include('components.invitation.music-player', ['event' => $event])
            @endif

            @include('components.invitation.rsvp', [
                'event' => $event,
                'guest' => $guest,
            ])
        </div>
    </x-theme>
</div>
