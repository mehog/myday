<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WeddingEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\LaravelPdf\Facades\Pdf;

class DownloadSeatingPlanPdfController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $wedding = $user->weddingEvent;

        abort_unless($wedding instanceof WeddingEvent, 404);

        $imageDataUri = session()->pull('seating_plan_pdf_image');

        abort_unless(is_string($imageDataUri), 422);

        $guests = $wedding->guests()->get(['id', 'name', 'plus_one_name']);
        $seatingPlan = $wedding->seating_plan ?? ['tables' => []];

        $nameMap = [
            'bride' => $wedding->bride_name,
            'groom' => $wedding->groom_name,
        ];

        foreach ($guests as $guest) {
            $nameMap[(string) $guest->id] = $guest->name;

            if (filled($guest->plus_one_name)) {
                $nameMap[(string) -$guest->id] = $guest->plus_one_name;
            }
        }

        $assignedIds = collect($seatingPlan['tables'] ?? [])
            ->flatMap(fn (array $table): array => $table['seats'] ?? [])
            ->filter()
            ->map(fn ($id): string => (string) $id)
            ->all();

        $tables = collect($seatingPlan['tables'] ?? [])->map(fn (array $table): array => [
            'label' => $table['label'] ?? '',
            'chair_count' => $table['chair_count'] ?? 0,
            'guests' => collect($table['seats'] ?? [])
                ->filter()
                ->map(fn ($id): string => $nameMap[(string) $id] ?? '?')
                ->values()
                ->all(),
        ]);

        $unassigned = collect($nameMap)
            ->filter(fn (string $name, string $id): bool => ! in_array($id, $assignedIds, true))
            ->values()
            ->all();

        $totalPeople = count($nameMap);
        $totalSeats = $tables->sum('chair_count');
        $totalAssigned = count($assignedIds);

        $filename = 'raspored-sjedenja-'.$wedding->slug.'.pdf';

        return Pdf::view('pdf.seating-plan', [
            'weddingEvent' => $wedding,
            'imageDataUri' => $imageDataUri,
            'logoDataUri' => $this->logoDataUri(),
            'tables' => $tables,
            'unassigned' => $unassigned,
            'totalPeople' => $totalPeople,
            'totalSeats' => $totalSeats,
            'totalAssigned' => $totalAssigned,
            'generatedAt' => now()->translatedFormat('j. F Y.'),
        ])
            ->driver('dompdf')
            ->format('a4')
            ->name($filename)
            ->toResponse($request);
    }

    private function logoDataUri(): string
    {
        $pngPath = public_path('icons/nd-logo-transparent.png');

        if (! is_file($pngPath)) {
            $webpPath = public_path('icons/nd-logo-transparent.webp');

            if (is_file($webpPath) && function_exists('imagecreatefromwebp')) {
                $image = imagecreatefromwebp($webpPath);

                if ($image !== false) {
                    imagepng($image, $pngPath);
                    imagedestroy($image);
                }
            }
        }

        abort_unless(is_file($pngPath), 500, 'Seating plan logo asset is missing.');

        $contents = file_get_contents($pngPath);

        abort_if($contents === false, 500, 'Seating plan logo asset could not be read.');

        return 'data:image/png;base64,'.base64_encode($contents);
    }
}
