<section id="naruči" class="landing-section px-6 py-20 scroll-mt-20">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-10 landing-fade-in">
            <h2 class="landing-heading text-3xl sm:text-4xl text-[#faf6ee] mb-4">
                {{ __('landing.contact_title') }}
            </h2>
            <p class="landing-body text-[#d4c4a8]">
                {{ __('landing.contact_subtitle') }}
            </p>
        </div>

        <div class="landing-card rounded-2xl border border-white/10 bg-[#1a1208]/80 p-6 sm:p-8 landing-fade-in">
            <livewire:contact-form />
        </div>
    </div>
</section>
