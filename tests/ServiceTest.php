<?php

namespace Box\Tests\Mod\Imgur;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Box\Mod\Imgur\Service
     */
    protected $service = null;

    public function setup()
    {
        $this->service = new \Box\Mod\Imgur\Service();
    }

    public function testDi()
    {
        $di = new \Box_Di();
        $this->service->setDi($di);
        $getDi = $this->service->getDi();
        $this->assertEquals($di, $getDi);
    }

    public function testInstall()
    {
        $dbMock = $this->getMockBuilder('\Box_Database')->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('exec')
            ->willReturn(true);
        $di = new \Box_Di();
        $di['db'] = $dbMock;

        $this->service->setDi($di);
        $restult = $this->service->install();
        $this->assertInternalType('bool', $restult);
        $this->assertTrue($restult);
    }

    public function testUninstall()
    {
        $sqlDeleteMetaValues = 'DELETE imgur WHERE extension = "mod_imgur"';
        $sqlDeleteTable = 'DROP TABLE IF EXISTS `imgur`';
        $dbMock = $this->getMockBuilder('\Box_Database')->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('exec')
            ->withConsecutive(array($sqlDeleteMetaValues), array($sqlDeleteTable))
            ->willReturn(true);
        $di = new \Box_Di();
        $di['db'] = $dbMock;

        $this->service->setDi($di);
        $restult = $this->service->uninstall();
        $this->assertInternalType('bool', $restult);
        $this->assertTrue($restult);
    }

    public function testgetConfig()
    {
        $extensionServiceMock = $this->getMockBuilder('\Box\Mod\Extension\Service')->getMock();
        $extensionServiceMock->expects($this->atLeastOnce())
            ->method('getConfig')
            ->with('mod_imgur')
            ->willReturn(array());

        $di = new \Box_Di();
        $di['mod_service'] = $di->protect( function ($extensionName) use ($extensionServiceMock) {
            if ($extensionName == 'Extension'){
                return $extensionServiceMock;
            }
        });

        $this->service->setDi($di);
        $result = $this->service->getConfig();
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testupdateConfig()
    {
        $extensionServiceMock = $this->getMockBuilder('\Box\Mod\Extension\Service')->getMock();
        $extensionServiceMock->expects($this->atLeastOnce())
            ->method('setConfig')
            ->willReturn(true);

        $di = new \Box_Di();
        $di['mod_service'] = $di->protect( function ($extensionName) use ($extensionServiceMock) {
            if ($extensionName == 'Extension'){
                return $extensionServiceMock;
            }
        });

        $params = array(
            'client_id' => '1eeee1234',
            'secrect' => 'code',
        );

        $this->service->setDi($di);
        $result = $this->service->updateConfig($params);
        $this->assertInternalType('bool', $result);
        $this->assertTrue($result);
    }

    public function testupdateConfig_Client_ID_missing()
    {
        $params = array(
            'secrect' => 'code',
        );

        $this->setExpectedException('\Box_Exception', 'Client ID param is missing');
        $this->service->updateConfig($params);
    }

    public function testuploadImage()
    {
        $config = array(
            'client_id' => '1eeee1234',
            'secrect' => 'code',
        );
        $extensionServiceMock = $this->getMockBuilder('\Box\Mod\Extension\Service')->getMock();
        $extensionServiceMock->expects($this->atLeastOnce())
            ->method('getConfig')
            ->with('mod_imgur')
            ->willReturn($config);

        $toolsMock = $this->getMockBuilder('\Box_Tools')->getMock();
        $toolsMock->expects($this->atLeastOnce())
            ->method('file_get_contents');

        $guzzleClientMock = $this->getMockBuilder('\Guzzle\Http\Client')->disableOriginalConstructor()->getMock();
        $guzzleClientMock->expects($this->atLeastOnce())
            ->method('post')
            ->with('https://api.imgur.com/3/image.json')
            ->willReturn(json_encode(array('data' => array('link' => 'link/to/file'))));

        $successfullyUploadedFileCount = 1;
        $file = array(
            'name' => 'test',
            'tmp_name' => '12345',
        );
        $fileMock = new \Box_RequestFile($file);

        $requestMock = $this->getMockBuilder('\Box_Request')->getMock();
        $requestMock->expects($this->atLeastOnce())
            ->method('hasFiles')
            ->will($this->returnValue($successfullyUploadedFileCount));
        $requestMock->expects($this->atLeastOnce())
            ->method('getUploadedFiles')
            ->will($this->returnValue(array($fileMock)));


        $di = new \Box_Di();
        $di['mod_service'] = $di->protect( function ($extensionName) use ($extensionServiceMock) {
            if ($extensionName == 'Extension'){
                return $extensionServiceMock;
            }
        });
        $di['tools'] = $toolsMock;
        $di['request'] = $requestMock;
        $di['guzzle_client'] = $guzzleClientMock;

        $this->service->setDi($di);

        $result = $this->service->uploadImage(array());
        $this->assertInternalType('string', $result);
        $this->assertNotEmpty($result);
    }

    public function testuploadImage_UnsuccesfulUpload()
    {
        $config = array(
            'client_id' => '1eeee1234',
            'secrect' => 'code',
        );
        $extensionServiceMock = $this->getMockBuilder('\Box\Mod\Extension\Service')->getMock();
        $extensionServiceMock->expects($this->atLeastOnce())
            ->method('getConfig')
            ->with('mod_imgur')
            ->willReturn($config);

        $toolsMock = $this->getMockBuilder('\Box_Tools')->getMock();
        $toolsMock->expects($this->atLeastOnce())
            ->method('file_get_contents');

        $guzzleClientMock = $this->getMockBuilder('\Guzzle\Http\Client')->disableOriginalConstructor()->getMock();
        $guzzleClientMock->expects($this->atLeastOnce())
            ->method('post')
            ->with('https://api.imgur.com/3/image.json')
            ->willReturn(json_encode(array()));

        $successfullyUploadedFileCount = 1;
        $file = array(
            'name' => 'test',
            'tmp_name' => '12345',
        );
        $fileMock = new \Box_RequestFile($file);

        $requestMock = $this->getMockBuilder('\Box_Request')->getMock();
        $requestMock->expects($this->atLeastOnce())
            ->method('hasFiles')
            ->will($this->returnValue($successfullyUploadedFileCount));
        $requestMock->expects($this->atLeastOnce())
            ->method('getUploadedFiles')
            ->will($this->returnValue(array($fileMock)));


        $di = new \Box_Di();
        $di['mod_service'] = $di->protect( function ($extensionName) use ($extensionServiceMock) {
            if ($extensionName == 'Extension'){
                return $extensionServiceMock;
            }
        });
        $di['tools'] = $toolsMock;
        $di['request'] = $requestMock;
        $di['guzzle_client'] = $guzzleClientMock;

        $this->service->setDi($di);

        $result = $this->service->uploadImage(array());
        $this->assertInternalType('string', $result);
        $this->assertEmpty($result);
    }

    public function testuploadImage_Missing_image()
    {
        $config = array(
            'client_id' => '1eeee1234',
            'secrect' => 'code',
        );

        $requestMock = $this->getMockBuilder('\Box_Request')->getMock();
        $requestMock->expects($this->atLeastOnce())
            ->method('hasFiles')
            ->will($this->returnValue(0));

        $di = new \Box_Di();
        $di['request'] = $requestMock;
        $this->service->setDi($di);

        $this->setExpectedException('\Box_Exception', 'Image is missing');
        $this->service->uploadImage(array());
    }

    public function testuploadImage_Missing_client_id()
    {
        $config = array();
        $extensionServiceMock = $this->getMockBuilder('\Box\Mod\Extension\Service')->getMock();
        $extensionServiceMock->expects($this->atLeastOnce())
            ->method('getConfig')
            ->with('mod_imgur')
            ->willReturn($config);


        $requestMock = $this->getMockBuilder('\Box_Request')->getMock();
        $requestMock->expects($this->atLeastOnce())
            ->method('hasFiles')
            ->will($this->returnValue(1));

        $di = new \Box_Di();
        $di['request'] = $requestMock;
        $di['mod_service'] = $di->protect( function ($extensionName) use ($extensionServiceMock) {
            if ($extensionName == 'Extension'){
                return $extensionServiceMock;
            }
        });

        $this->service->setDi($di);

        $data = array('image' => 'path/to/image.json');
        $this->setExpectedException('\Box_Exception', 'Client ID param is missing');
        $this->service->uploadImage($data);
    }

    public function testsaveImageInfo()
    {

        $model = new \RedBean_SimpleModel();
        $model->loadBean(new \RedBeanPHP\OODBBean());

        $dbMock = $this->getMockBuilder('\Box_Database')->getMock();
        $dbMock->expects($this->atLeastOnce())
            ->method('dispense')
            ->with('Imgur')
            ->willReturn($model);

        $new_id = 1;
        $dbMock->expects($this->atLeastOnce())
            ->method('store')
            ->with($model)
            ->willReturn($new_id);

        $di = new \Box_Di();
        $di['db'] = $dbMock;

        $this->service->setDi($di);
        $result = $this->service->saveImageInfo(1);
        $this->assertInternalType('int', $result);
        $this->assertEquals($new_id, $result);
    }





}