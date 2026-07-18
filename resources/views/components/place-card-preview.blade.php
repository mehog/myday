<div class="pc-preview-outer">
    <p class="pc-preview-label">{{ __('guests.place_cards_preview') }}</p>

    <div
        class="pc-preview-viewport"
        x-data="{
            bg: $wire.$entangle('mountedActions.0.data.bg'),
            text: $wire.$entangle('mountedActions.0.data.text'),
            accent: $wire.$entangle('mountedActions.0.data.accent'),
        }"
    >
        <div
            class="pc-preview-card"
            x-bind:style="{
                backgroundColor: bg || '#FDF8F0',
                color: text || '#2C1810',
                '--pc-accent': accent || '#C9A227',
            }"
        >
            <div class="pc-preview-back">
                <div class="pc-preview-name">{{ __('guests.place_cards_preview_guest') }}</div>
                <div class="pc-preview-plus-one">&amp; {{ __('guests.place_cards_preview_plus_one') }}</div>
            </div>

            <div class="pc-preview-fold"></div>

            <div class="pc-preview-front">
                <div class="pc-preview-content">
                    <div class="pc-preview-qr" aria-hidden="true"></div>
                    <div class="pc-preview-cta">
                        <div class="pc-preview-cta-rule"></div>
                        <div class="pc-preview-scan-cta">{{ __('guests.place_cards_scan_cta') }}</div>
                        <div class="pc-preview-cta-rule"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .pc-preview-outer {
        margin-bottom: 0.75rem;
    }

    .pc-preview-label {
        margin: 0 0 0.75rem;
        font-size: 0.75rem;
        line-height: 1.25;
        color: rgb(107 114 128);
        text-align: center;
    }

    .pc-preview-viewport {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 0.5rem 0 1rem;
    }

    .pc-preview-card {
        position: relative;
        width: 240px;
        height: 274px;
        overflow: hidden;
        border-radius: 2px;
        box-shadow: 0 2px 8px rgb(0 0 0 / 0.12);
    }

    .pc-preview-back {
        position: absolute;
        top: 0;
        left: 0;
        width: 240px;
        height: 137px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 12px 16px;
        transform: rotate(180deg);
    }

    .pc-preview-fold {
        position: absolute;
        top: 137px;
        left: 0;
        width: 240px;
        height: 0;
        border-top: 2px dashed var(--pc-accent, #C9A227);
    }

    .pc-preview-front {
        position: absolute;
        top: 137px;
        left: 0;
        width: 240px;
        height: 137px;
        padding: 7px;
    }

    .pc-preview-content {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        padding: 0 6px;
    }

    .pc-preview-qr {
        flex: 0 0 42%;
        width: 56px;
        height: 56px;
        margin: 0 auto;
        background:
            linear-gradient(90deg, currentColor 2px, transparent 2px) 0 0 / 8px 8px,
            linear-gradient(currentColor 2px, transparent 2px) 0 0 / 8px 8px;
        opacity: 0.35;
        border: 2px solid currentColor;
    }

    .pc-preview-cta {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 0 4px;
    }

    .pc-preview-scan-cta {
        font-size: 9px;
        font-weight: 700;
        line-height: 1.35;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        white-space: pre-line;
    }

    .pc-preview-cta-rule {
        width: 70%;
        height: 0;
        border-top: 1px solid var(--pc-accent, #C9A227);
        margin: 4px auto;
    }

    .pc-preview-name {
        font-size: 18px;
        font-weight: 700;
        line-height: 1.2;
    }

    .pc-preview-plus-one {
        margin-top: 4px;
        font-size: 13px;
        line-height: 1.2;
        opacity: 0.85;
    }

</style>
