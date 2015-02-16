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
     * Tests Bus Controller Trait
     * @author Ian Lindsay <ian@hemera-business-services.co.uk>
     */
    public function testGetViewWithBusReg()
    {
        $busRegId = 1;

        $service = m::mock('Common\Service\Data\BusReg');
        $service->shouldReceive('fetchOne')->with($busRegId);

        $pluginManager = m::mock('Common\Service\Data\PluginManager');
        $pluginManager->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($service);

        $serviceLocator = m::mock('Zend\ServiceManager\ServiceManager');
        $serviceLocator->shouldReceive('get')->with('DataServiceManager')->andReturn($pluginManager);

        $this->trait->expects($this->once())
            ->method('getView');

        $this->trait->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceLocator));

        $this->trait->expects($this->once())
            ->method('getFromRoute')
            ->with('busRegId')
            ->will($this->returnValue($busRegId));

        $this->trait->getViewWithBusReg();
    }

    /**
     * Tests isFromEbsr works when no id is passed
     *
     * @dataProvider isFromEbsrProvider
     *
     * @param int $resultCount
     * @param bool $expectedResult
     */
    public function testIsFromEbsrNullId($resultCount, $expectedResult)
    {
        $id = 1;

        $this->trait->expects($this->once())
            ->method('getFromRoute')
            ->with('busRegId')
            ->will($this->returnValue($id));

        $this->trait->expects($this->once())
            ->method('makeRestCall')
            ->with(
                $this->equalTo('EbsrSubmission'),
                $this->equalTo('GET'),
                $this->equalTo(array('busReg' => $id))
            )
            ->will($this->returnValue($this->getSampleResultWithCount($resultCount)));

        $this->assertEquals($this->trait->isFromEbsr(), $expectedResult);

    }

    /**
     * Tests isFromEbsr works when the id is passed in
     *
     * @dataProvider isFromEbsrProvider
     *
     * @param int $resultCount
     * @param bool $expectedResult
     */
    public function testIsFromEbsrWithId($resultCount, $expectedResult)
    {
        $id = 1;

        $this->trait->expects($this->never())
            ->method('getFromRoute');

        $this->trait->expects($this->once())
            ->method('makeRestCall')
            ->with(
                $this->equalTo('EbsrSubmission'),
                $this->equalTo('GET'),
                $this->equalTo(array('busReg' => $id))
            )
            ->will($this->returnValue($this->getSampleResultWithCount($resultCount)));

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
            [1, true],
            [0, false]
        ];
    }

    /**
     * Simulates a rest call with or without results
     *
     * @param int $count
     * @return array
     */
    private function getSampleResultWithCount($count)
    {
        return [
            'Count' => $count
        ];
    }

    /**
     * Gets a sample bus registration rest result
     *
     * @return array
     */
    private function sampleRestResult()
    {
        return [
            'licence' => [
                'organisation' => [
                    'name' => 'Organisation name'
                ]
            ],
            'status' => [
                'description' => 'Bus reg status'
            ],
            'routeSeq' => '123456',
            'variationNo' => 2,
            'regNo' => '1332432'
        ];
    }
}
