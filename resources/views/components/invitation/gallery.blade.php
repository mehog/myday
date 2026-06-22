<section
    class="invitation-section py-20 px-6 bg-[var(--color-bg-soft)]/50"
    x-data="{ lightbox: null, lightboxTitle: null }"
    @keydown.escape.window="lightbox = null; lightboxTitle = null"
>
    <div class="max-w-5xl mx-auto invitation-fade-in">
        <div class="text-center mb-10">
            <h2 class="invitation-heading text-4xl text-[var(--color-text)] mb-3">{{ __('invitation.gallery') }}</h2>
            <p class="invitation-body text-[var(--color-text-muted)] max-w-lg mx-auto">{{ __('invitation.gallery_description') }}</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
            @foreach ($event->eventPhotos as $photo)
                <div class="flex flex-col gap-2">
                    <button
                        type="button"
                        class="group relative aspect-square overflow-hidden rounded-xl border border-white/10 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]"
                        @click="lightbox = @js($photo->url); lightboxTitle = @js($photo->title)"
                    >
                        <img
                            src="{{ $photo->url }}"
                            alt="{{ $photo->title ?? __('invitation.photo_alt') }}"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                            loading="lazy"
                        >
                    </button>
                    @if ($photo->title)
                        <p class="invitation-body text-sm text-center text-[var(--color-text-muted)] px-1">
                            {{ $photo->title }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div
        x-show="lightbox"
        x-transition.opacity
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4"
        @click="lightbox = null; lightboxTitle = null"
        style="display: none;"
    >
        <div class="max-w-full text-center" @click.stop>
            <img
                :src="lightbox"
                :alt="lightboxTitle ?? @js(__('invitation.gallery_preview'))"
                class="max-h-[85vh] max-w-full rounded-lg shadow-2xl mx-auto"
            >
            <p
                x-show="lightboxTitle"
                x-text="lightboxTitle"
                class="invitation-body text-sm text-[var(--color-text-muted)] mt-4 px-4"
            ></p>
        </div>
    </div>
</section>
