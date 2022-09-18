<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Config\Repository;
use Matthewbdaly\LaravelAzureStorage\AzureStorageServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [AzureStorageServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        // Setup default database to use sqlite :memory:
        assert($app['config'] instanceof Repository);
        $app['config']->set('filesystems.default', 'azure');
        $app['config']->set('filesystems.cloud', 'azure');
        $app['config']->set('filesystems.disks.azure', [
            'driver'    => 'azure',
            'name'      => 'MY_AZURE_STORAGE_NAME',
            'key'       => base64_encode('MY_AZURE_STORAGE_KEY'),
            'endpoint'  => null,
            'container' => 'MY_AZURE_STORAGE_CONTAINER',
            'prefix'    => null,
            'cache' => [
                'store' => 'file',
                'expire' => 60,
                'prefix' => 'filecache',
            ],
        ]);
    }
}
