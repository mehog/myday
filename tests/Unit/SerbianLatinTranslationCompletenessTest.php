<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SerbianLatinTranslationCompletenessTest extends TestCase
{
    public function test_serbian_latin_lang_files_match_english_keys_and_placeholders(): void
    {
        $enDir = lang_path('en');
        $srDir = lang_path('sr_Latn');

        $this->assertDirectoryExists($srDir);

        $enFiles = collect(File::files($enDir))
            ->filter(fn (\SplFileInfo $file): bool => $file->getExtension() === 'php')
            ->mapWithKeys(fn (\SplFileInfo $file): array => [
                $file->getFilename() => $this->flattenTranslationKeys(require $file->getPathname()),
            ]);

        foreach ($enFiles as $filename => $enKeys) {
            $srPath = $srDir.DIRECTORY_SEPARATOR.$filename;
            $this->assertFileExists($srPath, "Missing Serbian Latin translation file: {$filename}");

            $srKeys = $this->flattenTranslationKeys(require $srPath);

            $missing = array_diff(array_keys($enKeys), array_keys($srKeys));
            $extra = array_diff(array_keys($srKeys), array_keys($enKeys));

            $this->assertSame([], $missing, "Missing keys in lang/sr_Latn/{$filename}: ".implode(', ', $missing));
            $this->assertSame([], $extra, "Unexpected keys in lang/sr_Latn/{$filename}: ".implode(', ', $extra));

            foreach ($enKeys as $key => $enValue) {
                if (! is_string($enValue) || ! is_string($srKeys[$key] ?? null)) {
                    continue;
                }

                $this->assertSame(
                    $this->extractPlaceholders($enValue),
                    $this->extractPlaceholders($srKeys[$key]),
                    "Placeholder mismatch in lang/sr_Latn/{$filename} [{$key}]",
                );
            }
        }
    }

    public function test_serbian_latin_support_bubble_translations_exist(): void
    {
        $en = require lang_path('vendor/support-bubble/en/support-bubble.php');
        $srPath = lang_path('vendor/support-bubble/sr_Latn/support-bubble.php');

        $this->assertFileExists($srPath);
        $sr = require $srPath;

        $this->assertSame(
            array_keys($this->flattenTranslationKeys($en)),
            array_keys($this->flattenTranslationKeys($sr)),
        );
    }

    /**
     * @param  array<string, mixed>  $translations
     * @return array<string, mixed>
     */
    private function flattenTranslationKeys(array $translations, string $prefix = ''): array
    {
        $flat = [];

        foreach ($translations as $key => $value) {
            $fullKey = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value)) {
                $flat += $this->flattenTranslationKeys($value, $fullKey);

                continue;
            }

            $flat[$fullKey] = $value;
        }

        return $flat;
    }

    /**
     * @return list<string>
     */
    private function extractPlaceholders(string $value): array
    {
        preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*|\{[A-Za-z_][A-Za-z0-9_]*\}/', $value, $matches);

        $placeholders = $matches[0] ?? [];
        sort($placeholders);

        return $placeholders;
    }
}
