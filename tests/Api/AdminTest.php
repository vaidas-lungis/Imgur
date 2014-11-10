<?php

namespace Box\Tests\Mod\Imgur\Api;

class AdmintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Box\Mod\Imgur\Api\Admin
     */
    protected $api = null;

    public function setup()
    {
        $this->api = new \Box\Mod\Imgur\Api\Admin();
    }

    public function testconfig_get()
    {
        $serviceMock = $this->getMockBuilder('\Box\Mod\Imgur\Service')->getMock();
        $serviceMock->expects($this->atLeastOnce())
            ->method('getConfig')
            ->willReturn(array());

        $this->api->setService($serviceMock);
        $result = $this->api->config_get(array());
        $this->assertInternalType('array', $result);
    }

    public function testconfig_update()
    {
        $serviceMock = $this->getMockBuilder('\Box\Mod\Imgur\Service')->getMock();
        $serviceMock->expects($this->atLeastOnce())
            ->method('updateConfig')
            ->willReturn(array());

        $this->api->setService($serviceMock);
        $result = $this->api->config_update(array());
        $this->assertInternalType('array', $result);
    }
}