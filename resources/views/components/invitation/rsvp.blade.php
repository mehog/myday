<section class="invitation-section py-20 px-6 pb-28" id="rsvp">
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
                    wire:click="respond('yes')"
                    wire:loading.attr="disabled"
                    class="rsvp-btn rsvp-btn-yes flex-1 rounded-xl px-8 py-4 invitation-heading text-xl transition"
                >
                    <span wire:loading.remove wire:target="respond">{{ __('invitation.yes_attending') }}</span>
                    <span wire:loading wire:target="respond">{{ __('invitation.saving') }}</span>
                </button>
                <button
                    type="button"
                    wire:click="respond('no')"
                    wire:loading.attr="disabled"
                    class="rsvp-btn rsvp-btn-no flex-1 rounded-xl px-8 py-4 invitation-heading text-xl transition"
                >
                    {{ __('invitation.no_attending') }}
                </button>
            </div>
        @endif
    </div>
</section>
