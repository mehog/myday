<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('seating.export_pdf') }} — {{ $weddingEvent->couple_names }}</title>
    <style>
        @page {
            margin: 12mm;
            size: A4 landscape;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            background: #ffffff;
        }

        .canvas-image {
            width: 100%;
            max-height: 186mm;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <img src="{{ $imageDataUri }}" alt="{{ __('seating.pdf_title') }}" class="canvas-image">
</body>
</html>
