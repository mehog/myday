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
        <form wire:submit="submitPhotos" class="space-y-4">
            <div>
                <label for="photoFiles" class="block text-sm text-[var(--color-text-muted)] mb-2">
                    {{ __('invitation.select_photos') }}
                </label>
                <input
                    id="photoFiles"
                    type="file"
                    wire:model="photoFiles"
                    accept="image/*"
                    multiple
                    class="w-full text-sm text-[var(--color-text-muted)] file:mr-4 file:rounded-lg file:border-0 file:bg-[var(--color-primary)] file:px-4 file:py-2 file:text-[var(--color-bg)] file:cursor-pointer"
                >
                @error('photoFiles')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
                @error('photoFiles.*')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div wire:loading wire:target="photoFiles" class="text-sm text-[var(--color-text-muted)]">
                {{ __('invitation.uploading_photos') }}
            </div>
            <div class="flex sm:justify-end">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="submitPhotos, photoFiles"
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
