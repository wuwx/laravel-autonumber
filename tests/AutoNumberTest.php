<?php

namespace Wuwx\LaravelAutoNumber\Tests;

use Orchestra\Testbench\TestCase;
use Wuwx\LaravelAutoNumber\AutoNumberServiceProvider;

class AutoNumberTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            AutoNumberServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    public function test_service_provider_loads()
    {
        $this->artisan('migrate', ['--database' => 'testing'])->run();
        $this->assertTrue(true);
    }
}
