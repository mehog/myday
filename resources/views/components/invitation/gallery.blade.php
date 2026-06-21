<section
    class="invitation-section py-20 px-6 bg-[var(--color-bg-soft)]/50"
    x-data="{ lightbox: null }"
>
    <div class="max-w-5xl mx-auto invitation-fade-in">
        <div class="text-center mb-10">
            <h2 class="invitation-heading text-4xl text-[var(--color-text)] mb-3">{{ __('invitation.gallery') }}</h2>
            <p class="invitation-body text-[var(--color-text-muted)] max-w-lg mx-auto">{{ __('invitation.gallery_description') }}</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
            @foreach ($event->eventPhotos as $photo)
                <button
                    type="button"
                    class="group relative aspect-square overflow-hidden rounded-xl border border-white/10 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]"
                    @click="lightbox = '{{ $photo->url }}'"
                >
                    <img
                        src="{{ $photo->url }}"
                        alt="{{ __('invitation.photo_alt') }}"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                        loading="lazy"
                    >
                </button>
            @endforeach
        </div>
    </div>

    <div
        x-show="lightbox"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
        @click="lightbox = null"
        @keydown.escape.window="lightbox = null"
        style="display: none;"
    >
        <img :src="lightbox" alt="{{ __('invitation.gallery_preview') }}" class="max-h-[90vh] max-w-full rounded-lg shadow-2xl">
    </div>
</section>
