<section
    class="invitation-section py-20 px-6"
    @unless ($event->isWeddingDay())
        x-data="countdown('{{ $event->wedding_date->toIso8601String() }}', @js([
            'days' => __('invitation.days'),
            'hours' => __('invitation.hours'),
            'minutes' => __('invitation.minutes'),
            'seconds' => __('invitation.seconds'),
        ]))"
        x-init="start()"
    @endunless
>
    <div class="max-w-3xl mx-auto text-center invitation-fade-in">
        @if ($event->isWeddingDay())
            <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">
                {{ __('invitation.today_is_the_day_eyebrow') }}
            </p>
            <h2 class="invitation-heading text-4xl sm:text-5xl text-[var(--color-text)] mb-6">
                {{ __('invitation.today_is_the_day_title') }}
            </h2>
            <p class="invitation-body text-lg text-[var(--color-text-muted)] max-w-xl mx-auto">
                {{ __('invitation.today_is_the_day_subtitle') }}
            </p>
        @else
            <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">
                {{ __('invitation.counting_down') }}
            </p>
            <h2 class="invitation-heading text-4xl sm:text-5xl text-[var(--color-text)] mb-10">
                {{ __('invitation.until_i_do') }} <span class="text-[var(--color-primary)]">{{ __('invitation.i_do') }}</span>
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
                <template x-for="(item, index) in units" :key="index">
                    <div class="countdown-unit rounded-2xl border border-white/10 bg-[var(--color-bg-soft)]/80 backdrop-blur px-4 py-6">
                        <div class="invitation-heading text-4xl sm:text-5xl font-semibold text-[var(--color-primary)]" x-text="item.value"></div>
                        <div class="mt-2 text-xs uppercase tracking-widest text-[var(--color-text-muted)]" x-text="item.label"></div>
                    </div>
                </template>
            </div>
        @endif

        @if ($event->motto)
            <p class="invitation-body text-lg text-[var(--color-text-muted)] italic mt-10 max-w-xl mx-auto">
                {{ $event->motto }}
            </p>
        @endif
    </div>
</section>
