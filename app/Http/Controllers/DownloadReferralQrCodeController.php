<?php

namespace App\Http\Controllers;

use App\Models\User;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Response;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

class DownloadReferralQrCodeController extends Controller
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private const FORMATS = [
        'a4' => [
            'type' => 'format',
            'value' => 'a4',
            'landscape' => false,
            'qrSize' => 280,
        ],
        'a5' => [
            'type' => 'format',
            'value' => 'a5',
            'landscape' => false,
            'qrSize' => 155,
        ],
        'letter' => [
            'type' => 'format',
            'value' => 'letter',
            'landscape' => false,
            'qrSize' => 280,
        ],
    ];

    public function __invoke(?string $format = null): Response
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user instanceof User, 403);
        abort_unless($user->hasReferralAccount(), 403);

        $formatKey = $this->resolveFormat($format);
        $formatConfig = self::FORMATS[$formatKey];
        $referralLink = $user->getReferralLink();

        abort_if($referralLink === '', 403);

        $pdf = $this->buildPdf($referralLink, $formatKey, $formatConfig);

        return $pdf->toResponse(request());
    }

    private function resolveFormat(?string $format): string
    {
        $format = strtolower((string) $format);

        if (array_key_exists($format, self::FORMATS)) {
            return $format;
        }

        return 'a4';
    }

    /**
     * @param  array<string, mixed>  $formatConfig
     */
    private function buildPdf(string $referralLink, string $formatKey, array $formatConfig): PdfBuilder
    {
        $pdf = Pdf::view('pdf.referral-qr-code', [
            'heading' => __('referrals.qr_pdf_heading'),
            'instructions' => __('referrals.qr_pdf_instructions'),
            'linkLabel' => __('referrals.qr_pdf_link_label'),
            'referralLink' => $referralLink,
            'footer' => __('referrals.qr_pdf_footer'),
            'siteUrl' => parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url'),
            'logoDataUri' => $this->logoDataUri(),
            'qrDataUri' => $this->qrDataUri($referralLink),
            'format' => $formatKey,
            'qrSize' => (int) $formatConfig['qrSize'],
        ])
            ->driver('dompdf')
            ->name('nasdan-referral-qr.pdf');

        if ($formatConfig['type'] === 'format') {
            $pdf->format((string) $formatConfig['value']);
        } else {
            $pdf->paperSize(
                (float) $formatConfig['width'],
                (float) $formatConfig['height'],
                'mm',
            );
        }

        if ($formatConfig['landscape']) {
            $pdf->landscape();
        } else {
            $pdf->portrait();
        }

        return $pdf;
    }

    private function qrDataUri(string $referralLink): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'eccLevel' => EccLevel::H,
            'scale' => 12,
            'outputBase64' => true,
            'quietzoneSize' => 2,
            'drawLightModules' => true,
            'imageTransparent' => false,
            'bgColor' => [255, 255, 255],
        ]);

        /** @var string $dataUri */
        $dataUri = (new QRCode($options))->render($referralLink);

        return $dataUri;
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

        abort_unless(is_file($pngPath), 500, 'Referral QR logo asset is missing.');

        $contents = file_get_contents($pngPath);

        abort_if($contents === false, 500, 'Referral QR logo asset could not be read.');

        return 'data:image/png;base64,'.base64_encode($contents);
    }
}
