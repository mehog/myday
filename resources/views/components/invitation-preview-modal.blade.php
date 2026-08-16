@props([
    'title' => null,
    'closeLabel' => null,
])

@php
    $modalTitle = $title ?? __('onboarding.preview_modal_title');
    $modalCloseLabel = $closeLabel ?? __('onboarding.preview_close');
@endphp

<div
    x-data="invitationPreviewModal(@js($modalTitle))"
    x-on:invitation-preview-open.window="show($event.detail)"
    x-on:keydown.escape.window="open && hide()"
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60"
        role="dialog"
        aria-modal="true"
        :aria-label="title"
        x-on:click.self="hide()"
        style="display: none;"
    >
        <div class="relative w-full max-w-[420px] max-h-[90vh] flex flex-col">
            <button
                type="button"
                x-on:click="hide()"
                class="absolute -top-2 -right-2 z-10 w-10 h-10 rounded-full bg-white text-[#1a1208] shadow-lg flex items-center justify-center hover:bg-[#fafaf8]"
                aria-label="{{ $modalCloseLabel }}"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="landing-phone-frame overflow-hidden bg-white shadow-2xl rounded-[2rem] border border-[#1a1208]/15">
                <template x-if="open && url">
                    <iframe
                        :src="url"
                        :title="title"
                        class="w-full h-[min(78vh,720px)] border-0"
                    ></iframe>
                </template>
            </div>
        </div>
    </div>
</div>
