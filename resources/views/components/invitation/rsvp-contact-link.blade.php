@if ($guest && filled($guest->token))
<div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
    <a
        href="{{ route('invitation.contact.guest', [$event->slug, $guest->token]) }}"
        class="rsvp-btn rsvp-btn-yes rounded-xl px-6 py-3 invitation-heading text-base transition"
    >
        {{ $event->acceptsGuestPhotos()
            ? __('invitation.share_photos_and_messages')
            : __('invitation.send_message_to_newlyweds') }}
    </a>
</div>
@endif
