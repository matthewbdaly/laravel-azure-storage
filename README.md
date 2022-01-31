# laravel-azure-storage
[![Build Status](https://travis-ci.org/matthewbdaly/laravel-azure-storage.svg?branch=master)](https://travis-ci.org/matthewbdaly/laravel-azure-storage)
[![Coverage Status](https://coveralls.io/repos/github/matthewbdaly/laravel-azure-storage/badge.svg?branch=master)](https://coveralls.io/github/matthewbdaly/laravel-azure-storage?branch=master)

Microsoft Azure Blob Storage integration for Laravel's Storage API.

This is a custom driver for [Laravel's File Storage API](https://laravel.com/docs/8.x/filesystem), which is itself built on top of [Flysystem](https://flysystem.thephpleague.com/v1/docs/). It uses Flysystem's own Azure blob storage adapter, and so can't easily add any more functionality than that has - indeed, adding that would be out of scope for the project.

# Installation

Install the package using composer:

```bash
composer require matthewbdaly/laravel-azure-storage
```

On Laravel versions before 5.5 you also need to add the service provider to `config/app.php` manually:

```php
    Matthewbdaly\LaravelAzureStorage\AzureStorageServiceProvider::class,
```

Then add this to the `disks` section of `config/filesystems.php`:

```php
        'azure' => [
            'driver'    => 'azure',
            'name'      => env('AZURE_STORAGE_NAME'),
            'key'       => env('AZURE_STORAGE_KEY'),
            'container' => env('AZURE_STORAGE_CONTAINER'),
            'url'       => env('AZURE_STORAGE_URL'),
            'prefix'    => null,
            'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING') // optional, will override default endpoint builder 
        ],
```

Finally, add the fields `AZURE_STORAGE_NAME`, `AZURE_STORAGE_KEY`, `AZURE_STORAGE_CONTAINER` and `AZURE_STORAGE_URL` to your `.env` file with the appropriate credentials. The `AZURE_STORAGE_URL` field is optional, this allows you to set a custom URL to be returned from `Storage::url()`, if using the `$root` container the URL will be returned without the container path. A `prefix` can be optionally used. If it's not set, the container root is used. Then you can set the `azure` driver as either your default or cloud driver and use it to fetch and retrieve files as usual.

For details on how to use this driver, refer to the [Laravel documentation on the file storage API](https://laravel.com/docs/filesystem).

# Custom endpoints

The package supports using a custom endpoint, as in this example:

```php
        'azure' => [
            'driver'    => 'azure',
            'name'      => env('AZURE_STORAGE_NAME'),
            'key'       => env('AZURE_STORAGE_KEY'),
            'container' => env('AZURE_STORAGE_CONTAINER'),
            'url'       => env('AZURE_STORAGE_URL'),
            'prefix'    => null,
            'connection_string' => null,
            'endpoint'  => env('AZURE_STORAGE_ENDPOINT'),
        ],
```

Then you can specify a suitable value for `AZURE_STORAGE_ENDPOINT` in your `.env` file as normal.

# SAS token authentication
With SAS token authentication the endpoint is required. The value has the following format: `https://[accountName].blob.core.windows.net`
```php
        'azure' => [
            'driver'    => 'azure',
            'sasToken'  => env('AZURE_STORAGE_SAS_TOKEN'),
            'container' => env('AZURE_STORAGE_CONTAINER'),
            'url'       => env('AZURE_STORAGE_URL'),
            'prefix'    => null,
            'endpoint'  => env('AZURE_STORAGE_ENDPOINT'),
        ],
```

# Caching
The package supports disk based caching as described in the [Laravel documentation](https://laravel.com/docs/filesystem#caching).
This feature requires adding the package `league/flysystem-cached-adapter`:
```bash
composer require league/flysystem-cached-adapter:^1.1
```

To enable caching for the azure disk, add a `cache` directive to the disk's configuration options.
```php
        'azure' => [
            'driver'    => 'azure',
            // Other Disk Options...
            'cache'     => [
                'store' => 'memcached',
                'expire' => 600,
                'prefix' => 'filecache',
            ]
        ],
```

# Retries
The Azure Storage SDK ships a [middleware to retry](https://github.com/Azure/azure-storage-php#retrying-failures) failed requests.
To enable the retry middewalre, add a `retry` directive to the disk's configuration options.
```php
        'azure' => [
            'driver'    => 'azure',
            // Other Disk Options...
            'retry'     => [
                'tries' => 3,                   // number of retries, default: 3
                'interval' => 500,              // wait interval in ms, default: 1000ms
                'increase' => 'exponential'     // how to increase the wait interval, options: linear, exponential, default: linear
            ]
        ],
```

# Support policy

This package is supported on the current Laravel LTS version, and any later versions. If you are using an older Laravel version, it may work, but I offer no guarantees, nor will I accept pull requests to add this support.

By extension, as the current Laravel LTS version required PHP 7.0 or greater, I don't test it against PHP < 7, nor will I accept any pull requests to add this support.
