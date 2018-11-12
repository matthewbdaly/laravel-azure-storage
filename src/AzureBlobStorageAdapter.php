<?php

namespace Matthewbdaly\LaravelAzureStorage;

use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter as BaseAzureBlobStorageAdapter;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

class AzureBlobStorageAdapter extends BaseAzureBlobStorageAdapter
{
    /**
     * Base file URL.
     *
     * @var string
     */
    protected $baseFileUrl;

    /**
     * Create a new AzureBlobStorageAdapter instance.
     *
     * @param  \MicrosoftAzure\Storage\Blob\BlobRestProxy  $client
     * @param  string  $container
     * @param  string|null  $prefix
     */
    public function __construct(BlobRestProxy $client, $container, $prefix = null)
    {
        parent::__construct($client, $container, $prefix);

        $this->baseFileUrl = $client->getPsrPrimaryUri().$container;
    }

    /**
     * Get the file URL by given path.
     *
     * @param  string  $path
     * @return string
     */
    public function getUrl($path)
    {
        return $this->baseFileUrl.'/'.$path;
    }
}
