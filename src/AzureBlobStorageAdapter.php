<?php

namespace Matthewbdaly\LaravelAzureStorage;

use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter as BaseAzureBlobStorageAdapter;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use Matthewbdaly\LaravelAzureStorage\Exceptions\InvalidCustomUrl;

/**
 * Blob storage adapter
 */
final class AzureBlobStorageAdapter extends BaseAzureBlobStorageAdapter
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
     * Custom url for getUrl()
     *
     * @var string
     */
    private $url;

    /**
     * Create a new AzureBlobStorageAdapter instance.
     *
     * @param  \MicrosoftAzure\Storage\Blob\BlobRestProxy $client    Client.
     * @param  string                                     $container Container.
     * @param  string|null                                $url       URL.
     * @param  string|null                                $prefix    Prefix.
     * @throws InvalidCustomUrl                                      URL is not valid.
     */
    public function __construct(BlobRestProxy $client, string $container, string $url = null, $prefix = null)
    {
        parent::__construct($client, $container, $prefix);
        $this->client = $client;
        $this->container = $container;
        if ($url && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidCustomUrl();
        }
        $this->url = $url;
        $this->setPathPrefix($prefix);
    }

    /**
     * Get the file URL by given path.
     *
     * @param  string $path Path.
     * @return string
     */
    public function getUrl(string $path)
    {
        if ($this->url) {
            return rtrim($this->url, '/') . '/' . ($this->container === '$root' ? '' : $this->container . '/') . ltrim($path, '/');
        }
        return $this->client->getBlobUrl($this->container, $path);
    }
}
