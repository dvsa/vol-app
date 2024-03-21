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

    public function setUp(): void
    {
        $this->sut = new PsvVehiclesVehicle();
    }

    public function testAlterFormNoop()
    {
        $mockForm = m::mock();
        $this->assertNull($this->sut->alterForm($mockForm));
    }
}
