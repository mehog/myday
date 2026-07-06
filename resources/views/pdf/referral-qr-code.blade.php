<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $heading }}</title>
    <style>
        @page {
            margin: 0;
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
            border: 4px solid #c9a227;
        }

        .page {
            padding: 10mm;
            text-align: center;
        }

        .logo {
            height: 16mm;
            margin: 0 auto 6mm;
        }

        .heading {
            font-size: 20pt;
            font-weight: bold;
            color: #1a1208;
            margin: 0 0 3mm;
            line-height: 1.2;
        }

        .instructions {
            font-size: 10pt;
            color: #5c4a32;
            margin: 0 auto 8mm;
            max-width: 140mm;
            line-height: 1.4;
        }

        .qr-wrap {
            margin: 0 auto 6mm;
            padding: 3mm;
            background: #ffffff;
            border: 2px solid #c9a227;
            display: inline-block;
        }

        .qr {
            width: {{ $qrSize }}px;
            height: {{ $qrSize }}px;
            display: block;
        }

        .link-label {
            font-size: 8pt;
            color: #5c4a32;
            margin: 0 0 1mm;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .link {
            font-size: 8pt;
            color: #1a1208;
            word-break: break-all;
            margin: 0 auto 0;
            max-width: 160mm;
            line-height: 1.3;
        }

        .footer {
            font-size: 7pt;
            color: #a8841a;
            margin-top: 5mm;
            line-height: 1.3;
        }

        .brand {
            font-weight: bold;
            color: #c9a227;
        }
    </style>
</head>
<body>
    <div class="page">
        <img src="{{ $logoDataUri }}" alt="NasDan" class="logo">

        <h1 class="heading">{{ $heading }}</h1>

        <p class="instructions">{{ $instructions }}</p>

        <div class="qr-wrap">
            <img src="{{ $qrDataUri }}" alt="QR code" class="qr">
        </div>

        <p class="link-label">{{ $linkLabel }}</p>
        <p class="link">{{ $referralLink }}</p>

        <p class="footer">
            <span class="brand">{{ $footer }}</span><br>
            {{ $siteUrl }}
        </p>
    </div>
</body>
</html>
