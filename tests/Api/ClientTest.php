<?php

namespace Box\Tests\Mod\Imgur\Api;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Box\Mod\Imgur\Api\Client
     */
    protected $api = null;

    public function setup()
    {
        $this->api = new \Box\Mod\Imgur\Api\Client();
    }

    public function testuploadImage_createsNewImageInfo()
    {
        $validatorMock = $this->getMockBuilder('\Box_Validate')->getMock();
        $validatorMock->expects($this->atLeastOnce())
            ->method('checkRequiredParamsForArray');

        $di = new \Box_Di();
        $di['validator'] = $validatorMock;
        $this->api->setDi($di);

        $client = new \Model_Client();
        $client->loadBean(new \RedBeanPHP\OODBBean());
        $client->id;
        $this->api->setIdentity($client);

        $imageLink = 'uri/to/image';
        $serviceMock = $this->getMockBuilder('\Box\Mod\Imgur\Service')->getMock();
        $serviceMock->expects($this->atLeastOnce())
            ->method('uploadImage')
            ->willReturn($imageLink);

        $serviceMock->expects($this->atLeastOnce())
            ->method('imageInfoExists')
            ->willReturn(null);

        $newId = 1;
        $serviceMock->expects($this->atLeastOnce())
            ->method('saveImageInfo')
            ->with($imageLink)
            ->willReturn($newId);

        $this->api->setService($serviceMock);

        $result = $this->api->uploadImage(array('support_ticket_id' => 1));
        $this->assertInternalType('int', $result);
        $this->assertEquals($newId, $result);
    }

    public function testuploadImage_updateImageInfo()
    {
        $validatorMock = $this->getMockBuilder('\Box_Validate')->getMock();
        $validatorMock->expects($this->atLeastOnce())
            ->method('checkRequiredParamsForArray');

        $di = new \Box_Di();
        $di['validator'] = $validatorMock;
        $this->api->setDi($di);

        $client = new \Model_Client();
        $client->loadBean(new \RedBeanPHP\OODBBean());
        $client->id;
        $this->api->setIdentity($client);

        $imageLink = 'uri/to/image';
        $serviceMock = $this->getMockBuilder('\Box\Mod\Imgur\Service')->getMock();
        $serviceMock->expects($this->atLeastOnce())
            ->method('uploadImage')
            ->willReturn($imageLink);

        $serviceMock->expects($this->atLeastOnce())
            ->method('imageInfoExists')
            ->willReturn(new \RedBean_SimpleModel());

        $updateedId = 1;
        $serviceMock->expects($this->atLeastOnce())
            ->method('updateImageInfo')
            ->with(new \RedBean_SimpleModel(), $imageLink)
            ->willReturn($updateedId);

        $this->api->setService($serviceMock);

        $result = $this->api->uploadImage(array('support_ticket_id' => 1));
        $this->assertInternalType('int', $result);
        $this->assertEquals($updateedId, $result);
    }

    public function testuploadImage_uploadException()
    {
        $validatorMock = $this->getMockBuilder('\Box_Validate')->getMock();
        $validatorMock->expects($this->atLeastOnce())
            ->method('checkRequiredParamsForArray');

        $di = new \Box_Di();
        $di['validator'] = $validatorMock;
        $this->api->setDi($di);

        $client = new \Model_Client();
        $client->loadBean(new \RedBeanPHP\OODBBean());
        $client->id;
        $this->api->setIdentity($client);

        $imageLink = '';
        $serviceMock = $this->getMockBuilder('\Box\Mod\Imgur\Service')->getMock();
        $serviceMock->expects($this->atLeastOnce())
            ->method('uploadImage')
            ->willReturn($imageLink);

        $this->api->setService($serviceMock);

        $this->setExpectedException('\Box_Exception', 'Upload unsuccessful');
        $this->api->uploadImage(array('support_ticket_id' => 1));

    }
}