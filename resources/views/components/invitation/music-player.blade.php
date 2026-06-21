<section class="invitation-section py-16 px-6">
    <div class="max-w-lg mx-auto text-center invitation-fade-in">
        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-6">
            {{ __('invitation.our_song') }}
        </p>

        <div
            class="relative w-full overflow-hidden rounded-xl border border-white/10"
            style="padding-top: 56.25%;"
        >
            <iframe
                src="{{ $event->youtube_embed_url }}"
                class="absolute inset-0 w-full h-full"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                loading="lazy"
                title="{{ __('invitation.our_song') }}"
            ></iframe>
        </div>
    </div>
</section>
