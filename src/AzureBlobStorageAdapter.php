<?php

namespace Matthewbdaly\LaravelAzureStorage;

use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter as BaseAzureBlobStorageAdapter;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

class AzureBlobStorageAdapter extends BaseAzureBlobStorageAdapter
{
    /**
     * The Azure Blob Client
     *
     * @var BlobRestProxy
     */
    private $client;

    /**
     * The container name
     *
     * @var string
     */
    private $container;

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
        $this->client = $client;
        $this->container = $container;
        $this->setPathPrefix($prefix);
    }

    /**
     * Get the file URL by given path.
     *
     * @param  string  $path
     * @return string
     */
    public function getUrl(string $path)
    {
        return $this->client->getBlobUrl($this->container, $path);
    }
}
