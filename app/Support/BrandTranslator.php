<?php

namespace App\Support;

use Illuminate\Translation\Translator as BaseTranslator;

class BrandTranslator extends BaseTranslator
{
    /**
     * @param  array<string, mixed>  $replace
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true): mixed
    {
        return parent::get($key, $this->withBrand($replace), $locale, $fallback);
    }

    /**
     * @param  \Countable|int|float|array<mixed>  $number
     * @param  array<string, mixed>  $replace
     */
    public function choice($key, $number, array $replace = [], $locale = null): string
    {
        return parent::choice($key, $number, $this->withBrand($replace), $locale);
    }

    /**
     * @param  array<string, mixed>  $replace
     * @return array<string, mixed>
     */
    private function withBrand(array $replace): array
    {
        if (! array_key_exists('brand', $replace)) {
            $replace['brand'] = (string) config('app.name', 'Nuptoria');
        }

        return $replace;
    }
}
