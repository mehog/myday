<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpStackCacheHelper
{
    /**
     * Cache duration in seconds (1 hour)
     */
    const CACHE_DURATION = 3600;

    /**
     * Cache key prefix
     */
    const CACHE_PREFIX = 'ipstack_data_';

    /**
     * Get IPStack data from cache or fetch from API
     */
    public static function getOrFetch(string $ip): ?object
    {
        $cacheKey = self::CACHE_PREFIX.$ip;

        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            Log::info('✅ Using cached IPStack data', ['ip' => $ip]);

            return $cachedData;
        }

        $freshData = self::fetchFromApi($ip);
        if ($freshData) {
            Cache::put($cacheKey, $freshData, self::CACHE_DURATION);
            Log::info('✅ Cached fresh IPStack data', [
                'ip' => $ip,
                'country_code' => $freshData->country_code ?? 'unknown',
                'cache_duration' => self::CACHE_DURATION,
            ]);
        }

        return $freshData;
    }

    /**
     * Clear cache for a specific IP
     */
    public static function clearCache(string $ip): bool
    {
        $cacheKey = self::CACHE_PREFIX.$ip;
        $result = Cache::forget($cacheKey);

        if ($result) {
            Log::info('🗑️ Cleared IPStack cache', ['ip' => $ip]);
        }

        return $result;
    }

    /**
     * Check if IP data is cached
     */
    public static function isCached(string $ip): bool
    {
        $cacheKey = self::CACHE_PREFIX.$ip;

        return Cache::has($cacheKey);
    }

    /**
     * Get cached data without fetching from API
     */
    public static function getCached(string $ip): ?object
    {
        $cacheKey = self::CACHE_PREFIX.$ip;

        return Cache::get($cacheKey);
    }

    /**
     * Manually cache IPStack data
     */
    public static function cache(string $ip, object $data, ?int $duration = null): bool
    {
        $cacheKey = self::CACHE_PREFIX.$ip;
        $cacheDuration = $duration ?? self::CACHE_DURATION;

        $result = Cache::put($cacheKey, $data, $cacheDuration);

        if ($result) {
            Log::info('✅ Manually cached IPStack data', [
                'ip' => $ip,
                'duration' => $cacheDuration,
            ]);
        }

        return $result;
    }

    /**
     * Fetch IPStack data from API
     */
    private static function fetchFromApi(string $ip): ?object
    {
        try {
            $apiKey = config('services.ipstack.access_key');

            if (! $apiKey) {
                Log::warning('IPStack API key not configured');

                return null;
            }

            $response = Http::timeout(10)->get('https://api.ipstack.com/'.$ip.'?access_key='.$apiKey);

            if ($response->successful()) {
                $data = $response->object();

                if (! isset($data->ip) || $data->ip !== $ip) {
                    Log::warning('IPStack API returned invalid data', [
                        'ip' => $ip,
                        'response_ip' => $data->ip ?? 'missing',
                    ]);

                    return null;
                }

                Log::info('🌐 Fresh IPStack API call successful', [
                    'ip' => $ip,
                    'country_code' => $data->country_code ?? 'unknown',
                ]);

                return $data;
            }

            Log::error('IPStack API error', [
                'ip' => $ip,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('IPStack API exception', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
