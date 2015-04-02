<?php

/**
 * Psv Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\PsvVehiclesVehicle;

/**
 * Psv Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvVehiclesVehicleTest extends MockeryTestCase
{
    protected $sut;

    protected $fsm;

    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new PsvVehiclesVehicle();
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

        $this->formHelper->shouldReceive('remove')
            ->with($mockForm, 'licence-vehicle->receivedDate');

        $this->sut->alterForm($mockForm, $params);
    }
}
