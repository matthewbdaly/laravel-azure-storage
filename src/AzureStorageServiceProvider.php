<?php

namespace Matthewbdaly\LaravelAzureStorage;

use Illuminate\Contracts\Container\Container;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Matthewbdaly\LaravelAzureStorage\Exceptions\EndpointNotSet;
use Matthewbdaly\LaravelAzureStorage\Exceptions\KeyNotSet;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Middlewares\RetryMiddleware;
use MicrosoftAzure\Storage\Common\Middlewares\RetryMiddlewareFactory;

/**
 * Service provider for Azure Blob Storage
 *
 * @psalm-type ProviderRetryConfig = array{
 *   tries: integer|null,
 *   interval: integer|null,
 *   increase: "exponential"|null
 * }
 * @psalm-type ProviderConfig = array{
 *  endpoint: string|null,
 *  sasToken: string|null,
 *  name: string,
 *  container: string,
 *  key: string|null,
 *  url: string|null,
 *  prefix: string|null,
 *  retry: ProviderRetryConfig
 * }
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
            assert($client instanceof BlobRestProxy);
            $adapter = new AzureBlobStorageAdapter(
                $client,
                $config['container'],
                $config['key'] ?? null,
                $config['url'] ?? null,
                $config['prefix'] ?? ''
            );

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
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
            assert(is_array($config));

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
     * Create retry middleware instance.
     *
     * @param  ProviderRetryConfig $config
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

    /**
     * Create connection string
     *
     * @psalm-param ProviderConfig $config
     */
    protected function createConnectionString(array $config): string
    {
        if (isset($config['sasToken'])) {
            if (!isset($config['endpoint'])) {
                throw new EndpointNotSet("Endpoint not set when using sasToken");
            }
            return sprintf(
                'BlobEndpoint=%s;SharedAccessSignature=%s;',
                $config['endpoint'],
                $config['sasToken']
            );
        }
        if (!isset($config['key'])) {
            throw new KeyNotSet();
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
