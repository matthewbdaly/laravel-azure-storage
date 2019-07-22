# laravel-azure-storage
[![Build Status](https://travis-ci.org/matthewbdaly/laravel-azure-storage.svg?branch=master)](https://travis-ci.org/matthewbdaly/laravel-azure-storage)
[![Coverage Status](https://coveralls.io/repos/github/matthewbdaly/laravel-azure-storage/badge.svg?branch=master)](https://coveralls.io/github/matthewbdaly/laravel-azure-storage?branch=master)

Microsoft Azure Blob Storage integration for Laravel's Storage API

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

# Support policy

This package is supported on the current Laravel LTS version, and any later versions. If you are using an older Laravel version, it may work, but I offer no guarantees, nor will I accept pull requests to add this support.

By extension, as the current Laravel LTS version required PHP 7.0 or greater, I don't test it against PHP < 7, nor will I accept any pull requests to add this support.
