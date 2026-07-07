<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Facades\Pdf;

class DownloadPlaceCardsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $weddingEvent = $user->weddingEvent;

        abort_unless($weddingEvent instanceof WeddingEvent, 404);

        $guests = $weddingEvent->guests()
            ->where('rsvp_status', RsvpStatus::Yes)
            ->orderBy('name')
            ->get();

        abort_if($guests->isEmpty(), 404);

        $colors = $this->resolveColors($weddingEvent, $request);

        $cards = $guests->map(function (Guest $guest) use ($weddingEvent): array {
            $contactUrl = route('invitation.contact.guest', [
                $weddingEvent->slug,
                $guest->token,
                'qr-code' => 'true',
            ]);

            return [
                'name' => Str::limit($guest->name, 25),
                'plus_one' => filled($guest->plus_one_name) ? Str::limit($guest->plus_one_name, 40) : null,
                'qr' => $this->qrDataUri($contactUrl),
            ];
        })->all();

        $pdf = Pdf::view('pdf.place-cards', [
            'cards' => $cards,
            'colors' => $colors,
            'weddingEvent' => $weddingEvent,
        ])
            ->driver('dompdf')
            ->format('a4')
            ->landscape()
            ->name('place-cards.pdf');

        return $pdf->toResponse($request);
    }

    /**
     * @return array{bg: string, text: string, accent: string}
     */
    private function resolveColors(WeddingEvent $weddingEvent, Request $request): array
    {
        $defaults = $weddingEvent->theme->placeCardColors();

        return [
            'bg' => $this->validHex($request->query('bg')) ?? $defaults['bg'],
            'text' => $this->validHex($request->query('text')) ?? $defaults['text'],
            'accent' => $this->validHex($request->query('accent')) ?? $defaults['accent'],
        ];
    }

    private function validHex(mixed $color): ?string
    {
        if (! is_string($color)) {
            return null;
        }

        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color) === 1) {
            return $color;
        }

        return null;
    }

    private function qrDataUri(string $url): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'eccLevel' => EccLevel::H,
            'scale' => 6,
            'outputBase64' => true,
            'quietzoneSize' => 1,
            'drawLightModules' => true,
            'imageTransparent' => false,
            'bgColor' => [255, 255, 255],
        ]);

        /** @var string $dataUri */
        $dataUri = (new QRCode($options))->render($url);

        return $dataUri;
    }
}
