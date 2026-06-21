<section class="invitation-section py-20 px-6 bg-[var(--color-bg-soft)]/50">
    <div class="max-w-3xl mx-auto invitation-fade-in">
        <div class="text-center mb-12">
            <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">{{ __('invitation.plan_of_day') }}</p>
            <h2 class="invitation-heading text-4xl text-[var(--color-text)]">{{ __('invitation.schedule') }}</h2>
        </div>

        <div class="space-y-0">
            @foreach ($event->scheduleItems as $item)
                <div class="schedule-item flex gap-6 py-6 border-b border-white/10 last:border-0">
                    <div class="shrink-0 w-20 text-right">
                        <span class="invitation-heading text-xl text-[var(--color-primary)]">
                            {{ \Illuminate\Support\Carbon::parse($item->time)->format('H:i') }}
                        </span>
                    </div>
                    <div class="flex-1">
                        <h3 class="invitation-heading text-2xl text-[var(--color-text)] mb-1">{{ $item->title }}</h3>
                        @if ($item->description)
                            <p class="invitation-body text-[var(--color-text-muted)]">{{ $item->description }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
