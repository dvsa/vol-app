<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\VehiclesVehicle;

/**
 * Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesVehicleTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);

        $this->sut = new VehiclesVehicle($this->formHelper);
    }

    public function testAlterForm(): void
    {
        $mockForm = m::mock();

        $this->formHelper->shouldReceive('remove')
            ->once()
            ->with($mockForm, 'licence-vehicle->specifiedDate')
            ->shouldReceive('remove')
            ->once()
            ->with($mockForm, 'licence-vehicle->removalDate')
            ->shouldReceive('remove')
            ->once()
            ->with($mockForm, 'licence-vehicle->discNo');

        $this->sut->alterForm($mockForm);
    }
}
