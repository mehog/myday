<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CroatianTranslationCompletenessTest extends TestCase
{
    public function test_croatian_lang_files_match_english_keys_and_placeholders(): void
    {
        $enDir = lang_path('en');
        $hrDir = lang_path('hr');

        $this->assertDirectoryExists($hrDir);

        $enFiles = collect(File::files($enDir))
            ->filter(fn (\SplFileInfo $file): bool => $file->getExtension() === 'php')
            ->mapWithKeys(fn (\SplFileInfo $file): array => [
                $file->getFilename() => $this->flattenTranslationKeys(require $file->getPathname()),
            ]);

        foreach ($enFiles as $filename => $enKeys) {
            $hrPath = $hrDir.DIRECTORY_SEPARATOR.$filename;
            $this->assertFileExists($hrPath, "Missing Croatian translation file: {$filename}");

            $hrKeys = $this->flattenTranslationKeys(require $hrPath);

            $missing = array_diff(array_keys($enKeys), array_keys($hrKeys));
            $extra = array_diff(array_keys($hrKeys), array_keys($enKeys));

            $this->assertSame([], $missing, "Missing keys in lang/hr/{$filename}: ".implode(', ', $missing));
            $this->assertSame([], $extra, "Unexpected keys in lang/hr/{$filename}: ".implode(', ', $extra));

            foreach ($enKeys as $key => $enValue) {
                if (! is_string($enValue) || ! is_string($hrKeys[$key] ?? null)) {
                    continue;
                }

                $this->assertSame(
                    $this->extractPlaceholders($enValue),
                    $this->extractPlaceholders($hrKeys[$key]),
                    "Placeholder mismatch in lang/hr/{$filename} [{$key}]",
                );
            }
        }
    }

    public function test_croatian_support_bubble_translations_exist(): void
    {
        $en = require lang_path('vendor/support-bubble/en/support-bubble.php');
        $hrPath = lang_path('vendor/support-bubble/hr/support-bubble.php');

        $this->assertFileExists($hrPath);
        $hr = require $hrPath;

        $this->assertSame(
            array_keys($this->flattenTranslationKeys($en)),
            array_keys($this->flattenTranslationKeys($hr)),
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
