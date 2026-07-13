<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // RefreshDatabase (and similar) must never touch a real database.
        // phpunit.xml forces sqlite :memory:; abort if that config is overridden.
        if (! app()->environment('testing')) {
            $this->fail('Tests must run with APP_ENV=testing.');
        }

        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            $this->fail('Tests must use the sqlite :memory: connection from phpunit.xml.');
        }
    }
}
