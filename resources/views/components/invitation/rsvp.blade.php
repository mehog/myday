<section
    class="invitation-section py-20 px-6 pb-28"
    id="rsvp"
    x-data="{
        pending: null,
        showCalendarModal: false,
        showPushPrompt: false,
        subscribing: false,
        subscribed: false,
        pushError: null,
        subscribeUrl: @js(! empty($isPersonalLink) && $guest ? route('push.subscribe', $guest->token) : null),
        pushErrorMessages: @js([
            'push_ios_update' => __('app.push_ios_update'),
            'push_error_not_supported' => __('app.push_error_not_supported'),
            'push_error_denied' => __('app.push_error_denied'),
            'push_error_config' => __('app.push_error_config'),
            'push_error_server' => __('app.push_error_server'),
            'push_error_unknown' => __('app.push_error_unknown'),
        ]),
    }"
    x-init="
        $watch('pending', v => v ? $dispatch('story-modal-open') : $dispatch('story-modal-close'));
        $watch('showCalendarModal', v => v ? $dispatch('story-modal-open') : $dispatch('story-modal-close'));
        $watch('showPushPrompt', v => v ? $dispatch('story-modal-open') : $dispatch('story-modal-close'));
    "
    @keydown.escape.window="pending = null; showPushPrompt = false; showCalendarModal = false"
    @rsvp-accepted.window="if (subscribeUrl) { showPushPrompt = true; pushError = null; }"
