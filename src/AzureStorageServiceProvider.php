<?php

namespace Matthewbdaly\LaravelAzureStorage;

use Storage;
use League\Flysystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Azure\AzureAdapter;
use MicrosoftAzure\Storage\Common\ServicesBuilder;

/**
 * Service provider for Azure Blob Storage
 */
class AzureStorageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('azure', function ($app, $config) {
            $endpoint = sprintf(
                'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s',
                $config['name'],
                $config['key']
            );
            $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($endpoint);
            return new Filesystem(new AzureAdapter($blobRestProxy, "/".$config['container']));
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
