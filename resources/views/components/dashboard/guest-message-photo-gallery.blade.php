@props([
    'photos' => [],
    'downloadUrl' => null,
    'showThumbnails' => true,
])

@if ($photos === [])
    <p class="text-sm text-muted-foreground">{{ __('app.guest_messages_photos_empty') }}</p>
@else
    <div
        {{ $attributes->class(['w-full']) }}
        x-data="{
            photos: @js($photos),
            selected: [],
            carouselIndex: null,
            downloadBaseUrl: @js($downloadUrl),
            touchStartX: null,
            get selectedCount() {
                return this.selected.length
            },
            get allSelected() {
                return this.photos.length > 0 && this.selected.length === this.photos.length
            },
            isSelected(index) {
                return this.selected.includes(index)
            },
            toggleSelect(index) {
                if (this.isSelected(index)) {
                    this.selected = this.selected.filter((value) => value !== index)
                    return
                }

                this.selected = [...this.selected, index].sort((a, b) => a - b)
            },
            selectAll() {
                this.selected = this.photos.map((photo) => photo.index)
            },
            clearSelection() {
                this.selected = []
            },
            openCarousel(index) {
                this.carouselIndex = this.photos.findIndex((photo) => photo.index === index)

                if (this.carouselIndex < 0) {
                    this.carouselIndex = null
                }
            },
            openCarouselAtPosition(position) {
                if (position >= 0 && position < this.photos.length) {
                    this.carouselIndex = position
                }
            },
            closeCarousel() {
                this.carouselIndex = null
            },
            next() {
                if (this.carouselIndex === null || this.photos.length === 0) {
                    return
                }

                this.carouselIndex = (this.carouselIndex + 1) % this.photos.length
            },
            prev() {
                if (this.carouselIndex === null || this.photos.length === 0) {
                    return
                }

                this.carouselIndex = (this.carouselIndex - 1 + this.photos.length) % this.photos.length
            },
            onTouchStart(event) {
                this.touchStartX = event.changedTouches[0].screenX
            },
            onTouchEnd(event) {
                if (this.touchStartX === null || this.carouselIndex === null) {
                    return
                }

                const delta = event.changedTouches[0].screenX - this.touchStartX

                if (Math.abs(delta) > 50) {
                    if (delta < 0) {
                        this.next()
                    } else {
                        this.prev()
                    }
                }

                this.touchStartX = null
            },
            downloadUrlForIndexes(indexes) {
                if (! this.downloadBaseUrl) {
                    return null
                }

                const params = new URLSearchParams()

                indexes.forEach((index) => {
                    params.append('indexes[]', String(index))
                })

                return `${this.downloadBaseUrl}?${params.toString()}`
            },
            downloadSelected() {
                if (this.selected.length === 0) {
                    return
                }

                const url = this.downloadUrlForIndexes(this.selected)

                if (url) {
                    window.location.href = url
                }
            },
            downloadCurrent() {
                if (this.carouselIndex === null) {
                    return
                }

                const url = this.downloadUrlForIndexes([this.photos[this.carouselIndex].index])

                if (url) {
                    window.location.href = url
                }
            },
        }"
        @keydown.escape.window="closeCarousel()"
        @keydown.arrow-left.window="if (carouselIndex !== null) prev()"
        @keydown.arrow-right.window="if (carouselIndex !== null) next()"
        x-effect="document.body.classList.toggle('overflow-hidden', carouselIndex !== null)"
    >
        @if ($showThumbnails)
            <div class="flex flex-wrap gap-2">
                <template x-for="photo in photos" :key="photo.index">
                    <button
                        type="button"
                        class="relative h-24 w-24 shrink-0 overflow-hidden rounded-md border border-border bg-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                        @click="openCarousel(photo.index)"
                        :aria-label="@js(__('app.guest_messages_photos_open')) + ' ' + (photo.index + 1)"
                    >
                        <img
                            :src="photo.url"
                            :alt="photo.name"
                            loading="lazy"
                            class="h-full w-full object-cover"
                        >
                    </button>
                </template>
            </div>
        @endif

        <div
            x-show="carouselIndex !== null"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[200] flex items-center justify-center bg-black/90 p-4"
            role="dialog"
            aria-modal="true"
            :aria-label="@js(__('app.guest_messages_photos_carousel'))"
            @click.self="closeCarousel()"
        >
            <button
                type="button"
                class="absolute right-4 top-4 rounded-lg bg-white/10 px-3 py-2 text-sm font-medium text-white hover:bg-white/20"
                @click="closeCarousel()"
            >
                {{ __('app.guest_messages_photos_close') }}
            </button>

            <button
                type="button"
                class="absolute left-3 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 sm:left-6"
                x-show="photos.length > 1"
                @click.stop="prev()"
                :aria-label="@js(__('app.guest_messages_photos_prev'))"
            >
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                </svg>
            </button>

            <button
                type="button"
                class="absolute right-3 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 sm:right-6"
                x-show="photos.length > 1"
                @click.stop="next()"
                :aria-label="@js(__('app.guest_messages_photos_next'))"
            >
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                </svg>
            </button>

            <div
                class="flex max-h-full w-full max-w-5xl flex-col items-center gap-4"
                @click.stop
                @touchstart="onTouchStart($event)"
                @touchend="onTouchEnd($event)"
            >
                <img
                    x-show="carouselIndex !== null"
                    :src="carouselIndex !== null ? photos[carouselIndex].url : ''"
                    :alt="carouselIndex !== null ? photos[carouselIndex].name : ''"
                    class="max-h-[75vh] max-w-full rounded-lg object-contain shadow-2xl select-none"
                    draggable="false"
                >

                <div class="flex flex-wrap items-center justify-center gap-3 text-sm text-white">
                    <span
                        x-show="carouselIndex !== null"
                        x-text="`${carouselIndex + 1} / ${photos.length}`"
                    ></span>
                    <button
                        type="button"
                        class="rounded-lg bg-white px-3 py-2 font-semibold text-gray-900 hover:bg-gray-100"
                        x-show="downloadBaseUrl"
                        @click="downloadCurrent()"
                    >
                        {{ __('app.guest_messages_photos_download_current') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
