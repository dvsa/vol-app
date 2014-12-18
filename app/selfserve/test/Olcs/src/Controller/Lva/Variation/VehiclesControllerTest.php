<?php

/**
 * Variation Vehicles Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Variation;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**
 * Vehicles Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VehiclesControllerTest extends MockeryTestCase
{
    protected $sm;

    protected $sut;

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\Lva\Variation\VehiclesController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @group variation-vehicle-controller
     */
    public function testAlterFormForLvaInIndexAction()
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')
            ->andReturn(false)
            ->getMock();

        $form = m::mock('\Zend\Form\Form');

        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('getFilterForm')
            ->andReturn('filterForm')
            ->shouldReceive('getForm')
            ->andReturn($form)
            ->shouldReceive('alterForm')
            ->with($form)
            ->andReturn($form)
            ->shouldReceive('getVehicleGoodsAdapter')
            ->andReturn(null)
            ->shouldReceive('getTotalNumberOfVehicles')
            ->andReturn(1)
            ->shouldReceive('getTotalNumberOfAuthorisedVehicles')
            ->andReturn(2)
            ->shouldReceive('render')
            ->with('vehicles', $form, ['filterForm' => 'filterForm'])
            ->andReturn('RENDER');

        $mockFormHelper = m::mock()
            ->shouldReceive('remove')
            ->with($form, 'data->hasEnteredReg')
            ->shouldReceive('remove')
            ->with($form, 'data->notice')
            ->getMock();

        $this->sm->setService('Helper\Form', $mockFormHelper);

        $mockScript = m::mock()
            ->shouldReceive('loadFiles')
            ->with(['lva-crud', 'forms/filter', 'table-actions', 'vehicle-goods'])
            ->getMock();

        $this->sm->setService('Script', $mockScript);

        $this->assertEquals('RENDER', $this->sut->indexAction());
    }
}
