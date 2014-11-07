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



}