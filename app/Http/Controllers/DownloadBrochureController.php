<?php

namespace App\Http\Controllers;

use App\Models\User;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Response;
use Spatie\LaravelPdf\Facades\Pdf;

class DownloadBrochureController extends Controller
{
    public function __invoke(): Response
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user instanceof User, 403);
        abort_unless($user->hasReferralAccount(), 403);

        $referralLink = $user->getReferralLink();

        abort_if($referralLink === '', 403);

        $pdf = Pdf::view('pdf.brochure', [
            'logoDataUri' => $this->logoDataUri(),
            'qrDataUri' => $this->qrDataUri($referralLink),
            'dotPatternUri' => $this->dotPatternUri(),
            'referralLink' => $referralLink,
            'siteUrl' => parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url'),
            'features' => collect(range(1, 14))
                ->map(fn (int $index): string => (string) __('landing.pricing_feature_'.$index))
                ->all(),
        ])
            ->driver('dompdf')
            ->format('a4')
            ->landscape()
            ->name('nasdan-brochure.pdf');

        return $pdf->toResponse(request());
    }

    private function qrDataUri(string $referralLink): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'eccLevel' => EccLevel::H,
            'scale' => 10,
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

    private function dotPatternUri(): string
    {
        $size = 32;
        $image = imagecreatetruecolor($size, $size);

        if ($image === false) {
            return '';
        }

        imagesavealpha($image, true);
        imagealphablending($image, false);

        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);

        imagealphablending($image, true);
        $gold = imagecolorallocatealpha($image, 201, 162, 39, 83);
        imagefilledellipse($image, (int) ($size / 2), (int) ($size / 2), 4, 4, $gold);

        ob_start();
        imagepng($image);
        $png = ob_get_clean() ?: '';
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($png);
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

        abort_unless(is_file($pngPath), 500, 'Brochure logo asset is missing.');

        $contents = file_get_contents($pngPath);

        abort_if($contents === false, 500, 'Brochure logo asset could not be read.');

        return 'data:image/png;base64,'.base64_encode($contents);
    }
}
