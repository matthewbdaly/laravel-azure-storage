<?php

namespace Tests;

use Storage;
use Mockery as m;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_sets_up_the_storage_correctly()
    {
        $storage = $this->app['filesystem'];
        $this->assertEquals('azure', $storage->getDefaultDriver());
        $this->assertEquals('azure', $storage->getDefaultCloudDriver());
    }

    /** @test */
    public function it_sets_up_the_config_correctly()
    {
        $storage = $this->app['filesystem'];
        $settings = $this->app['config']->get('filesystems.disks.azure');

        foreach($settings as $key => $value){
            $this->assertEquals($value, $storage->getConfig()->get($key));
        }
    }
}
