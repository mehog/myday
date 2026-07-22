<?php

namespace Tests\Concerns;

use LogicException;

trait RefreshInMemoryDatabase
{
    protected function setUpRefreshInMemoryDatabase(): void
    {
        if (
            ! app()->environment('testing')
            || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:'
        ) {
            throw new LogicException('Database-refreshing tests require the sqlite :memory: connection.');
        }

        $this->artisan('migrate:fresh');
    }
}
