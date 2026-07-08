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

        $filename = 'raspored-sjedenja-'.$wedding->slug.'.pdf';

        return Pdf::view('pdf.seating-plan', [
            'weddingEvent' => $wedding,
            'imageDataUri' => $imageDataUri,
        ])
            ->driver('dompdf')
            ->format('a4')
            ->landscape()
            ->name($filename)
            ->toResponse($request);
    }
}
