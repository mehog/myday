<div>
    <x-theme :theme="$event->theme">
        <div class="invitation-page min-h-screen">
            <section class="invitation-section py-16 px-6">
                <div class="max-w-2xl mx-auto invitation-fade-in">
                    <div class="text-center mb-10">
                        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">
                            {{ $event->couple_names }}
                        </p>
                        <h1 class="invitation-heading text-4xl text-[var(--color-text)] mb-4">
                            {{ __('invitation.push_notifications_page_title') }}
                        </h1>
                        <p class="invitation-body text-[var(--color-text-muted)]">
                            {{ __('invitation.push_notifications_page_description') }}
                        </p>
                    </div>

                    <div class="mb-8 text-center">
                        <a
                            href="{{ route('invitation.guest', [$event->slug, $guest->token]) }}#rsvp"
                            class="text-sm text-[var(--color-primary)] hover:underline transition"
                        >
                            &larr; {{ __('invitation.back_to_invitation') }}
                        </a>
                    </div>

                    @if ($notifications->isEmpty())
                        <div class="rounded-2xl border border-[var(--color-primary)]/20 bg-[var(--color-bg-soft)]/80 p-8 text-center">
                            <p class="invitation-body text-[var(--color-text-muted)]">
                                {{ __('invitation.push_notifications_empty') }}
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($notifications as $notification)
                                <article class="rounded-2xl border border-[var(--color-primary)]/20 bg-[var(--color-bg-soft)]/80 p-6">
                                    <h2 class="invitation-heading text-xl text-[var(--color-text)] mb-2">
                                        {{ $notification->title }}
                                    </h2>
                                    <p class="invitation-body text-[var(--color-text-muted)] whitespace-pre-wrap">
                                        {{ $notification->body }}
                                    </p>
                                    @if ($notification->sent_at)
                                        <p class="mt-4 text-xs text-[var(--color-text-muted)]">
                                            {{ __('invitation.push_notification_sent_at') }}
                                            {{ $notification->sent_at->translatedFormat('j M Y, H:i') }}
                                        </p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </x-theme>
</div>
