@php
    /** @var \App\Models\GuestMessage $record */

    $photos = collect($record->file_paths ?? [])
        ->values()
        ->map(fn (string $path, int $index): array => [
            'index' => $index,
            'url' => \App\Support\MediaDisk::url($path),
            'name' => basename($path),
        ])
        ->filter(fn (array $photo): bool => filled($photo['url']))
        ->values()
        ->all();

    $downloadBaseUrl = route('guest-messages.photos.download', ['message' => $record->id]);
@endphp

@if ($photos === [])
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ __('app.guest_messages_photos_empty') }}
    </p>
@else
    <div
        class="w-full"
        x-data="{
            photos: @js($photos),
            selected: [],
            carouselIndex: null,
            downloadBaseUrl: @js($downloadBaseUrl),
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
            downloadUrl(indexes) {
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

                window.location.href = this.downloadUrl(this.selected)
            },
            downloadCurrent() {
                if (this.carouselIndex === null) {
                    return
                }

                window.location.href = this.downloadUrl([this.photos[this.carouselIndex].index])
            },
        }"
        @keydown.escape.window="closeCarousel()"
        @keydown.arrow-left.window="if (carouselIndex !== null) prev()"
        @keydown.arrow-right.window="if (carouselIndex !== null) next()"
        x-effect="document.body.classList.toggle('overflow-hidden', carouselIndex !== null)"
    >
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <button
                type="button"
                class="fi-btn fi-size-sm fi-btn-color-gray fi-outlined relative inline-grid grid-flow-col items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold outline-none transition duration-75"
                x-show="! allSelected"
                @click="selectAll()"
            >
                {{ __('app.guest_messages_photos_select_all') }}
            </button>
            <button
                type="button"
                class="fi-btn fi-size-sm fi-btn-color-gray fi-outlined relative inline-grid grid-flow-col items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold outline-none transition duration-75"
                x-show="selectedCount > 0"
                x-cloak
                @click="clearSelection()"
            >
                {{ __('app.guest_messages_photos_clear_selection') }}
            </button>
            <button
                type="button"
                class="fi-btn fi-size-sm fi-color-primary fi-btn-color-primary relative inline-grid grid-flow-col items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold outline-none transition duration-75"
                x-show="selectedCount > 0"
                x-cloak
                @click="downloadSelected()"
            >
                <span x-text="selectedCount === 1
                    ? @js(__('app.guest_messages_photos_download_one'))
                    : @js(__('app.guest_messages_photos_download_selected')).replace(':count', selectedCount)">
                </span>
            </button>
            <span
                class="text-sm text-gray-500 dark:text-gray-400"
                x-show="selectedCount > 0"
                x-cloak
                x-text="@js(__('app.guest_messages_photos_selected_count')).replace(':count', selectedCount)"
            ></span>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
            <template x-for="photo in photos" :key="photo.index">
                <div class="group relative">
                    <button
                        type="button"
                        class="relative aspect-square w-full overflow-hidden rounded-xl border border-gray-200 bg-gray-100 bg-cover bg-center shadow-sm transition hover:opacity-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:border-white/10 dark:bg-gray-800"
                        :style="`background-image: url('${photo.url}')`"
                        :aria-label="@js(__('app.guest_messages_photos_open')) + ' ' + (photo.index + 1)"
                        @click="openCarousel(photo.index)"
                    >
                        <span class="sr-only" x-text="photo.name"></span>
                    </button>

                    <label
                        class="absolute left-2 top-2 z-10 flex h-7 w-7 cursor-pointer items-center justify-center rounded-md bg-white/90 shadow ring-1 ring-gray-900/10 backdrop-blur dark:bg-gray-900/80 dark:ring-white/20"
                        @click.stop
                    >
                        <input
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            :checked="isSelected(photo.index)"
                            @change="toggleSelect(photo.index)"
                            :aria-label="@js(__('app.guest_messages_photos_select')) + ' ' + (photo.index + 1)"
                        >
                    </label>
                </div>
            </template>
        </div>

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

            <div class="flex max-h-full w-full max-w-5xl flex-col items-center gap-4" @click.stop>
                <img
                    x-show="carouselIndex !== null"
                    :src="carouselIndex !== null ? photos[carouselIndex].url : ''"
                    :alt="carouselIndex !== null ? photos[carouselIndex].name : ''"
                    class="max-h-[75vh] max-w-full rounded-lg object-contain shadow-2xl"
                >

                <div class="flex flex-wrap items-center justify-center gap-3 text-sm text-white">
                    <span
                        x-show="carouselIndex !== null"
                        x-text="`${carouselIndex + 1} / ${photos.length}`"
                    ></span>
                    <button
                        type="button"
                        class="rounded-lg bg-white px-3 py-2 font-semibold text-gray-900 hover:bg-gray-100"
                        @click="downloadCurrent()"
                    >
                        {{ __('app.guest_messages_photos_download_current') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
