@php
    $downloadBaseUrl = route('guest-messages.photos.download');
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="hidden text-xl font-semibold lg:block">{{ __('app.guest_messages_all_photos_title') }}</h2>
        <div class="flex flex-wrap gap-2">
            @if ($totalPhotoCount > 0)
                <x-dashboard.button variant="secondary" href="{{ $downloadBaseUrl }}" target="_blank">
                    {{ __('app.guest_messages_download_photos') }}
                </x-dashboard.button>
            @endif
            <x-dashboard.button variant="outline" href="{{ route('dashboard.messages') }}">
                {{ __('app.guest_messages_back') }}
            </x-dashboard.button>
        </div>
    </div>

    @if ($totalPhotoCount === 0)
        <x-dashboard.card>
            <p class="text-sm text-muted-foreground">{{ __('app.guest_messages_photos_empty') }}</p>
        </x-dashboard.card>
    @else
        <div
            class="w-full"
            x-data="{
                photos: @js($photos),
                hasMore: @js($hasMore),
                isLoading: @js($isLoading),
                totalPhotoCount: @js($totalPhotoCount),
                selected: [],
                carouselPhotos: [],
                carouselMessageId: null,
                carouselIndex: null,
                touchStartX: null,
                downloadBaseUrl: @js($downloadBaseUrl),
                groupedPhotos() {
                    const groups = {}

                    this.photos.forEach((photo) => {
                        groups[photo.message_id] ??= []
                        groups[photo.message_id].push({
                            index: photo.index,
                            url: photo.url,
                            name: photo.name,
                        })
                    })

                    return groups
                },
                get selectedCount() {
                    return this.selected.length
                },
                get allSelected() {
                    return this.photos.length > 0 && this.selected.length === this.photos.length
                },
                photoKey(photo) {
                    return photo.key
                },
                isSelected(key) {
                    return this.selected.includes(key)
                },
                toggleSelect(key) {
                    if (this.isSelected(key)) {
                        this.selected = this.selected.filter((value) => value !== key)
                        return
                    }

                    this.selected = [...this.selected, key]
                },
                selectAll() {
                    this.selected = this.photos.map((photo) => photo.key)
                },
                clearSelection() {
                    this.selected = []
                },
                openGroupCarousel(messageId, index) {
                    this.carouselMessageId = messageId
                    this.carouselPhotos = this.groupedPhotos()[messageId] ?? []

                    if (this.carouselPhotos.length === 0) {
                        return
                    }

                    this.carouselIndex = this.carouselPhotos.findIndex((photo) => photo.index === index)

                    if (this.carouselIndex < 0) {
                        this.carouselIndex = 0
                    }
                },
                closeCarousel() {
                    this.carouselIndex = null
                    this.carouselPhotos = []
                    this.carouselMessageId = null
                },
                next() {
                    if (this.carouselIndex === null || this.carouselPhotos.length === 0) {
                        return
                    }

                    this.carouselIndex = (this.carouselIndex + 1) % this.carouselPhotos.length
                },
                prev() {
                    if (this.carouselIndex === null || this.carouselPhotos.length === 0) {
                        return
                    }

                    this.carouselIndex = (this.carouselIndex - 1 + this.carouselPhotos.length) % this.carouselPhotos.length
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
                downloadUrl(selections) {
                    const params = new URLSearchParams()

                    selections.forEach((item, index) => {
                        params.append(`selections[${index}][message]`, String(item.message_id))
                        params.append(`selections[${index}][index]`, String(item.index))
                    })

                    return `${this.downloadBaseUrl}?${params.toString()}`
                },
                downloadSelected() {
                    if (this.selected.length === 0) {
                        return
                    }

                    const selections = this.selected
                        .map((key) => this.photos.find((photo) => photo.key === key))
                        .filter(Boolean)

                    if (selections.length === 0) {
                        return
                    }

                    window.location.href = this.downloadUrl(selections)
                },
                downloadCurrent() {
                    if (this.carouselIndex === null || this.carouselPhotos.length === 0 || this.carouselMessageId === null) {
                        return
                    }

                    const current = this.carouselPhotos[this.carouselIndex]

                    window.location.href = this.downloadUrl([{
                        message_id: this.carouselMessageId,
                        index: current.index,
                    }])
                },
            }"
            x-init="
                $watch(() => $wire.photos, (value) => { photos = value })
                $watch(() => $wire.hasMore, (value) => { hasMore = value })
            "
            @keydown.escape.window="closeCarousel()"
            @keydown.arrow-left.window="if (carouselIndex !== null) prev()"
            @keydown.arrow-right.window="if (carouselIndex !== null) next()"
            x-effect="document.body.classList.toggle('overflow-hidden', carouselIndex !== null)"
        >
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <p class="mr-auto text-sm text-muted-foreground">
                    {{ __('app.guest_messages_col_photo_count', ['count' => $totalPhotoCount]) }}
                </p>
                <button
                    type="button"
                    class="rounded-md border border-border px-3 py-1.5 text-sm font-medium hover:bg-accent"
                    x-show="! allSelected"
                    @click="selectAll()"
                >
                    {{ __('app.guest_messages_photos_select_all') }}
                </button>
                <button
                    type="button"
                    class="rounded-md border border-border px-3 py-1.5 text-sm font-medium hover:bg-accent"
                    x-show="selectedCount > 0"
                    x-cloak
                    @click="clearSelection()"
                >
                    {{ __('app.guest_messages_photos_clear_selection') }}
                </button>
                <button
                    type="button"
                    class="rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground hover:opacity-90"
                    x-show="selectedCount > 0"
                    x-cloak
                    @click="downloadSelected()"
                >
                    <span x-text="selectedCount === 1
                        ? @js(__('app.guest_messages_photos_download_one'))
                        : @js(__('app.guest_messages_photos_download_selected')).replace(':count', selectedCount)">
                    </span>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                <template x-for="photo in photos" :key="photo.key">
                    <div class="group relative">
                        <button
                            type="button"
                            class="relative aspect-square w-full overflow-hidden rounded-xl border border-border bg-muted transition hover:opacity-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                            :aria-label="@js(__('app.guest_messages_photos_open')) + ' ' + photo.sender_name"
                            @click="openGroupCarousel(photo.message_id, photo.index)"
                        >
                            <img
                                :src="photo.url"
                                :alt="photo.name"
                                loading="lazy"
                                class="h-full w-full object-cover"
                            >
                        </button>

                        <label
                            class="absolute left-2 top-2 z-10 flex h-7 w-7 cursor-pointer items-center justify-center rounded-md bg-background/90 shadow ring-1 ring-border backdrop-blur"
                            @click.stop
                        >
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-border text-primary focus:ring-primary"
                                :checked="isSelected(photo.key)"
                                @change="toggleSelect(photo.key)"
                                :aria-label="@js(__('app.guest_messages_photos_select')) + ' ' + photo.sender_name"
                            >
                        </label>

                        <p
                            class="pointer-events-none absolute inset-x-0 bottom-0 truncate rounded-b-xl bg-gradient-to-t from-black/70 to-transparent px-2 pb-2 pt-6 text-xs font-medium text-white"
                            x-text="photo.sender_name"
                        ></p>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex justify-center" x-show="hasMore" x-cloak>
                <x-dashboard.button
                    type="button"
                    variant="secondary"
                    wire:click="loadMore"
                    wire:loading.attr="disabled"
                    wire:target="loadMore"
                >
                    <span wire:loading.remove wire:target="loadMore">{{ __('app.guest_messages_all_photos_load_more') }}</span>
                    <span wire:loading wire:target="loadMore">{{ __('app.guest_messages_all_photos_loading') }}</span>
                </x-dashboard.button>
            </div>

            <p
                class="mt-6 text-center text-sm text-muted-foreground"
                x-show="! hasMore && photos.length > 0"
                x-cloak
            >
                {{ __('app.guest_messages_all_photos_end') }}
            </p>

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
                    x-show="carouselPhotos.length > 1"
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
                    x-show="carouselPhotos.length > 1"
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
                        :src="carouselIndex !== null ? carouselPhotos[carouselIndex].url : ''"
                        :alt="carouselIndex !== null ? carouselPhotos[carouselIndex].name : ''"
                        class="max-h-[75vh] max-w-full rounded-lg object-contain shadow-2xl select-none"
                        draggable="false"
                    >

                    <div class="flex flex-wrap items-center justify-center gap-3 text-sm text-white">
                        <span
                            x-show="carouselIndex !== null"
                            x-text="`${carouselIndex + 1} / ${carouselPhotos.length}`"
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
</div>
