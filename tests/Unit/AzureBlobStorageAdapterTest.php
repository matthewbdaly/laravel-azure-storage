<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Filesystem\FilesystemManager;
use Matthewbdaly\LaravelAzureStorage\AzureBlobStorageAdapter;
use Matthewbdaly\LaravelAzureStorage\Exceptions\KeyNotSet;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

it('correctly generates the file URL', function (): void {
    $client = BlobRestProxy::createBlobService('DefaultEndpointsProtocol=https;AccountName=azure_account;AccountKey=' . base64_encode('azure_key'));
    $adapter = new AzureBlobStorageAdapter($client, 'azure_container', 'azure_key');
    $this->assertEquals('https://azure_account.blob.core.windows.net/azure_container/test.txt', $adapter->getUrl('test.txt'));
});

it('supports the URL method', function (): void {
    $storage = $this->app['filesystem'];
    assert($storage instanceof FilesystemManager);
    $this->assertEquals('https://my_azure_storage_name.blob.core.windows.net/MY_AZURE_STORAGE_CONTAINER/test.txt', $storage->url('test.txt'));
});

it('supports preceding slash', function (): void {
    $storage = $this->app['filesystem'];
    assert($storage instanceof FilesystemManager);
    $this->assertEquals('https://my_azure_storage_name.blob.core.windows.net/MY_AZURE_STORAGE_CONTAINER/test.txt', $storage->url('/test.txt'));
});

it('supports custom URL', function (): void {
    $client = BlobRestProxy::createBlobService('DefaultEndpointsProtocol=https;AccountName=azure_account;AccountKey=' . base64_encode('azure_key'));
    $adapter = new AzureBlobStorageAdapter($client, 'azure_container', 'azure_key', 'https://example.com');
    $this->assertEquals('https://example.com/azure_container/test.txt', $adapter->getUrl('test.txt'));
});

it('supports custom URL with root container', function (): void {
    $client = BlobRestProxy::createBlobService('DefaultEndpointsProtocol=https;AccountName=azure_account;AccountKey=' . base64_encode('azure_key'));
    $adapter = new AzureBlobStorageAdapter($client, '$root', 'azure_key', 'https://example.com');
    $this->assertEquals('https://example.com/test.txt', $adapter->getUrl('test.txt'));
});

it('supports temporary URL without prefix', function (): void {
    $client = BlobRestProxy::createBlobService('DefaultEndpointsProtocol=https;AccountName=azure_account;AccountKey=' . base64_encode('azure_key'));
    $adapter = new AzureBlobStorageAdapter($client, 'azure_container', 'azure_key', null, '');
    $tempUrl = $adapter->getTemporaryUrl('test_path/test.txt', now()->addMinutes(1));
    $this->assertStringStartsWith('https://azure_account.blob.core.windows.net/azure_container/test_path/test.txt', $tempUrl);
    $this->assertStringContainsString('sig=', $tempUrl);
});

it('supports temporary URL with prefix', function (): void {
    $client = BlobRestProxy::createBlobService('DefaultEndpointsProtocol=https;AccountName=azure_account;AccountKey=' . base64_encode('azure_key'));
    $adapter = new AzureBlobStorageAdapter($client, 'azure_container', 'azure_key', null, 'test_prefix');
    $tempUrl = $adapter->getTemporaryUrl('test_path/test.txt', now()->addMinutes(1));
    $this->assertStringStartsWith('https://azure_account.blob.core.windows.net/azure_container/test_prefix/test_path/test.txt', $tempUrl);
    $this->assertStringContainsString('sig=', $tempUrl);
});

it('handles invalid custom URL', function (): void {
    $this->expectException('Matthewbdaly\LaravelAzureStorage\Exceptions\InvalidCustomUrl');
    $client = BlobRestProxy::createBlobService('DefaultEndpointsProtocol=https;AccountName=azure_account;AccountKey=' . base64_encode('azure_key'));
    $adapter = new AzureBlobStorageAdapter($client, 'azure_container', 'azure_key', 'foo');
    $this->assertInstanceOf(AzureBlobStorageAdapter::class, $adapter);
});

it('throws an error if key missing when creating temporary URL', function (): void {
    $client = BlobRestProxy::createBlobService('DefaultEndpointsProtocol=https;AccountName=azure_account;AccountKey=' . base64_encode('azure_key'));
    $adapter = new AzureBlobStorageAdapter($client, 'azure_container', null, null, 'test_path');
    $adapter->getTemporaryUrl('test_path/test.txt', now()->addMinutes(1));
})->throws(KeyNotSet::class);

it('handles custom prefix', function (): void {
    $client = BlobRestProxy::createBlobService('DefaultEndpointsProtocol=https;AccountName=azure_account;AccountKey=' . base64_encode('azure_key'));
    $adapter = new AzureBlobStorageAdapter($client, 'azure_container', 'azure_key', null, 'test_path');
    $this->assertEquals('https://azure_account.blob.core.windows.net/azure_container/test_path/test.txt', $adapter->getUrl('test_path/test.txt'));
});

it('includes the prefix in the return URL', function (): void {
    $client = BlobRestProxy::createBlobService('DefaultEndpointsProtocol=https;AccountName=azure_account;AccountKey=' . base64_encode('azure_key'));
    $adapter = new AzureBlobStorageAdapter($client, 'container', 'azure_key', 'https://example.com', 'my_prefix');
    $this->assertEquals('https://example.com/container/my_prefix/test.txt', $adapter->getUrl('test.txt'));
});
