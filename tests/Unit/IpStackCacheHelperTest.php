<?php

namespace Tests\Unit;

use App\Helpers\IpStackCacheHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IpStackCacheHelperTest extends TestCase
{
    public function test_caches_payload_as_array_and_returns_object(): void
    {
        $ip = '203.0.113.50';

        Http::fake([
            'api.ipstack.com/*' => Http::response([
                'ip' => $ip,
                'country_code' => 'BA',
            ]),
        ]);

        config()->set('services.ipstack.access_key', 'test-access-key');

        $data = IpStackCacheHelper::getOrFetch($ip);

        $this->assertIsObject($data);
        $this->assertSame('BA', $data->country_code);
        $this->assertIsArray(Cache::get('ipstack_data_'.$ip));
        $this->assertSame('BA', Cache::get('ipstack_data_'.$ip)['country_code']);
    }

    public function test_reads_legacy_array_cache_entries(): void
    {
        $ip = '203.0.113.51';
        Cache::put('ipstack_data_'.$ip, [
            'ip' => $ip,
            'country_code' => 'DE',
        ], 3600);

        $data = IpStackCacheHelper::getOrFetch($ip);

        $this->assertSame('DE', $data?->country_code);
        Http::assertNothingSent();
    }

    public function test_discards_incomplete_cached_objects_and_refetches(): void
    {
        $ip = '203.0.113.52';
        $incomplete = unserialize('O:14:"MissingIpClass":1:{s:12:"country_code";s:2:"BA";}');

        $this->assertIsObject($incomplete);
        $this->assertNotInstanceOf(\stdClass::class, $incomplete);

        Cache::put('ipstack_data_'.$ip, $incomplete, 3600);
        config()->set('services.ipstack.access_key', 'test-access-key');

        Http::fake([
            'api.ipstack.com/*' => Http::response([
                'ip' => $ip,
                'country_code' => 'BA',
            ]),
        ]);

        $data = IpStackCacheHelper::getOrFetch($ip);

        $this->assertInstanceOf(\stdClass::class, $data);
        $this->assertSame('BA', $data->country_code);
        $this->assertIsArray(Cache::get('ipstack_data_'.$ip));
        Http::assertSentCount(1);
    }
}
