<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('landing.meta_title') }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #1a1208;
            background: #faf6ee;
        }

        .page {
            position: relative;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
        }

        .page-2 {
            page-break-before: always;
            background-image: url('{{ $dotPatternUri }}');
            background-repeat: repeat;
        }

        .divider {
            position: absolute;
            top: 0;
            height: 210mm;
            width: 1px;
            background: #c9a227;
        }

        .divider-1 {
            left: 99mm;
        }

        .divider-2 {
            left: 198mm;
        }

        .cover-bg {
            position: absolute;
            top: 0;
            right: 0;
            width: 99mm;
            height: 210mm;
            background: #1a1208;
        }

        .spread {
            position: relative;
            z-index: 1;
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .spread td {
            width: 33.33%;
            vertical-align: top;
            padding: 8mm;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        .panel-title {
            font-size: 13pt;
            font-weight: bold;
            color: #1a1208;
            margin-bottom: 4mm;
            line-height: 1.2;
        }

        .panel-subtitle {
            font-size: 8pt;
            color: #5c4a32;
            margin-bottom: 3mm;
            line-height: 1.4;
        }

        .body-text {
            font-size: 8pt;
            color: #5c4a32;
            line-height: 1.45;
            margin-bottom: 3mm;
        }

        .logo {
            display: block;
            margin: 0 auto 6mm;
            height: 22mm;
        }

        .accent-bar {
            height: 3mm;
            background: #c9a227;
            margin: -8mm -8mm 5mm;
        }

        .step {
            margin-bottom: 3mm;
        }

        .step-number {
            font-size: 11pt;
            font-weight: bold;
            color: #c9a227;
            line-height: 1;
            margin-bottom: 1mm;
        }

        .step-title {
            font-size: 9pt;
            font-weight: bold;
            color: #1a1208;
            margin-bottom: 1mm;
        }

        .step-text {
            font-size: 7pt;
            color: #5c4a32;
            line-height: 1.4;
        }

        .benefit {
            margin-bottom: 4mm;
        }

        .benefit-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1a1208;
            margin-bottom: 1mm;
        }

        .benefit-text {
            font-size: 7.5pt;
            color: #5c4a32;
            line-height: 1.4;
        }

        .feature-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .feature-list li {
            font-size: 7pt;
            color: #5c4a32;
            line-height: 1.35;
            margin-bottom: 2mm;
        }

        .feature-bullet {
            color: #c9a227;
            font-weight: bold;
        }

        .qr-section {
            text-align: center;
            margin-top: 4mm;
        }

        .qr-wrap {
            text-align: center;
            margin: 4mm auto;
            padding: 2mm;
            background: #ffffff;
            border: 1px solid #c9a227;
            display: inline-block;
        }

        .qr {
            width: 110px;
            height: 110px;
            display: block;
        }

        .referral-link {
            font-size: 6.5pt;
            font-family: DejaVu Sans Mono, monospace;
            color: #1a1208;
            word-break: break-all;
            text-align: center;
            line-height: 1.3;
            margin-top: 2mm;
        }

        .referral-note {
            font-size: 7pt;
            color: #a8841a;
            text-align: center;
            margin-top: 2mm;
        }

        .site-url {
            font-size: 9pt;
            font-weight: bold;
            color: #c9a227;
            text-align: center;
        }

        .cover-title {
            font-size: 14pt;
            font-weight: bold;
            color: #faf6ee;
            text-align: center;
            line-height: 1.25;
            margin-bottom: 4mm;
        }

        .cover-subtitle {
            font-size: 8pt;
            color: #d4c4a8;
            text-align: center;
            line-height: 1.45;
            margin-bottom: 8mm;
        }

        .cover-url {
            font-size: 10pt;
            font-weight: bold;
            color: #c9a227;
            text-align: center;
            margin-top: 10mm;
        }

        .interaction-block {
            margin-top: 5mm;
            padding-top: 4mm;
            border-top: 1px solid #e8dcc4;
        }

        .interaction-title {
            font-size: 9pt;
            font-weight: bold;
            color: #1a1208;
            margin-bottom: 2mm;
        }

        .interaction-subtitle {
            font-size: 7pt;
            color: #5c4a32;
            line-height: 1.35;
        }
    </style>
</head>
<body>
    {{-- Page 1: Outside spread (inside flap | back cover | front cover) --}}
    <div class="page">
        <div class="cover-bg"></div>
        <div class="divider divider-1"></div>
        <div class="divider divider-2"></div>
        <table class="spread">
            <tr>
                <td>
                    <h2 class="panel-title">{{ __('landing.contact_title') }}</h2>
                    <p class="panel-subtitle">{{ __('landing.contact_subtitle') }}</p>
                    <p class="body-text">{{ __('referrals.qr_pdf_instructions') }}</p>
                    <p class="site-url" style="margin-bottom: 4mm;">{{ $siteUrl }}</p>

                    <div class="qr-section">
                        <div class="qr-wrap">
                            <img src="{{ $qrDataUri }}" alt="QR code" class="qr">
                        </div>
                        <p class="referral-link">{{ $referralLink }}</p>
                        <p class="referral-note">{{ __('referrals.qr_pdf_footer') }}</p>
                    </div>
                </td>
                <td>
                    <h2 class="panel-title">{{ __('referrals.qr_pdf_heading') }}</h2>
                    <p class="panel-subtitle">{{ __('referrals.qr_pdf_instructions') }}</p>
                    <p class="site-url" style="margin-bottom: 4mm;">{{ $siteUrl }}</p>

                    <div class="qr-section">
                        <div class="qr-wrap">
                            <img src="{{ $qrDataUri }}" alt="QR code" class="qr">
                        </div>
                        <p class="referral-link">{{ $referralLink }}</p>
                        <p class="referral-note">{{ __('referrals.qr_pdf_footer') }}</p>
                    </div>
                </td>
                <td style="color: #faf6ee; vertical-align: middle; padding-top: 25mm; text-align: center;">
                    <img src="{{ $logoDataUri }}" alt="NasDan" class="logo">
                    <h1 class="cover-title">{{ __('landing.hero_title') }}</h1>
                    <p class="cover-subtitle">{{ __('landing.meta_description') }}</p>
                    <p class="cover-url">{{ $siteUrl }}</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- Page 2: Inside spread (features | how it works | guest experience) --}}
    <div class="page page-2">
        <div class="divider divider-1"></div>
        <div class="divider divider-2"></div>
        <table class="spread">
            <tr>
                <td>
                    <h2 class="panel-title">{{ __('landing.pricing_features_title') }}</h2>
                    <ul class="feature-list">
                        @foreach ($features as $feature)
                            <li><span class="feature-bullet">·</span> {{ $feature }}</li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    <div class="accent-bar"></div>
                    <h2 class="panel-title">{{ __('landing.steps_title') }}</h2>
                    <p class="panel-subtitle">{{ __('landing.steps_subtitle') }}</p>

                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-title">{{ __('landing.step_1_title') }}</div>
                        <div class="step-text">{{ __('landing.step_1_text') }}</div>
                    </div>

                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-title">{{ __('landing.step_2_title') }}</div>
                        <div class="step-text">{{ __('landing.step_2_text') }}</div>
                    </div>

                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-title">{{ __('landing.step_3_title') }}</div>
                        <div class="step-text">{{ __('landing.step_3_text') }}</div>
                    </div>
                </td>
                <td>
                    <h2 class="panel-title">{{ __('landing.benefits_title') }}</h2>
                    <p class="panel-subtitle">{{ __('landing.benefits_subtitle') }}</p>

                    <div class="benefit">
                        <div class="benefit-title">{{ __('landing.benefit_1_title') }}</div>
                        <div class="benefit-text">{{ __('landing.benefit_1_text') }}</div>
                    </div>

                    <div class="benefit">
                        <div class="benefit-title">{{ __('landing.benefit_2_title') }}</div>
                        <div class="benefit-text">{{ __('landing.benefit_2_text') }}</div>
                    </div>

                    <div class="benefit">
                        <div class="benefit-title">{{ __('landing.benefit_3_title') }}</div>
                        <div class="benefit-text">{{ __('landing.benefit_3_text') }}</div>
                    </div>

                    <div class="interaction-block">
                        <div class="interaction-title">{{ __('landing.interaction_title') }}</div>
                        <div class="interaction-subtitle">{{ __('landing.interaction_subtitle') }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
