<div>
    <x-theme :theme="$event->theme">
        <div class="invitation-page min-h-screen">
            <section class="invitation-section py-16 px-6">
                <div class="max-w-2xl mx-auto invitation-fade-in">
                    <div class="text-center mb-10">
                        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)] mb-3">
                            {{ $event->couple_names }}
                        </p>
                        <h1 class="invitation-heading text-4xl text-[var(--color-text)] mb-4">
                            @if ($fromPlaceCardQr)
                                {{ __('invitation.contact_page_qr_title') }}
                            @else
                                {{ __('invitation.contact_page_title') }}
                            @endif
                        </h1>
                        <p class="invitation-body text-[var(--color-text-muted)]">
                            @if ($fromPlaceCardQr)
                                {{ __('invitation.contact_page_qr_description', ['name' => $senderName]) }}
                            @else
                                {{ __('invitation.contact_page_description', ['name' => $senderName]) }}
                            @endif
                        </p>
                    </div>

                    <div class="mb-8 text-center">
                        <a
                            href="{{ route('invitation.guest', [$event->slug, $guest->token]) }}#rsvp"
                            class="text-sm text-[var(--color-primary)] hover:underline transition"
                        >
                            &larr; {{ __('invitation.back_to_invitation') }}
                        </a>
                    </div>

                    <div class="space-y-6">
                        @includeWhen($event->isWeddingDay(), 'livewire.partials.guest-contact-photos-section')

                        {{-- Text message --}}
                        <div class="rounded-2xl border border-[var(--color-primary)]/20 bg-[var(--color-bg-soft)]/80 p-6">
                            <h2 class="invitation-heading text-xl text-[var(--color-text)] mb-2">
                                {{ __('invitation.send_text_message') }}
                            </h2>
                            <p class="invitation-body text-sm text-[var(--color-text-muted)] mb-4">
                                {{ __('invitation.send_text_message_description') }}
                            </p>
                            <form wire:submit="submitText" class="space-y-4">
                                <div>
                                    <label for="textContent" class="block text-sm text-[var(--color-text-muted)] mb-2">
                                        {{ __('invitation.your_message') }}
                                    </label>
                                    <textarea
                                        id="textContent"
                                        wire:model="textContent"
                                        rows="5"
                                        class="w-full rounded-xl border border-white/10 bg-[var(--color-bg)] px-4 py-3 text-[var(--color-text)] placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-primary)] focus:outline-none resize-y"
                                        placeholder="{{ __('invitation.message_placeholder') }}"
                                    ></textarea>
                                    @error('textContent')
                                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex sm:justify-end">
                                    <button
                                        type="submit"
                                        wire:loading.attr="disabled"
                                        wire:target="submitText"
                                        class="rsvp-btn rsvp-btn-yes w-full sm:w-auto rounded-xl px-6 py-3 invitation-heading text-base transition disabled:opacity-60"
                                    >
                                        <span wire:loading.remove wire:target="submitText">{{ __('invitation.send_message') }}</span>
                                        <span wire:loading wire:target="submitText">{{ __('invitation.saving') }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Audio message --}}
                        <div
                            class="rounded-2xl border border-[var(--color-primary)]/20 bg-[var(--color-bg-soft)]/80 p-6"
                            x-data="{
                                isDemo: @js($isDemo),
                                recording: false,
                                hasRecording: false,
                                mediaRecorder: null,
                                audioChunks: [],
                                audioUrl: null,
                                audioBlob: null,
                                uploading: false,
                                error: null,
                                async startRecording() {
                                    this.error = null;
                                    try {
                                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                                        const preferredMimes = [
                                            'audio/webm;codecs=opus',
                                            'audio/webm',
                                            'audio/ogg;codecs=opus',
                                            'audio/ogg',
                                            'audio/mp4',
                                        ];
                                        const mimeType = preferredMimes.find((t) => MediaRecorder.isTypeSupported(t)) || '';
                                        this.mediaRecorder = new MediaRecorder(stream, mimeType ? { mimeType } : {});
                                        this.audioChunks = [];
                                        this.mediaRecorder.ondataavailable = (e) => {
                                            if (e.data.size > 0) this.audioChunks.push(e.data);
                                        };
                                        this.mediaRecorder.onstop = () => {
                                            stream.getTracks().forEach((track) => track.stop());
                                            this.audioBlob = new Blob(this.audioChunks, { type: this.mediaRecorder.mimeType || 'audio/webm' });
                                            if (this.audioUrl) URL.revokeObjectURL(this.audioUrl);
                                            this.audioUrl = URL.createObjectURL(this.audioBlob);
                                            this.hasRecording = true;
                                        };
                                        this.mediaRecorder.start();
                                        this.recording = true;
                                    } catch (e) {
                                        this.error = @js(__('invitation.microphone_error'));
                                    }
                                },
                                stopRecording() {
                                    if (this.mediaRecorder && this.recording) {
                                        this.mediaRecorder.stop();
                                        this.recording = false;
                                    }
                                },
                                discardRecording() {
                                    if (this.audioUrl) URL.revokeObjectURL(this.audioUrl);
                                    this.audioUrl = null;
                                    this.audioBlob = null;
                                    this.hasRecording = false;
                                    this.audioChunks = [];
                                },
                                sendRecording() {
                                    if (this.isDemo) {
                                        this.$dispatch('demo-message-sent');
                                        return;
                                    }
                                    if (! this.audioBlob) return;
                                    this.uploading = true;
                                    this.error = null;
                                    const blobType = this.audioBlob.type;
                                    let extension = 'webm';
                                    if (blobType.includes('ogg')) {
                                        extension = 'ogg';
                                    } else if (blobType.includes('mp4') || blobType.includes('m4a')) {
                                        extension = 'mp4';
                                    } else if (blobType.includes('3gpp') || blobType.includes('3gp')) {
                                        extension = '3gp';
                                    }
                                    const file = new File([this.audioBlob], `recording.${extension}`, { type: this.audioBlob.type || 'audio/webm' });
                                    this.$wire.upload('audioFile', file, () => {
                                        this.$wire.submitAudio().then(() => {
                                            this.discardRecording();
                                            this.uploading = false;
                                        }).catch(() => {
                                            this.uploading = false;
                                        });
                                    }, () => {
                                        this.error = @js(__('invitation.audio_upload_error'));
                                        this.uploading = false;
                                    });
                                },
                            }"
                        >
                            <h2 class="invitation-heading text-xl text-[var(--color-text)] mb-2">
                                {{ __('invitation.send_audio_message') }}
                            </h2>
                            <p class="invitation-body text-sm text-[var(--color-text-muted)] mb-4">
                                {{ __('invitation.send_audio_message_description') }}
                            </p>

                            <div class="mb-4">
                                <div x-show="! recording && ! hasRecording" class="flex sm:justify-end">
                                    <button
                                        type="button"
                                        @click="startRecording()"
                                        class="rsvp-btn rsvp-btn-yes w-full sm:w-auto rounded-xl px-6 py-3 invitation-heading text-base transition"
                                    >
                                        {{ __('invitation.record_audio') }}
                                    </button>
                                </div>
                                <div x-show="recording" x-cloak class="flex sm:justify-end">
                                    <button
                                        type="button"
                                        @click="stopRecording()"
                                        class="rsvp-btn rsvp-btn-no w-full sm:w-auto rounded-xl px-6 py-3 invitation-heading text-base transition"
                                    >
                                        {{ __('invitation.stop_recording') }}
                                    </button>
                                </div>
                                <template x-if="hasRecording">
                                    <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                                        <audio x-ref="playback" :src="audioUrl" controls class="w-full max-w-sm"></audio>
                                        <button
                                            type="button"
                                            @click="discardRecording()"
                                            class="text-sm text-[var(--color-text-muted)] hover:text-[var(--color-primary)] transition"
                                        >
                                            {{ __('invitation.discard_recording') }}
                                        </button>
                                        <button
                                            type="button"
                                            @click="sendRecording()"
                                            :disabled="uploading"
                                            class="rsvp-btn rsvp-btn-yes w-full sm:w-auto rounded-xl px-6 py-3 invitation-heading text-base transition disabled:opacity-60"
                                        >
                                            <span x-show="! uploading">{{ __('invitation.send_message') }}</span>
                                            <span x-show="uploading">{{ __('invitation.saving') }}</span>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <p x-show="error" x-text="error" class="text-sm text-red-400"></p>
                            @error('audioFile')
                                <p class="text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        @includeUnless($event->isWeddingDay(), 'livewire.partials.guest-contact-photos-section')
                    </div>
                </div>
            </section>

            <footer class="py-8 px-6 border-t border-[color-mix(in_srgb,var(--color-text)_10%,transparent)] flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="shrink-0">
                    <img
                        src="{{ asset('icons/nd-logo-transparent.webp') }}"
                        alt="{{ config('app.name', 'NasDan') }}"
                        class="max-w-[50px] w-full h-auto"
                        style="max-width: 50px;"
                    >
                </a>
                <x-locale-picker
                    class="justify-end"
                    selectClass="text-sm py-1.5 px-3 min-w-[9rem] cursor-pointer rounded-xl border border-[color-mix(in_srgb,var(--color-primary)_40%,transparent)] bg-[var(--color-bg-soft)] text-[var(--color-text)]"
                    labelClass="text-sm text-[var(--color-text-muted)]"
                />
            </footer>

            <div
                x-data="{ show: @entangle('messageSent').live, isDemo: @js($isDemo) }"
                @demo-message-sent.window="show = true"
                x-show="show"
                x-transition.opacity
                x-cloak
                @keydown.escape.window="if (show) { show = false; if (!isDemo) $wire.dismissSuccess() }"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4"
                @click.self="show = false; if (!isDemo) $wire.dismissSuccess()"
                style="display: none;"
            >
                <div
                    x-show="show"
                    x-transition
                    class="w-full max-w-md rounded-2xl border border-[var(--color-primary)]/20 bg-[var(--color-bg-soft)] p-8 text-center shadow-xl"
                    @click.stop
                >
                    <h3 class="invitation-heading text-2xl text-[var(--color-text)] mb-2">
                        {{ __('invitation.message_sent') }}
                    </h3>
                    <p class="invitation-body text-[var(--color-text-muted)] mb-8">
                        {{ __('invitation.message_sent_description') }}
                    </p>
                    <button
                        type="button"
                        @click="show = false; if (!isDemo) $wire.dismissSuccess()"
                        class="rsvp-btn rsvp-btn-yes w-full py-4 rounded-xl invitation-heading text-lg transition"
                    >
                        {{ __('invitation.message_sent_close') }}
                    </button>
                </div>
            </div>
        </div>
    </x-theme>
</div>
