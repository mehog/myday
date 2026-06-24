<template x-if="pushError === 'push_needs_install'">
    <div class="text-sm text-[var(--color-text-muted)] mt-2 text-left space-y-1 max-w-md mx-auto">
        <p class="font-medium text-[var(--color-text)]">{{ __('app.push_install_title') }}</p>
        <p>{{ __('app.push_install_step1') }}</p>
        <p>{{ __('app.push_install_step2') }}</p>
        <p>{{ __('app.push_install_step3') }}</p>
    </div>
</template>
<p
    x-show="pushError && pushError !== 'push_needs_install'"
    x-cloak
    class="text-sm text-red-400 mt-2"
    x-text="pushErrorMessages[pushError] ?? pushError"
></p>
