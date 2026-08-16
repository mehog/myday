<div class="min-h-screen flex flex-col items-center justify-center px-6 py-16">
    <div class="max-w-md w-full text-center">
        <h1 class="landing-heading text-2xl font-semibold text-[#1a1208] mb-3">
            {{ __('onboarding.preview_missing_title') }}
        </h1>
        <p class="landing-body text-[#5c5246] mb-8 text-sm">
            {{ __('onboarding.preview_missing_body') }}
        </p>
        <a href="{{ route('onboarding') }}" class="inline-flex landing-btn-primary px-6 py-3 rounded-xl landing-heading">
            {{ __('onboarding.preview_back_to_onboarding') }}
        </a>
    </div>
</div>
