<div class="text-center">
    <div class="onboarding-story-art mx-auto mb-8 flex items-center justify-center" aria-hidden="true">
        @include('livewire.onboarding.partials.story-svg-'.$illustration)
    </div>

    <h1 class="landing-heading text-2xl sm:text-3xl font-semibold text-[#1a1208] mb-3">
        {{ $title }}
    </h1>
    <p class="landing-body text-[#5c5246] mb-8 text-sm leading-relaxed max-w-sm mx-auto">
        {{ $body }}
    </p>

    <button
        type="button"
        wire:click="nextStep"
        class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition"
    >
        {{ __('onboarding.next') }}
    </button>
</div>
