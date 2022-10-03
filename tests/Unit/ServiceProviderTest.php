<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Config\Repository;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Matthewbdaly\LaravelAzureStorage\Exceptions\EndpointNotSet;
use Matthewbdaly\LaravelAzureStorage\Exceptions\KeyNotSet;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

it('sets up the storage correctly', function (): void {
    $storage = $this->app['filesystem'];
    assert($storage instanceof FilesystemManager);
    $this->assertEquals('azure', $storage->getDefaultDriver());
    $this->assertEquals('azure', $storage->getDefaultCloudDriver());
});

it('sets up the config correctly', function (): void {
    $storage = $this->app['filesystem'];
    assert($storage instanceof FilesystemManager);

    assert($this->app['config'] instanceof Repository);
    $settings = $this->app['config']->get('filesystems.disks.azure');
    assert(is_array($settings));

    foreach ($settings as $key => $value) {
        $this->assertEquals($value, Arr::get($storage->getConfig(), $key));
    }
});

it('handles custom blob endpoints', function (): void {
    $endpoint = 'http://custom';
    assert($this->app['config'] instanceof Repository);
    $container = $this->app['config']->get('filesystems.disks.azure.container');
    assert(is_string($container));
    $this->app['config']->set('filesystems.disks.azure.endpoint', $endpoint);

    $this->assertEquals("$endpoint/$container/a.txt", Storage::url('a.txt'));
});

it('can set a custom URL to override the endpoint', function (): void {
    $endpoint = 'http://custom';
    $customUrl = 'http://cdn.com';
    assert($this->app['config'] instanceof Repository);
    $container = $this->app['config']->get('filesystems.disks.azure.container');
    assert(is_string($container));
    $this->app['config']->set('filesystems.disks.azure.endpoint', $endpoint);
    $this->app['config']->set('filesystems.disks.azure.url', $customUrl);

    $this->assertEquals("$customUrl/$container/a.txt", Storage::url('a.txt'));
});

it('resolves the Azure client', function (): void {
    $this->assertTrue($this->app->bound(BlobRestProxy::class));

    Storage::disk();

    $this->assertTrue($this->app->resolved(BlobRestProxy::class));
});

it('sets up the retry middleware', function (): void {
    $this->app['config']->set('filesystems.disks.azure.retry', [
        'tries' => 3,
        'interval' => 500,
        'increase' => 'exponential',
    ]);

    $this->assertNotNull($this->app->get(BlobRestProxy::class));

    $this->assertTrue($this->app->resolved(BlobRestProxy::class));
});

it('handles a connection string', function (): void {
    $this->app['config']->set('filesystems.disks.azure.connection_string', 'DefaultEndpointsProtocol=http;AccountName=devstoreaccount1;AccountKey=Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==;BlobEndpoint=http://127.0.0.1:10000/devstoreaccount1;');
    $this->assertNotNull($this->app->get(BlobRestProxy::class));
});

it('throws an error if key not set', function (): void {
    $this->app['config']->set('filesystems.disks.azure.key', null);
    $this->app->get(BlobRestProxy::class);
})->throws(KeyNotSet::class);

it('throws an error if endpoint not set when using sasToken', function (): void {
    $this->app['config']->set('filesystems.disks.azure.endpoint', null);
    $this->app['config']->set('filesystems.disks.azure.sasToken', 'foo');
    $this->app->get(BlobRestProxy::class);
})->throws(EndpointNotSet::class);

it('allows using sasToken', function (): void {
    $this->app['config']->set('filesystems.disks.azure.endpoint', 'https://example.com');
    $this->app['config']->set('filesystems.disks.azure.sasToken', 'foo');
    $this->assertNotNull($this->app->get(BlobRestProxy::class));
});
