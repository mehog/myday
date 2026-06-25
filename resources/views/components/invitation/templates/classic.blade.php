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
    'isPersonalLink' => $isPersonalLink,
])
