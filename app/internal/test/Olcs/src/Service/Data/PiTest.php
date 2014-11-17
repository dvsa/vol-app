<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\Pi;
use Mockery as m;

/**
 * Class PiTest
 * @package OlcsTest\Service\Data
 */
class PiTest extends \PHPUnit_Framework_TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new Pi();
    }

    public function testCreateService()
    {

        $mockTranslator = $this->getMock('stdClass', ['getLocale']);
        $mockTranslator->expects($this->once())->method('getLocale')->willReturn('en_GB');

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', 0);
        $mockRestClient->expects($this->once())->method('setLanguage')->with($this->equalTo('en_GB'));

        $mockApiResolver = $this->getMock('stdClass', ['getClient']);
        $mockApiResolver
            ->expects($this->once())
            ->method('getClient')
            ->with($this->equalTo('Pi'))
            ->willReturn($mockRestClient);

        $mockSl = $this->getMock('\Zend\ServiceManager\ServiceManager');
        $mockSl->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['translator', true, $mockTranslator],
                    ['ServiceApiResolver', true, $mockApiResolver],
                ]
            );

        $service = $this->sut->createService($mockSl);

        $this->assertInstanceOf('\Olcs\Service\Data\Pi', $service);
        $this->assertSame($mockRestClient, $service->getRestClient());
    }

    public function testFetchData()
    {
        $id = 1;
        $bundle = [];
        $pi = ['id' => $id];

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', false);
        $mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo('/' . $id), $this->isType('array'))
            ->willReturn($pi);

        $this->sut->setRestClient($mockRestClient);

        $result = $this->sut->fetchData($id, $bundle);
        $this->assertEquals($pi, $result);
    }

    public function testGetBundle()
    {
        $this->assertArrayHasKey('properties', $this->sut->getBundle());
    }


    public function testCanClose()
    {
        $id = 99;
        $mockData = [
            'closedDate' => null
        ];
        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->withAnyArgs()->andReturn($mockData);

        $this->sut->setRestClient($mockRestClient);

        $this->assertTrue($this->sut->canClose($id));
    }

    public function testCanReopen()
    {
        $id = 99;
        $mockData = [
            'closedDate' => null
        ];
        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->withAnyArgs()->andReturn($mockData);

        $this->sut->setRestClient($mockRestClient);

        $this->assertFalse($this->sut->canReopen($id));
    }
}
