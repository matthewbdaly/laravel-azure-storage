# laravel-azure-storage
[![Build Status](https://travis-ci.org/matthewbdaly/laravel-azure-storage.svg?branch=master)](https://travis-ci.org/matthewbdaly/laravel-azure-storage)

Microsoft Azure Blob Storage integration for Laravel's Storage API

# Installation

Install the package using composer:

```bash
composer require matthewbdaly/laravel-azure-storage
```

Then add this to the `disks` section of `config/filesystems.php`:

```php
        'azure' => [
            'driver'    => 'azure',
            'name'      => env('AZURE_STORAGE_NAME'),
            'key'       => env('AZURE_STORAGE_KEY'),
            'container' => env('AZURE_STORAGE_CONTAINER'),
        ],
```

Finally, add the fields `AZURE_STORAGE_NAME`, `AZURE_STORAGE_KEY` and `AZURE_STORAGE_CONTAINER` to your `.env` file with the appropriate credentials.
