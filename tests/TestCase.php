<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
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
}
