<?php

namespace Matthewbdaly\LaravelAzureStorage;

use Illuminate\Filesystem\Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory as MemoryStore;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

/**
 * Service provider for Azure Blob Storage
 */
final class AzureStorageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('azure', function ($app, $config) {
            $client = $app->make(BlobRestProxy::class, $config);
            $adapter = new AzureBlobStorageAdapter(
                $client,
                $config['container'],
                $config['key'] ?? null,
                $config['url'] ?? null,
                $config['prefix'] ?? null
            );

            $cache = Arr::pull($config, 'cache');
            if ($cache) {
                $adapter = new CachedAdapter($adapter, $this->createCacheStore($cache));
            }

            return new Filesystem($adapter, $config);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(BlobRestProxy::class, function ($app, $config) {
            $config = empty($config) ? $app->make('config')->get('filesystems.disks.azure') : $config;

            if (array_key_exists('sasToken', $config)) {
                $endpoint = sprintf(
                    'BlobEndpoint=%s;SharedAccessSignature=%s;',
                    $config['endpoint'],
                    $config['sasToken']
                );
            } else {
                $endpoint = sprintf(
                    'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;',
                    $config['name'],
                    $config['key']
                );
                if (isset($config['endpoint'])) {
                    $endpoint .= sprintf("BlobEndpoint=%s;", $config['endpoint']);
                }
            }

            return BlobRestProxy::createBlobService($endpoint);
        });
    }

    /**
     * Create a cache store instance.
     *
     * @param  mixed  $config
     * @return \League\Flysystem\Cached\CacheInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function createCacheStore($config)
    {
        if ($config === true) {
            return new MemoryStore;
        }

        return new Cache(
            $this->app['cache']->store($config['store']),
            $config['prefix'] ?? 'flysystem',
            $config['expire'] ?? null
        );
    }
}
