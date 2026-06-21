<section
    class="invitation-section py-16 px-6"
    x-data="{ playing: false }"
>
    <div class="max-w-md mx-auto text-center invitation-fade-in">
        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">{{ __('invitation.our_song') }}</p>
        <h2 class="invitation-heading text-3xl text-[var(--color-text)] mb-6">{{ __('invitation.music') }}</h2>

        <audio
            x-ref="player"
            src="{{ $event->music_url }}"
            preload="metadata"
            @play="playing = true"
            @pause="playing = false"
            @ended="playing = false"
        ></audio>

        <button
            type="button"
            class="inline-flex items-center gap-3 rounded-full border border-[var(--color-primary)] px-8 py-3 text-[var(--color-text)] transition hover:bg-[var(--color-primary)] hover:text-[var(--color-bg)]"
            @click="playing ? $refs.player.pause() : $refs.player.play()"
        >
            <span x-text="playing ? @js(__('invitation.pause')) : @js(__('invitation.play_song'))"></span>
        </button>
    </div>
</section>
