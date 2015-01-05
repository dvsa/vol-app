<?php

/**
 * Abstract Vehicles Controller Test Case
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**
 * Abstract Vehicles Controller Test Case
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractVehiclesControllerTestCase extends MockeryTestCase
{
    protected $sm;

    protected $sut;

    protected $controllerName;

    public function setUp()
    {
        $this->sut = m::mock($this->controllerName)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Mock abstract vehicle controller
     */
    public function mockAbstractVehicleController()
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
    }
}
