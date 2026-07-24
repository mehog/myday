{{-- Photos --}}
<div @class([
    'rounded-2xl border p-6',
    'border-[var(--color-primary)]/20 bg-[var(--color-bg-soft)]/80' => $this->canSendPhotos(),
    'border-white/5 bg-[var(--color-bg-soft)]/40 opacity-70' => ! $this->canSendPhotos(),
])>
    <h2 class="invitation-heading text-xl text-[var(--color-text)] mb-2">
        {{ __('invitation.send_photos') }}
    </h2>

    @if ($this->canSendPhotos())
        <p class="invitation-body text-sm text-[var(--color-text-muted)] mb-4">
            {{ __('invitation.send_photos_description') }}
        </p>
        <form
            wire:submit="submitPhotos"
            class="space-y-4"
            x-data="{
                uploading: false,
                uploaded: 0,
                total: 0,
                error: null,
                previews: [],
                maxPhotos: 10,
                clearPreviews() {
                    this.previews.forEach((preview) => URL.revokeObjectURL(preview.url));
                    this.previews = [];
                },
                selectionLabel() {
                    if (this.previews.length === 0) {
                        return @js(__('invitation.no_photos_selected'));
                    }

                    return @js(__('invitation.photos_selected')).replace(':count', this.previews.length);
                },
                async removePhoto(index) {
                    const preview = this.previews[index];

                    if (! preview || preview.status === 'uploading') {
                        return;
                    }

                    if (preview.tmpFilename) {
                        await new Promise((resolve) => {
                            this.$wire.removeUpload('photoFiles', preview.tmpFilename, () => resolve());
                        });
                    }

                    URL.revokeObjectURL(preview.url);
                    this.previews.splice(index, 1);
                },
                async uploadPhotos(event) {
                    const files = Array.from(event.target.files);

                    if (files.length === 0) {
                        return;
                    }

                    const remaining = this.maxPhotos - this.previews.length;

                    if (remaining <= 0) {
                        this.error = @js(__('invitation.photos_max'));
                        event.target.value = '';

                        return;
                    }

                    const filesToUpload = files.slice(0, remaining);
                    const startIndex = this.previews.length;

                    this.previews = [
                        ...this.previews,
                        ...filesToUpload.map((file) => ({
                            name: file.name,
                            url: URL.createObjectURL(file),
                            status: 'pending',
                            tmpFilename: null,
                        })),
                    ];

                    this.uploading = true;
                    this.uploaded = 0;
                    this.total = filesToUpload.length;
                    this.error = null;

                    try {
                        for (let offset = 0; offset < filesToUpload.length; offset++) {
                            const index = startIndex + offset;
                            this.previews[index].status = 'uploading';

                            await new Promise((resolve, reject) => {
                                this.$wire.upload(
                                    'photoFiles',
                                    filesToUpload[offset],
                                    (tmpFilename) => {
                                        this.uploaded++;
                                        this.previews[index].tmpFilename = tmpFilename;
                                        this.previews[index].status = 'done';
                                        resolve();
                                    },
                                    () => reject(new Error('Upload failed')),
                                );
                            });
                        }
                    } catch (error) {
                        this.error = @js(__('invitation.photos_upload_error'));
                        const failedIndex = startIndex + this.uploaded;
                        if (this.previews[failedIndex]) {
                            this.previews[failedIndex].status = 'error';
                        }
                    } finally {
                        this.uploading = false;
                        event.target.value = '';
                    }
                },
            }"
            x-on:photos-submitted.window="clearPreviews()"
        >
            <div>
                <span class="block text-sm text-[var(--color-text-muted)] mb-2">
                    {{ __('invitation.select_photos') }}
                </span>
                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        x-on:click="$refs.photoInput.click()"
                        x-bind:disabled="uploading || previews.length >= maxPhotos"
                        class="rounded-lg bg-[var(--color-primary)] px-4 py-2 text-sm text-[var(--color-bg)] transition disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ __('invitation.choose_photos') }}
                    </button>
                    <span
                        class="text-sm text-[var(--color-text-muted)]"
                        x-text="selectionLabel()"
                    ></span>
                    <input
                        id="photoFiles"
                        type="file"
                        accept="image/*"
                        multiple
                        x-ref="photoInput"
                        x-on:change="uploadPhotos($event)"
                        x-bind:disabled="uploading || previews.length >= maxPhotos"
                        class="sr-only"
                    >
                </div>
                <div
                    x-show="previews.length"
                    x-cloak
                    class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5"
                >
                    <template x-for="(preview, index) in previews" :key="preview.url">
                        <div class="relative aspect-square overflow-hidden rounded-lg border border-[var(--color-primary)]/15 bg-[var(--color-bg)]">
                            <img
                                :src="preview.url"
                                :alt="preview.name"
                                class="h-full w-full object-cover"
                            >
                            <div class="absolute inset-x-0 bottom-0 truncate bg-black/55 px-1.5 py-0.5 text-[10px] leading-tight text-white" x-text="preview.name"></div>
                            <div
                                x-show="preview.status === 'uploading'"
                                class="absolute inset-0 flex items-center justify-center bg-black/35"
                            >
                                <span class="h-5 w-5 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                            </div>
                            <div
                                x-show="preview.status === 'done'"
                                class="absolute bottom-5 left-1 flex h-5 w-5 items-center justify-center rounded-full bg-[var(--color-primary)] text-[10px] text-[var(--color-bg)]"
                            >
                                ✓
                            </div>
                            <div
                                x-show="preview.status === 'error'"
                                class="absolute inset-0 flex items-center justify-center bg-red-900/50 text-xs text-white"
                            >
                                !
                            </div>
                            <button
                                type="button"
                                x-show="preview.status !== 'uploading'"
                                x-on:click="removePhoto(index)"
                                x-bind:aria-label="@js(__('invitation.remove_photo'))"
                                class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-black/65 text-xs leading-none text-white transition hover:bg-black/85"
                            >
                                ×
                            </button>
                        </div>
                    </template>
                </div>
                <p
                    x-show="uploading"
                    x-cloak
                    class="mt-2 text-sm text-[var(--color-text-muted)]"
                >
                    {{ __('invitation.uploading_photos') }}
                    <span x-text="`${uploaded}/${total}`"></span>
                </p>
                <p
                    x-show="error"
                    x-cloak
                    x-text="error"
                    class="mt-2 text-sm text-red-400"
                ></p>
                @error('photoFiles')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
                @error('photoFiles.*')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex sm:justify-end">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="submitPhotos, photoFiles"
                    x-bind:disabled="uploading || previews.length === 0"
                    class="rsvp-btn rsvp-btn-yes w-full sm:w-auto rounded-xl px-6 py-3 invitation-heading text-base transition disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="submitPhotos">{{ __('invitation.send_photos') }}</span>
                    <span wire:loading wire:target="submitPhotos">{{ __('invitation.saving') }}</span>
                </button>
            </div>
        </form>
    @else
        <p class="invitation-body text-sm text-[var(--color-text-muted)]">
            {{ __('invitation.photos_available_from', ['date' => $event->wedding_date->translatedFormat('j. F Y.')]) }}
        </p>
    @endif
</div>
