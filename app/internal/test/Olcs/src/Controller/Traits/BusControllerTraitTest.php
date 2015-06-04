<?php

namespace OlcsTest\Controller\Traits;

use Mockery as m;

/**
 * Tests Bus Controller Trait
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusControllerTraitTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->trait = $this->getMockForTrait(
            '\Olcs\Controller\Traits\BusControllerTrait', array(), '', true, true, true, array(
                'getView',
                'makeRestCall',
                'getFromRoute',
                'getServiceLocator'
            )
        );
    }

    /**
     * Tests isFromEbsr works when no id is passed
     *
     * @dataProvider isFromEbsrProvider
     *
     * @param array $busRegData
     * @param bool $expectedResult
     */
    public function testIsFromEbsrNullId($busRegData, $expectedResult)
    {
        $this->markTestSkipped();
        $id = 1;

        $service = m::mock('Common\Service\Data\BusReg');
        $service->shouldReceive('fetchOne')->once()->with($id)->andReturn($busRegData);

        $pluginManager = m::mock('Common\Service\Data\PluginManager');
        $pluginManager->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($service);

        $serviceLocator = m::mock('Zend\ServiceManager\ServiceManager');
        $serviceLocator->shouldReceive('get')->with('DataServiceManager')->andReturn($pluginManager);

        $this->trait->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceLocator));

        $this->trait->expects($this->once())
            ->method('getFromRoute')
            ->with('busRegId')
            ->will($this->returnValue($id));

        $this->assertEquals($this->trait->isFromEbsr(), $expectedResult);

    }

    /**
     * Tests isFromEbsr works when the id is passed in
     *
     * @dataProvider isFromEbsrProvider
     *
     * @param array $busRegData
     * @param bool $expectedResult
     */
    public function testIsFromEbsrWithId($busRegData, $expectedResult)
    {
        $this->markTestSkipped();
        $id = 1;

        $service = m::mock('Common\Service\Data\BusReg');
        $service->shouldReceive('fetchOne')->once()->with($id)->andReturn($busRegData);

        $pluginManager = m::mock('Common\Service\Data\PluginManager');
        $pluginManager->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($service);

        $serviceLocator = m::mock('Zend\ServiceManager\ServiceManager');
        $serviceLocator->shouldReceive('get')->with('DataServiceManager')->andReturn($pluginManager);

        $this->trait->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceLocator));

        $this->trait->expects($this->never())
            ->method('getFromRoute');

        $this->assertEquals($this->trait->isFromEbsr($id), $expectedResult);
    }

    /**
     * Data provider for isEbsr tests
     *
     * @return array
     */
    public function isFromEbsrProvider()
    {
        return [
            [['isTxcApp' => 'Y'], true],
            [['isTxcApp' => 'N'], false],
            [[], false]
        ];
    }
}
