<?php

namespace Matthewbdaly\LaravelAzureStorage;

use Illuminate\Contracts\Container\Container;
use Illuminate\Filesystem\Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Cached\CacheInterface;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory as MemoryStore;
use League\Flysystem\Filesystem;
use Matthewbdaly\LaravelAzureStorage\Exceptions\CacheAdapterNotInstalled;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Middlewares\RetryMiddleware;
use MicrosoftAzure\Storage\Common\Middlewares\RetryMiddlewareFactory;

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
        Storage::extend('azure', function (Container $app, array $config) {
            $client = $app->make(BlobRestProxy::class, $config);
            $adapter = new AzureBlobStorageAdapter(
                $client,
                $config['container'],
                $config['key'] ?? null,
                $config['url'] ?? null,
                $config['prefix'] ?? null
            );

            if ($cache = Arr::pull($config, 'cache')) {
                if (!class_exists(CachedAdapter::class)) {
                    throw new CacheAdapterNotInstalled("Caching requires the league/flysystem-cached-adapter to be installed.");
                }

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
        $this->app->bind(BlobRestProxy::class, function (Container $app, array $config) {
            $config = empty($config) ? $app->make('config')->get('filesystems.disks.azure') : $config;

            if (!empty($config['connection_string'])) {
                $endpoint = $config['connection_string'];
            } else {
                $endpoint = $this->createConnectionString($config);
            }


            $blobOptions = [];
            $retry = data_get($config, 'retry');
            if (isset($retry)) {
                $blobOptions = [
                    'middlewares' => [
                        $this->createRetryMiddleware($retry)
                    ]
                ];
            }

            return BlobRestProxy::createBlobService($endpoint, $blobOptions);
        });
    }

    /**
     * Create a cache store instance.
     *
     * @param  mixed  $config
     *
     * @return \League\Flysystem\Cached\CacheInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function createCacheStore($config): CacheInterface
    {
        if ($config === true) {
            return new MemoryStore();
        }

        return new Cache(
            $this->app['cache']->store($config['store']),
            $config['prefix'] ?? 'flysystem',
            $config['expire'] ?? null
        );
    }

    /**
     * Create retry middleware instance.
     *
     * @param  array $config
     *
     * @return RetryMiddleware
     */
    protected function createRetryMiddleware(array $config): RetryMiddleware
    {
        return RetryMiddlewareFactory::create(
            RetryMiddlewareFactory::GENERAL_RETRY_TYPE,
            $config['tries'] ?? 3,
            $config['interval'] ?? 1000,
            $config['increase'] === 'exponential' ?
                RetryMiddlewareFactory::EXPONENTIAL_INTERVAL_ACCUMULATION :
                RetryMiddlewareFactory::LINEAR_INTERVAL_ACCUMULATION,
            true  // Whether to retry connection failures too, default false
        );
    }

    protected function createConnectionString(array $config): string
    {
        if (array_key_exists('sasToken', $config)) {
            return sprintf(
                'BlobEndpoint=%s;SharedAccessSignature=%s;',
                $config['endpoint'],
                $config['sasToken']
            );
        }
        $endpoint = sprintf(
            'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;',
            $config['name'],
            $config['key']
        );
        if (isset($config['endpoint'])) {
            $endpoint .= sprintf("BlobEndpoint=%s;", $config['endpoint']);
        }

        return $endpoint;
    }
}
