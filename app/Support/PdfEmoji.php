<?php

namespace App\Support;

class PdfEmoji
{
    private const TWEMOJI_VERSION = '14.0.2';

    private const CDN_BASE = 'https://cdn.jsdelivr.net/gh/twitter/twemoji@'.self::TWEMOJI_VERSION.'/assets/72x72';

    private const FETCH_TIMEOUT_SECONDS = 3;

    /** @var array<string, string> */
    private static array $dataUriCache = [];

    public static function toHtml(string $text, string $size = '1em'): string
    {
        if ($text === '') {
            return '';
        }

        preg_match_all('/\X/u', $text, $matches);

        $html = '';

        foreach ($matches[0] as $grapheme) {
            if (self::isEmojiGrapheme($grapheme)) {
                $html .= self::emojiToImage($grapheme, $size);
            } else {
                $html .= e($grapheme);
            }
        }

        return $html;
    }

    private static function isEmojiGrapheme(string $grapheme): bool
    {
        if (preg_match('/\p{Extended_Pictographic}/u', $grapheme) === 1) {
            return true;
        }

        if (preg_match('/\p{Regional_Indicator}/u', $grapheme) === 1) {
            return true;
        }

        return preg_match('/^(?:\x{FE0F}|\x{200D}|\x{1F3FB}-\x{1F3FF})+$/u', $grapheme) === 1;
    }

    private static function emojiToImage(string $emoji, string $size): string
    {
        $dataUri = self::resolveDataUri($emoji);

        if ($dataUri === null) {
            return e($emoji);
        }

        return sprintf(
            '<img src="%s" alt="" class="pdf-emoji" style="height:%s;width:%s;vertical-align:-0.15em;" />',
            e($dataUri),
            e($size),
            e($size),
        );
    }

    private static function resolveDataUri(string $emoji): ?string
    {
        foreach (self::twemojiHexCandidates($emoji) as $hex) {
            if (isset(self::$dataUriCache[$hex])) {
                return self::$dataUriCache[$hex];
            }

            $contents = self::readCachedPng($hex) ?? self::fetchAndCachePng($hex);

            if ($contents === null) {
                continue;
            }

            self::$dataUriCache[$hex] = 'data:image/png;base64,'.base64_encode($contents);

            return self::$dataUriCache[$hex];
        }

        return null;
    }

    private static function readCachedPng(string $hex): ?string
    {
        $path = self::pngPath($hex);

        if (! is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        return $contents;
    }

    private static function fetchAndCachePng(string $hex): ?string
    {
        $url = self::CDN_BASE.'/'.$hex.'.png';
        $context = stream_context_create([
            'http' => [
                'timeout' => self::FETCH_TIMEOUT_SECONDS,
                'ignore_errors' => true,
            ],
        ]);

        $contents = @file_get_contents($url, false, $context);

        if ($contents === false || $contents === '') {
            return null;
        }

        if (! self::isSuccessfulHttpResponse($http_response_header ?? [])) {
            return null;
        }

        if (! self::writePngAtomically($hex, $contents)) {
            return $contents;
        }

        return $contents;
    }

    /**
     * @param  list<string>  $headers
     */
    private static function isSuccessfulHttpResponse(array $headers): bool
    {
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\d(?:\.\d)?\s+(\d{3})/', $header, $matches) === 1) {
                $status = (int) $matches[1];

                return $status >= 200 && $status < 300;
            }
        }

        return false;
    }

    private static function writePngAtomically(string $hex, string $contents): bool
    {
        $path = self::pngPath($hex);
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            return false;
        }

        $temporaryPath = $path.'.'.bin2hex(random_bytes(8)).'.tmp';
        $written = file_put_contents($temporaryPath, $contents, LOCK_EX);

        if ($written === false) {
            @unlink($temporaryPath);

            return false;
        }

        if (! rename($temporaryPath, $path)) {
            @unlink($temporaryPath);

            return false;
        }

        return true;
    }

    private static function pngPath(string $hex): string
    {
        return storage_path("emoji/twemoji/72x72/{$hex}.png");
    }

    /**
     * @return list<string>
     */
    private static function twemojiHexCandidates(string $emoji): array
    {
        $codepoints = [];

        $length = mb_strlen($emoji, 'UTF-8');

        for ($i = 0; $i < $length; $i++) {
            $codepoints[] = mb_ord(mb_substr($emoji, $i, 1, 'UTF-8'), 'UTF-8');
        }

        $full = self::codepointsToHex($codepoints);
        $withoutVariationSelectors = self::codepointsToHex(
            array_values(array_filter($codepoints, fn (int $codepoint): bool => $codepoint !== 0xFE0F)),
        );

        return array_values(array_unique(array_filter([
            $full,
            $withoutVariationSelectors !== $full ? $withoutVariationSelectors : null,
        ])));
    }

    /**
     * @param  list<int>  $codepoints
     */
    private static function codepointsToHex(array $codepoints): string
    {
        return implode('-', array_map(
            static fn (int $codepoint): string => strtolower(dechex($codepoint)),
            $codepoints,
        ));
    }
}
