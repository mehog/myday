<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('seating.export_pdf') }} — {{ $weddingEvent->couple_names }}</title>
    <style>
        @page {
            margin: 12mm;
            size: A4 portrait;
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

        .cover {
            text-align: center;
            padding-top: 20mm;
            page-break-after: always;
        }

        .cover-logo {
            height: 16mm;
            margin-bottom: 6mm;
        }

        .cover h1 {
            margin: 0 0 4mm;
            font-size: 18px;
            font-weight: bold;
        }

        .cover-couple {
            margin: 0 0 2mm;
            font-size: 14px;
            font-weight: bold;
        }

        .cover-date {
            margin: 0 0 8mm;
            font-size: 12px;
            color: #6b7280;
        }

        .stats-grid {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 8mm;
            flex-wrap: wrap;
        }

        .stat-box {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 16px;
            min-width: 40mm;
            text-align: center;
        }

        .stat-value {
            font-size: 16px;
            font-weight: bold;
        }

        .stat-label {
            margin-top: 2px;
            font-size: 9px;
            color: #6b7280;
        }

        .cover-footer {
            margin-top: 16mm;
            font-size: 9px;
            color: #9ca3af;
        }

        .tables-section {
            columns: 2;
            column-gap: 8mm;
        }

        .table-card {
            break-inside: avoid;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 6mm;
        }

        .table-card-header {
            background: #f9fafb;
            padding: 6px 10px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
        }

        .table-card-header span:last-child {
            float: right;
            font-weight: normal;
            color: #6b7280;
        }

        .table-card-row {
            padding: 3px 10px;
            font-size: 10px;
            border-top: 1px solid #f3f4f6;
        }

        .table-card-empty {
            color: #9ca3af;
        }

        .unassigned-section {
            break-before: column;
        }

        .canvas-page {
            page-break-before: always;
        }

        .canvas-image {
            width: 100%;
            max-height: 273mm;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="cover">
        <img src="{{ $logoDataUri }}" alt="" class="cover-logo">

        <h1>{{ __('seating.pdf_heading') }}</h1>

        <p class="cover-couple">{{ $weddingEvent->couple_names }}</p>

        <p class="cover-date">{{ $weddingEvent->wedding_date->translatedFormat('j. F Y.') }}</p>

        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-value">{{ $totalPeople }}</div>
                <div class="stat-label">{{ __('seating.pdf_total_people') }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $totalAssigned }}</div>
                <div class="stat-label">{{ __('seating.assigned') }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $totalPeople - $totalAssigned }}</div>
                <div class="stat-label">{{ __('seating.unassigned') }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $totalSeats }}</div>
                <div class="stat-label">{{ __('seating.pdf_total_seats') }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $tables->count() }}</div>
                <div class="stat-label">{{ __('seating.pdf_tables') }}</div>
            </div>
        </div>

        <p class="cover-footer">{{ __('seating.pdf_generated') }} {{ $generatedAt }}</p>
    </div>

    <div class="tables-section">
        @foreach ($tables as $table)
            <div class="table-card">
                <div class="table-card-header">
                    <span>{{ $table['label'] }}</span>
                    <span>{{ count($table['guests']) }} / {{ $table['chair_count'] }}</span>
                </div>

                @forelse ($table['guests'] as $index => $name)
                    <div class="table-card-row">{{ $index + 1 }}. {{ $name }}</div>
                @empty
                    <div class="table-card-row table-card-empty">{{ __('seating.pdf_no_guests') }}</div>
                @endforelse
            </div>
        @endforeach
    </div>

    @if (count($unassigned) > 0)
        <div class="unassigned-section">
            <div class="table-card">
                <div class="table-card-header">
                    <span>{{ __('seating.pdf_unassigned_guests') }}</span>
                    <span>{{ count($unassigned) }}</span>
                </div>

                @foreach ($unassigned as $index => $name)
                    <div class="table-card-row">{{ $index + 1 }}. {{ $name }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="canvas-page">
        <img src="{{ $imageDataUri }}" alt="{{ __('seating.pdf_title') }}" class="canvas-image">
    </div>
</body>
</html>