>
    <div class="max-w-xl mx-auto text-center invitation-fade-in">
        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">{{ __('invitation.rsvp') }}</p>
        <h2 class="invitation-heading text-4xl text-[var(--color-text)] mb-4">{{ __('invitation.confirm_attendance') }}</h2>

        @if ($event->rsvp_deadline)
            <p class="invitation-body text-[var(--color-text-muted)] mb-8">
                {{ __('invitation.respond_by', ['date' => $event->rsvp_deadline->translatedFormat('j. F Y.')]) }}
            </p>
        @endif

        @if ($guest && $guest->hasResponded() && ! $isEditing)
            <div class="rounded-2xl border border-[var(--color-primary)]/30 bg-[var(--color-bg-soft)]/80 px-6 py-8">
                <p class="invitation-heading text-2xl text-[var(--color-text)] mb-2">
                    {{ __('invitation.thank_you', ['name' => $guest->name]) }}
                </p>
                <p class="invitation-body text-[var(--color-text-muted)]">
                    {{ __('invitation.your_response') }}:
                    <span class="text-[var(--color-primary)] font-medium">
                        {{ $guest->rsvp_status->label() }}
                    </span>
                </p>
                @if (! $isEditing && (! $event->rsvp_deadline || now()->lte($event->rsvp_deadline)))
                    <button
                        type="button"
                        wire:click="editRsvp"
                        class="mt-4 text-sm text-[var(--color-primary)] hover:underline transition"
                    >
                        {{ __('invitation.edit_response') }}
                    </button>
                @endif
                @if ($guest->rsvp_status === \App\RsvpStatus::Yes)
                    @include('components.invitation.rsvp-yes-actions', [
                        'event' => $event,
                        'guest' => $guest,
                        'isPersonalLink' => $isPersonalLink ?? false,
                    ])
                @endif
            </div>
        @elseif ($rsvpSubmitted && $guest && ! $isEditing)
            <div class="rounded-2xl border border-[var(--color-primary)]/30 bg-[var(--color-bg-soft)]/80 px-6 py-8">
                <p class="invitation-heading text-2xl text-[var(--color-text)] mb-2">
                    {{ __('invitation.thank_you', ['name' => $guest->name]) }}
                </p>
                <p class="invitation-body text-[var(--color-text-muted)]">
                    {{ __('invitation.response_received') }}
                </p>
                @if (! $isEditing && (! $event->rsvp_deadline || now()->lte($event->rsvp_deadline)))
                    <button
                        type="button"
                        wire:click="editRsvp"
                        class="mt-4 text-sm text-[var(--color-primary)] hover:underline transition"
                    >
                        {{ __('invitation.edit_response') }}
                    </button>
                @endif
                @if ($guest->rsvp_status === \App\RsvpStatus::Yes)
                    @include('components.invitation.rsvp-yes-actions', [
                        'event' => $event,
                        'guest' => $guest,
                        'isPersonalLink' => $isPersonalLink ?? false,
                    ])
                @endif
            </div>
        @else
            @if ($guest)
                <p class="invitation-body text-[var(--color-text-muted)] mb-8">
                    {{ __('invitation.greeting_hello') }}, <span class="text-[var(--color-accent)]">{{ $guest->name }}</span>. {{ __('invitation.greeting_question') }}
                </p>

                @if ($guest->plus_one_allowed)
                    <p class="invitation-body text-sm text-[var(--color-text-muted)] -mt-5 mb-8">
                        {{ __('invitation.plus_one_notice') }}
                    </p>
                @endif
            @else
                <div class="mb-8 text-left">
                    <label for="anonymousName" class="block text-sm text-[var(--color-text-muted)] mb-2">{{ __('invitation.your_name') }}</label>
                    <input
                        id="anonymousName"
                        type="text"
                        wire:model="anonymousName"
                        class="w-full rounded-xl border border-white/10 bg-[var(--color-bg-soft)] px-4 py-3 text-[var(--color-text)] placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-primary)] focus:outline-none"
                        placeholder="{{ __('invitation.name_placeholder') }}"
                    >
                    @error('anonymousName')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button
                    type="button"
                    @click="pending = 'yes'"
                    wire:loading.attr="disabled"
                    class="rsvp-btn rsvp-btn-yes flex-1 rounded-xl px-8 py-4 invitation-heading text-xl transition"
                >
                    <span wire:loading.remove wire:target="respond">{{ __('invitation.yes_attending') }}</span>
                    <span wire:loading wire:target="respond">{{ __('invitation.saving') }}</span>
                </button>
                <button
                    type="button"
                    @click="pending = 'no'"
                    wire:loading.attr="disabled"
                    class="rsvp-btn rsvp-btn-no flex-1 rounded-xl px-8 py-4 invitation-heading text-xl transition"
                >
                    {{ __('invitation.no_attending') }}
                </button>
            </div>

            <p class="invitation-body text-sm text-[var(--color-text-muted)] mt-6">
                {{ __('invitation.rsvp_update_helper_text') }}
                @if ($guest && ($isPersonalLink ?? false))
                    &nbsp;·&nbsp;
                    <a
                        href="{{ route('invitation.contact.guest', [$event->slug, $guest->token]) }}"
                        class="text-[var(--color-primary)] hover:underline transition"
                    >
                        {{ __('invitation.rsvp_leave_message_hint') }}
                    </a>
                @endif
            </p>

            @include('components.invitation.push-enable', [
                'event' => $event,
                'guest' => $guest ?? null,
                'isPersonalLink' => $isPersonalLink ?? false,
            ])
        @endif
    </div>

    @if (! ($guest && $guest->hasResponded() && ! $isEditing) && ! ($rsvpSubmitted && $guest && ! $isEditing))
        <div
            x-show="pending !== null"
            x-transition.opacity
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4"
            @click.self="pending = null"
            style="display: none;"
        >
            <div
                x-show="pending !== null"
                x-transition
                class="w-full max-w-md rounded-2xl border border-[var(--color-primary)]/20 bg-[var(--color-bg-soft)] p-8 text-center"
                @click.stop
            >
                <h3 class="invitation-heading text-2xl text-[var(--color-text)] mb-2">
                    {{ __('invitation.confirm_rsvp_title') }}
                </h3>
                <p
                    class="invitation-body text-[var(--color-text-muted)] mb-8"
                    x-text="pending === 'yes'
                        ? '{{ __('invitation.confirm_rsvp_yes') }}'
                        : '{{ __('invitation.confirm_rsvp_no') }}'"
                ></p>

                @if ($guest?->plus_one_allowed)
                    <div
                        x-show="pending === 'yes'"
                        x-cloak
                        class="mb-6 text-left"
                    >
                        <label for="plusOneName" class="block text-sm text-[var(--color-text-muted)] mb-2">
                            {{ __('invitation.plus_one_question') }}
                        </label>
                        <input
                            id="plusOneName"
                            type="text"
                            wire:model="plusOneName"
                            class="w-full rounded-xl border border-white/10 bg-[var(--color-bg)] px-4 py-3 text-[var(--color-text)] placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-primary)] focus:outline-none"
                            placeholder="{{ __('invitation.plus_one_name_placeholder') }}"
                        >
                        {{-- helper text --}}
                        <p class="text-sm text-[var(--color-text-muted)] mt-2">
                            {{ __('invitation.plus_one_helper_text') }}
                        </p>
                    </div>
                @endif

                <div class="mb-6 text-left">
                    <label for="rsvpNote" class="block text-sm text-[var(--color-text-muted)] mb-2">
                        {{ __('invitation.rsvp_note_label') }}
                    </label>
                    <textarea
                        id="rsvpNote"
                        wire:model="rsvpNote"
                        rows="3"
                        maxlength="500"
                        class="w-full rounded-xl border border-white/10 bg-[var(--color-bg)] px-4 py-3 text-[var(--color-text)] placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-primary)] focus:outline-none resize-none"
                        placeholder="{{ __('invitation.rsvp_note_placeholder') }}"
                    ></textarea>
                </div>

                <div class="flex flex-col gap-3">
                    <button
                        type="button"
                        @click="$wire.respond(pending); pending = null"
                        class="rsvp-btn w-full py-4 rounded-xl invitation-heading text-lg transition"
                        :class="pending === 'yes' ? 'rsvp-btn-yes' : 'rsvp-btn-no'"
                    >
                        {{ __('invitation.rsvp_confirm_btn') }}
                    </button>
                    <button
                        type="button"
                        class="text-sm text-[var(--color-text-muted)] hover:text-[var(--color-primary)] transition mt-2"
                        @click="pending = null"
                    >
                        {{ __('invitation.rsvp_cancel_btn') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div
        x-show="showCalendarModal"
        x-transition.opacity
        class="fixed inset-0 z-[105] flex items-center justify-center bg-black/80 p-4"
        @click.self="showCalendarModal = false"
        style="display: none;"
    >
        <div
            x-show="showCalendarModal"
            x-transition
            class="w-full max-w-md rounded-2xl border border-[var(--color-primary)]/20 bg-[var(--color-bg-soft)] p-8 text-center"
            @click.stop
        >
            <h3 class="invitation-heading text-2xl text-[var(--color-text)] mb-2">
                {{ __('invitation.add_to_calendar') }}
            </h3>
            <p class="invitation-body text-[var(--color-text-muted)] mb-8">
                {{ __('invitation.add_to_calendar_description') }}
            </p>
            <div class="flex flex-col gap-3">
                <a
                    href="{{ $event->googleCalendarUrl() }}"
                    target="_blank"
                    rel="noopener"
                    class="rsvp-btn rsvp-btn-yes w-full py-4 rounded-xl invitation-heading text-lg transition"
                >
                    {{ __('invitation.add_to_google_calendar') }}
                </a>
                <a
                    href="{{ route('invitation.ics', $event->slug) }}"
                    class="rsvp-btn rsvp-btn-no w-full py-4 rounded-xl invitation-heading text-lg transition"
                >
                    {{ __('invitation.download_ics') }}
                </a>
                <button
                    type="button"
                    class="text-sm text-[var(--color-text-muted)] hover:text-[var(--color-primary)] transition mt-2"
                    @click="showCalendarModal = false"
                >
                    {{ __('invitation.rsvp_cancel_btn') }}
                </button>
            </div>
        </div>
    </div>

    @if (! empty($isPersonalLink) && $guest)
        <div
            x-show="showPushPrompt && ! subscribed"
            x-transition.opacity
            class="fixed inset-0 z-[110] flex items-center justify-center bg-black/80 p-4"
            @click.self="showPushPrompt = false"
            style="display: none;"
        >
            <div
                x-show="showPushPrompt && ! subscribed"
                x-transition
                class="w-full max-w-md rounded-2xl border border-[var(--color-primary)]/20 bg-[var(--color-bg-soft)] p-8 text-center"
                @click.stop
            >
                <h3 class="invitation-heading text-2xl text-[var(--color-text)] mb-2">
                    {{ __('app.push_notifications_title') }}
                </h3>
                <p class="invitation-body text-[var(--color-text-muted)] mb-8">
                    {{ __('app.push_notifications_prompt_body', ['couple' => $event->couple_names]) }}
                </p>
                <div class="flex flex-col gap-3">
                    <button
                        type="button"
                        class="rsvp-btn rsvp-btn-yes w-full py-4 rounded-xl invitation-heading text-lg transition disabled:opacity-60"
                        :disabled="subscribing"
                        @click="
                            subscribing = true;
                            pushError = null;
                            subscribeToPush(subscribeUrl).then((result) => {
                                subscribing = false;
                                if (result.ok) {
                                    subscribed = true;
                                    showPushPrompt = false;
                                } else if (result.error) {
                                    pushError = result.error;
                                }
                            });
                        "
                    >
                        <span x-show="! subscribing">{{ __('app.push_notifications_allow') }}</span>
                        <span x-show="subscribing">{{ __('invitation.saving') }}</span>
                    </button>
                    <button
                        type="button"
                        class="text-sm text-[var(--color-text-muted)] hover:text-[var(--color-primary)] transition"
                        @click="showPushPrompt = false"
                    >
                        {{ __('app.push_notifications_maybe_later') }}
                    </button>
                    @include('components.invitation.push-error-feedback')
                </div>
            </div>
        </div>
    @endif
</section>
