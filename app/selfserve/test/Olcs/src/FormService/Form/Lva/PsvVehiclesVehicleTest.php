<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\FormService\FormServiceManager;
use Laminas\Form\Form;
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

    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $this->sut = new PsvVehiclesVehicle($this->formHelper, $this->fsm);
    }

    public function testAlterForm(): void
    {
        $mockForm = m::mock();
        $params = [];

        $mockVehiclesVehicle = m::mock(Form::class);
        $this->fsm->setService('lva-vehicles-vehicle', $mockVehiclesVehicle);

        $mockVehiclesVehicle->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $this->formHelper->shouldReceive('remove')
            ->once()
            ->with($mockForm, 'licence-vehicle->receivedDate');

        $this->sut->alterForm($mockForm, $params);
    }
}
