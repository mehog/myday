<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('guests.place_cards_download') }} — {{ $weddingEvent->couple_names }}</title>
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
            color: {{ $colors['text'] }};
            background: #ffffff;
        }

        .page {
            width: 297mm;
            padding: 54.2mm 0;
        }

        .page + .page {
            page-break-before: always;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .grid td {
            width: 33.33%;
            height: 101.6mm;
            padding: 0;
            vertical-align: top;
            text-align: center;
        }

        .card {
            position: relative;
            display: block;
            width: 88.9mm;
            height: 101.6mm;
            margin: 0 auto;
            background-color: {{ $colors['bg'] }};
            color: {{ $colors['text'] }};
            overflow: hidden;
        }

        .card-back {
            position: absolute;
            top: 0;
            left: 0;
            width: 88.9mm;
            height: 50.8mm;
            text-align: center;
            padding-top: 18mm;
            transform: rotate(180deg);
        }

        .card-fold {
            position: absolute;
            top: 50.8mm;
            left: 0;
            width: 88.9mm;
            height: 0;
            border-top: 1px dashed {{ $colors['accent'] }};
        }

        .card-front {
            position: absolute;
            top: 50.8mm;
            left: 0;
            width: 88.9mm;
            height: 50.8mm;
            text-align: center;
            padding-top: 6mm;
        }

        .card-site-url {
            position: absolute;
            right: 3mm;
            bottom: 2mm;
            font-size: 6pt;
            line-height: 1;
            text-align: right;
            opacity: 0.65;
        }

        .card-qr {
            width: 16mm;
            height: 16mm;
            display: block;
            margin: 0 auto 2mm;
        }

        .guest-name {
            font-size: 14pt;
            font-weight: bold;
            line-height: 1.2;
            text-align: center;
        }

        .plus-one-name {
            margin-top: 1.5mm;
            font-size: 9pt;
            line-height: 1.2;
            text-align: center;
            opacity: 0.85;
        }

        .cut-mark {
            position: absolute;
            background: {{ $colors['accent'] }};
        }

        .cut-mark-top-left-h {
            top: 0;
            left: 0;
            width: 4mm;
            height: 0.3mm;
        }

        .cut-mark-top-left-v {
            top: 0;
            left: 0;
            width: 0.3mm;
            height: 4mm;
        }

        .cut-mark-top-right-h {
            top: 0;
            right: 0;
            width: 4mm;
            height: 0.3mm;
        }

        .cut-mark-top-right-v {
            top: 0;
            right: 0;
            width: 0.3mm;
            height: 4mm;
        }

        .cut-mark-bottom-left-h {
            bottom: 0;
            left: 0;
            width: 4mm;
            height: 0.3mm;
        }

        .cut-mark-bottom-left-v {
            bottom: 0;
            left: 0;
            width: 0.3mm;
            height: 4mm;
        }

        .cut-mark-bottom-right-h {
            bottom: 0;
            right: 0;
            width: 4mm;
            height: 0.3mm;
        }

        .cut-mark-bottom-right-v {
            bottom: 0;
            right: 0;
            width: 0.3mm;
            height: 4mm;
        }
    </style>
</head>
<body>
    @php
        $siteUrl = parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url');
    @endphp
    @foreach (collect($cards)->chunk(3) as $pageCards)
        <div class="page">
            <table class="grid">
                <tr>
                    @foreach ($pageCards as $card)
                        <td>
                            <div class="card">
                                <span class="cut-mark cut-mark-top-left-h"></span>
                                <span class="cut-mark cut-mark-top-left-v"></span>
                                <span class="cut-mark cut-mark-top-right-h"></span>
                                <span class="cut-mark cut-mark-top-right-v"></span>
                                <span class="cut-mark cut-mark-bottom-left-h"></span>
                                <span class="cut-mark cut-mark-bottom-left-v"></span>
                                <span class="cut-mark cut-mark-bottom-right-h"></span>
                                <span class="cut-mark cut-mark-bottom-right-v"></span>

                                <div class="card-back">
                                    <div class="guest-name">{{ $card['name'] }}</div>
                                    @if (! empty($card['plus_one']))
                                        <div class="plus-one-name">&amp; {{ $card['plus_one'] }}</div>
                                    @endif
                                </div>

                                <div class="card-fold"></div>

                                <div class="card-front">
                                    <img class="card-qr" src="{{ $card['qr'] }}" alt="">
                                    <div class="guest-name">{{ $card['name'] }}</div>
                                    @if (! empty($card['plus_one']))
                                        <div class="plus-one-name">&amp; {{ $card['plus_one'] }}</div>
                                    @endif
                                </div>

                                <div class="card-site-url">{{ $siteUrl }}</div>
                            </div>
                        </td>
                    @endforeach

                    @for ($i = $pageCards->count(); $i < 3; $i++)
                        <td></td>
                    @endfor
                </tr>
            </table>
        </div>
    @endforeach
</body>
</html>
