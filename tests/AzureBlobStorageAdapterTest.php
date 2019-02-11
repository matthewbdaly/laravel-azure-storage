<?php

namespace Tests;

use Matthewbdaly\LaravelAzureStorage\AzureBlobStorageAdapter;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

class AzureBlobStorageAdapterTest extends TestCase
{
    /** @test */
    public function it_correctly_generates_the_file_url()
    {
        $client = BlobRestProxy::createBlobService('DefaultEndpointsProtocol=https;AccountName=azure_account;AccountKey=' . base64_encode('azure_key'));

        $adapter = new AzureBlobStorageAdapter($client, 'azure_container');

        $this->assertEquals('https://azure_account.blob.core.windows.net/azure_container/test.txt', $adapter->getUrl('test.txt'));
    }

    /** @test */
    public function it_now_supports_the_url_method()
    {
        $storage = $this->app['filesystem'];

        $this->assertEquals('https://my_azure_storage_name.blob.core.windows.net/MY_AZURE_STORAGE_CONTAINER/test.txt', $storage->url('test.txt'));
    }

    /** @test */
    public function it_supports_preceding_slash()
    {
        $storage = $this->app['filesystem'];

        $this->assertEquals('https://my_azure_storage_name.blob.core.windows.net/MY_AZURE_STORAGE_CONTAINER/test.txt', $storage->url('/test.txt'));
    }
}
