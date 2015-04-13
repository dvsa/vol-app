<?php

/**
 * Goods Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\GoodsVehiclesVehicle;

/**
 * Goods Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GoodsVehiclesVehicleTest extends MockeryTestCase
{
    protected $sut;

    protected $fsm;

    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new GoodsVehiclesVehicle();
        $this->sut->setFormServiceLocator($this->fsm);
        $this->sut->setFormHelper($this->formHelper);
    }

    public function testAlterForm()
    {
        $mockForm = m::mock();
        $params = [];

        $mockVehiclesVehicle = m::mock('\Common\FormService\FormServiceInterface');
        $this->fsm->setService('lva-vehicles-vehicle', $mockVehiclesVehicle);

        $mockVehiclesVehicle->shouldReceive('alterForm')
            ->with($mockForm);

        $mockForm->shouldReceive('remove')
            ->with('vehicle-history-table');

        $this->formHelper->shouldReceive('remove')
            ->with($mockForm, 'licence-vehicle->receivedDate');

        $this->sut->alterForm($mockForm, $params);
    }
}
