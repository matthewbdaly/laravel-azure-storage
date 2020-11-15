<?php

namespace Matthewbdaly\LaravelAzureStorage;

use Illuminate\Support\Arr;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter as BaseAzureBlobStorageAdapter;
use Matthewbdaly\LaravelAzureStorage\Exceptions\InvalidCustomUrl;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\BlobSharedAccessSignatureHelper;
use MicrosoftAzure\Storage\Common\Internal\Resources;

/**
 * Blob storage adapter
 *
 * @internal
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
     * @var string|null
     */
    private $url;

    /**
     * The account key to access the storage
     *
     * @var string|null
     */
    private $key;

    /**
     * Create a new AzureBlobStorageAdapter instance.
     *
     * @param \MicrosoftAzure\Storage\Blob\BlobRestProxy $client Client.
     * @param string $container Container.
     * @param string $key
     * @param string|null $url URL.
     * @param string|null $prefix Prefix.
     * @throws InvalidCustomUrl URL is not valid.
     */
    public function __construct(BlobRestProxy $client, string $container, string $key = null, string $url = null, $prefix = null)
    {
        parent::__construct($client, $container, $prefix);
        $this->client = $client;
        $this->container = $container;
        if ($url && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidCustomUrl();
        }
        $this->url = $url;
        $this->key = $key;
        $this->setPathPrefix($prefix);
    }

    /**
     * Get the file URL by given path.
     *
     * @param  string $path Path.
     *
     * @return string
     */
    public function getUrl(string $path)
    {
        if ($this->url) {
            return rtrim($this->url, '/') . '/' . ($this->container === '$root' ? '' : $this->container . '/') . ltrim($path, '/');
        }
        return $this->client->getBlobUrl($this->container, $path);
    }

    /**
     * Generate Temporary Url with SAS query
     *
     * @param string $path
     * @param \Datetime|string $ttl
     * @param array $options
     *
     * @return string
     */
    public function getTemporaryUrl(string $path, $ttl, array $options = [])
    {
        $resourceName = (empty($path) ? $this->container : $this->container  . '/' . $path);
        $sas = new BlobSharedAccessSignatureHelper($this->client->getAccountName(), $this->key);
        $sasString = $sas->generateBlobServiceSharedAccessSignatureToken(
            Arr::get($options, 'signed_resource', 'b'),
            $resourceName,
            Arr::get($options, 'signed_permissions', 'r'),
            $ttl,
            Arr::get($options, 'signed_start', ''),
            Arr::get($options, 'signed_ip', ''),
            Arr::get($options, 'signed_protocol', 'https'),
            Arr::get($options, 'signed_identifier', ''),
            Arr::get($options, 'cache_control', ''),
            Arr::get($options, 'content_disposition', ''),
            Arr::get($options, 'content_encoding', ''),
            Arr::get($options, 'content_language', ''),
            Arr::get($options, 'content_type', '')
        );

        return sprintf('%s?%s', $this->getUrl($path), $sasString);
    }
}
