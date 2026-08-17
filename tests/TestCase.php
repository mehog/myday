<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\RefreshInMemoryDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshInMemoryDatabase;

    protected function setUpTraits()
    {
        if (! app()->environment('testing')) {
            $this->fail('Tests must run with APP_ENV=testing.');
        }

        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            $this->fail('Tests must use the sqlite :memory: connection from phpunit.xml.');
        }

        return parent::setUpTraits();
    }

    protected function fakeVisitorCountry(string $countryCode, string $ip = '203.0.113.10'): void
    {
        Config::set('services.ipstack.access_key', 'test-access-key');

        Http::fake([
            'api.ipstack.com/*' => Http::response([
                'ip' => $ip,
                'country_code' => $countryCode,
            ]),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => $ip]);
        $this->app->instance('request', Request::create('/', 'GET', server: ['REMOTE_ADDR' => $ip]));
    }
}
