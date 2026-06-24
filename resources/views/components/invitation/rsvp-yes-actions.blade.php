<div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
    <button
        type="button"
        @click="showCalendarModal = true"
        class="rsvp-btn rsvp-btn-yes rounded-xl px-6 py-3 invitation-heading text-base transition"
    >
        {{ __('invitation.add_to_calendar') }}
    </button>
    @if ($guest)
        <a
            href="{{ route('invitation.contact.guest', [$event->slug, $guest->token]) }}"
            class="rsvp-btn rsvp-btn-no rounded-xl px-6 py-3 invitation-heading text-base transition"
        >
            {{ __('invitation.send_message_to_newlyweds') }}
        </a>
    @endif
</div>
