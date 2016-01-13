<?php

/**
 * Variation Vehicles PSV Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Variation;

use Mockery as m;
use OlcsTest\Controller\Lva\AbstractVehiclesPsvControllerTestCase;

/**
 * Variation Vehicles PSV Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VehiclesPsvControllerTest extends AbstractVehiclesPsvControllerTestCase
{
    protected $controllerName = '\Olcs\Controller\Lva\Variation\VehiclesPsvController';

    /**
     * @group variation-vehicle-psv-controller
     */
    public function testAlterFormForLvaInIndexAction()
    {
        $this->mockAbstractVehiclePsvController();

        $fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();
        $this->sm->setService('FormServiceManager', $fsm);

        $mockLvVehicles = m::mock('\Common\FormService\FormServiceInterface');
        $fsm->setService('lva-licence-variation-vehicles', $mockLvVehicles);

        $mockLvVehicles->shouldReceive('alterForm')
            ->once();

        $this->assertEquals('RENDER', $this->sut->indexAction());
    }
}
