# laravel-azure-storage
[![CircleCI](https://dl.circleci.com/status-badge/img/gh/matthewbdaly/laravel-azure-storage/tree/master.svg?style=svg)](https://dl.circleci.com/status-badge/redirect/gh/matthewbdaly/laravel-azure-storage/tree/master)

Microsoft Azure Blob Storage integration for Laravel's Storage API.

This is a custom driver for [Laravel's File Storage API](https://laravel.com/docs/9.x/filesystem), which is itself built on top of [Flysystem 3](https://flysystem.thephpleague.com/docs/). It uses Flysystem's own Azure blob storage adapter, and so can't easily add any more functionality than that has - indeed, adding that would be out of scope for the project.

# Installation

Install the package using composer:

```bash
composer require matthewbdaly/laravel-azure-storage
```

Then add this to the `disks` section of `config/filesystems.php`:

```php
        'azure' => [ // NB This need not be set to "azure", because it's just the name of the connection - feel free to call it what you want, or even set up multiple blobs with different names
            'driver'    => 'azure', // As this is the name of the driver, this MUST be set to "azure"
            'name'      => env('AZURE_STORAGE_NAME'),
            'key'       => env('AZURE_STORAGE_KEY'),
            'container' => env('AZURE_STORAGE_CONTAINER'),
            'url'       => env('AZURE_STORAGE_URL'),
            'prefix'    => null,
            'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING') // optional, will override default endpoint builder 
        ],
```

Finally, add the fields `AZURE_STORAGE_NAME`, `AZURE_STORAGE_KEY`, `AZURE_STORAGE_CONTAINER` and `AZURE_STORAGE_URL` to your `.env` file with the appropriate credentials. The `AZURE_STORAGE_URL` field is optional, this allows you to set a custom URL to be returned from `Storage::url()`, if using the `$root` container the URL will be returned without the container path. A `prefix` can be optionally used. If it's not set, the container root is used. Then you can set the `azure` driver as either your default or cloud driver and use it to fetch and retrieve files as usual.

For details on how to use this driver, refer to the [Laravel documentation on the file storage API](https://laravel.com/docs/9.x/filesystem).

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

# Can you add support for storage backend X?

No. This is *exclusively* for Azure Blob storage, and I am categorically not interested in expanding the scope of it to support other backends.

As long as Flysystem supports it, you can roll your own Laravel filesystem driver easily as described at https://laravel.com/docs/9.x/filesystem#custom-filesystems if you need to - this is the method I used to build this package.
