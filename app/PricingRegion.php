<?php

namespace App;

use App\Helpers\IpStackCacheHelper;
use Illuminate\Http\Request;

enum PricingRegion: string
{
    case FirstWorld = 'first_world';
    case ThirdWorld = 'third_world';

    /**
     * Countries billed in BAM. Everyone else is EUR.
     *
     * @var list<string>
     */
    public const BAM_COUNTRIES = ['BA'];

    public function currency(): string
    {
        return (string) config('dodo.regions.'.$this->value.'.currency');
    }

    public function label(): string
    {
        return match ($this) {
            self::FirstWorld => 'EUR (international)',
            self::ThirdWorld => 'BAM (Bosnia)',
        };
    }

    public function priceFor(PlanTier $tier): int
    {
        return (int) config('dodo.regions.'.$this->value.'.prices.'.$tier->value);
    }

    public static function fromCountryCode(?string $code): self
    {
        $normalized = is_string($code) && $code !== '' ? strtoupper($code) : null;

        return $normalized !== null && in_array($normalized, self::BAM_COUNTRIES, true)
            ? self::ThirdWorld
            : self::FirstWorld;
    }

    public static function forVisitor(?Request $request = null): self
    {
        $request ??= request();
        $ip = $request->ip();

        if (! is_string($ip) || $ip === '' || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return self::FirstWorld;
        }

        if (! config('services.ipstack.access_key')) {
            return self::FirstWorld;
        }

        $data = IpStackCacheHelper::getOrFetch($ip);

        return self::fromCountryCode(self::countryCodeFrom($data));
    }

    private static function countryCodeFrom(mixed $data): ?string
    {
        if ($data instanceof \stdClass) {
            $code = $data->country_code ?? null;

            return is_string($code) ? $code : null;
        }

        if (is_array($data)) {
            $code = $data['country_code'] ?? null;

            return is_string($code) ? $code : null;
        }

        return null;
    }
}
