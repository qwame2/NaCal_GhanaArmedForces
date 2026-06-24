<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        if (! $this->app) {
            $this->refreshApplication();
        }

        // Safety check to prevent running tests against the actual mysql database
        if (config('database.default') === 'mysql' || config('database.connections.mysql.database') === 'nacal_db') {
            // Programmatically clear the config cache immediately to self-heal the setup
            try {
                \Illuminate\Support\Facades\Artisan::call('config:clear');
            } catch (\Exception $e) {}

            throw new \RuntimeException(
                "CRITICAL ERROR: The test suite was about to run against the live MySQL database ('nacal_db'). " .
                "This usually happens when the configuration cache is active. " .
                "The configuration cache has been automatically cleared. Please re-run your tests."
            );
        }

        parent::setUp();
    }
}
