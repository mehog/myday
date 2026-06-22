<section
    class="invitation-section py-20 px-6 pb-28"
    id="rsvp"
    x-data="{ pending: null }"
    @keydown.escape.window="pending = null"
>
    <div class="max-w-xl mx-auto text-center invitation-fade-in">
        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">{{ __('invitation.rsvp') }}</p>
        <h2 class="invitation-heading text-4xl text-[var(--color-text)] mb-4">{{ __('invitation.confirm_attendance') }}</h2>

        @if ($event->rsvp_deadline)
            <p class="invitation-body text-[var(--color-text-muted)] mb-8">
                {{ __('invitation.respond_by', ['date' => $event->rsvp_deadline->translatedFormat('j. F Y.')]) }}
            </p>
        @endif

        @if ($guest && $guest->hasResponded())
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
            </div>
        @elseif ($rsvpSubmitted && $guest)
            <div class="rounded-2xl border border-[var(--color-primary)]/30 bg-[var(--color-bg-soft)]/80 px-6 py-8">
                <p class="invitation-heading text-2xl text-[var(--color-text)] mb-2">
                    {{ __('invitation.thank_you', ['name' => $guest->name]) }}
                </p>
                <p class="invitation-body text-[var(--color-text-muted)]">
                    {{ __('invitation.response_received') }}
                </p>
            </div>
        @else
            @if ($guest)
                <p class="invitation-body text-[var(--color-text-muted)] mb-8">
                    Zdravo, <span class="text-[var(--color-accent)]">{{ $guest->name }}</span>. {{ __('invitation.greeting_question') }}
                </p>
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
        @endif
    </div>

    @if (! ($guest && $guest->hasResponded()) && ! ($rsvpSubmitted && $guest))
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
</section>
