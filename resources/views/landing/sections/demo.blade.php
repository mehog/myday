<section
    id="demo"
    class="landing-section landing-section-alt px-6 py-20 scroll-mt-20"
    x-data="{
        modalOpen: false,
        publicUrl: '',
        personalUrl: '',
        openModal(publicUrl, personalUrl) {
            this.publicUrl = publicUrl;
            this.personalUrl = personalUrl;
            this.modalOpen = true;
        },
        closeModal() {
            this.modalOpen = false;
        },
        openLink(url) {
            if (url) {
                window.open(url, '_blank');
            }
            this.closeModal();
        }
    }"
    @keydown.escape.window="closeModal()"
>
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-10 landing-fade-in">
            <h2 class="landing-heading text-3xl sm:text-4xl text-[#faf6ee] mb-4">
                {{ __('landing.demo_title') }}
            </h2>
            <p class="landing-body text-[#d4c4a8]">
                {{ __('landing.demo_subtitle') }}
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            @foreach ($demos as $demo)
                <div class="landing-card rounded-2xl border border-[#c9a227]/25 overflow-hidden landing-fade-in">
                    <div class="aspect-video bg-gradient-to-br from-[#2a1f0f] to-[#1a1208] flex flex-col items-center justify-center p-8 border-b border-white/5">
                        <p class="landing-heading text-3xl sm:text-4xl text-[#faf6ee] mb-2 text-center">
                            {{ $demo['couple'] }}
                        </p>
                        <p class="landing-body text-[#c9a227]">{{ $demo['theme'] }}</p>
                    </div>
                    <div class="p-8 text-center">
                        <button
                            type="button"
                            class="landing-btn-primary px-10 py-4 rounded-xl landing-heading text-lg transition"
                            @click="openModal(@js($demo['publicUrl']), @js($demo['personalUrl']))"
                        >
                            {{ __('landing.demo_cta') }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Link type modal --}}
    <div
        x-show="modalOpen"
        x-transition.opacity
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4"
        @click.self="closeModal()"
        style="display: none;"
    >
        <div
            x-show="modalOpen"
            x-transition
            class="w-full max-w-md rounded-2xl border border-[#c9a227]/20 bg-[#1a1208] p-8 text-center"
            @click.stop
        >
            <h3 class="landing-heading text-2xl text-[#faf6ee] mb-2">
                {{ __('landing.demo_modal_title') }}
            </h3>
            <p class="landing-body text-[#d4c4a8] mb-8">
                {{ __('landing.demo_modal_subtitle') }}
            </p>

            <div class="flex flex-col gap-3">
                <button
                    type="button"
                    class="landing-btn-primary w-full py-4 rounded-xl landing-heading text-lg transition"
                    @click="openLink(publicUrl)"
                >
                    {{ __('landing.demo_modal_public') }}
                </button>
                <button
                    type="button"
                    class="landing-btn-secondary w-full py-4 rounded-xl landing-heading text-lg transition"
                    @click="openLink(personalUrl)"
                    x-bind:disabled="!personalUrl"
                    x-bind:class="!personalUrl && 'opacity-50 cursor-not-allowed'"
                >
                    {{ __('landing.demo_modal_personalized') }}
                </button>
                <button
                    type="button"
                    class="text-sm text-[#d4c4a8] hover:text-[#c9a227] transition mt-2"
                    @click="closeModal()"
                >
                    {{ __('landing.demo_modal_cancel') }}
                </button>
            </div>
        </div>
    </div>
</section>
