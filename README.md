# laravel-azure-storage
[![Build Status](https://travis-ci.org/matthewbdaly/laravel-azure-storage.svg?branch=master)](https://travis-ci.org/matthewbdaly/laravel-azure-storage)
[![Coverage Status](https://coveralls.io/repos/github/matthewbdaly/laravel-azure-storage/badge.svg?branch=master)](https://coveralls.io/github/matthewbdaly/laravel-azure-storage?branch=master)

Microsoft Azure Blob Storage integration for Laravel's Storage API.

This is a custom driver for [Laravel's File Storage API](https://laravel.com/docs/8.x/filesystem), which is itself built on top of [Flysystem](https://flysystem.thephpleague.com/v1/docs/). It uses Flysystem's own Azure blob storage adapter, and so can't easily add any more functionality than that has, and indeed adding that would be out of scope for the project.

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
        ],
```

Finally, add the fields `AZURE_STORAGE_NAME`, `AZURE_STORAGE_KEY`, `AZURE_STORAGE_CONTAINER` and `AZURE_STORAGE_URL` to your `.env` file with the appropriate credentials. The `AZURE_STORAGE_URL` field is optional, this allows you to set a custom URL to be returned from `Storage::url()`, if using the `$root` container the URL will be returned without the container path. A `prefix` can be optionally used. If it's not set, the container root is used. Then you can set the `azure` driver as either your default or cloud driver and use it to fetch and retrieve files as usual.

For details on how to use this driver, refer to the [Laravel documentation on the file storage API](https://laravel.com/docs/7.x/filesystem).

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
            'endpoint'  => env('AZURE_STORAGE_ENDPOINT'),
        ],
```

Then you can specify a suitable value for `AZURE_STORAGE_ENDPOINT` in your `.env` file as normal.

# Support policy

This package is supported on the current Laravel LTS version, and any later versions. If you are using an older Laravel version, it may work, but I offer no guarantees, nor will I accept pull requests to add this support.

By extension, as the current Laravel LTS version required PHP 7.0 or greater, I don't test it against PHP < 7, nor will I accept any pull requests to add this support.
