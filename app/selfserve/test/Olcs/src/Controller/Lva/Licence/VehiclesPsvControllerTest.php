<?php

/**
 * Licence Vehicles PSV Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Licence;

use OlcsTest\Controller\Lva\AbstractVehiclesPsvControllerTestCase;

/**
 * Licence Vehicles PSV Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VehiclesPsvControllerTest extends AbstractVehiclesPsvControllerTestCase
{
    protected $controllerName = '\Olcs\Controller\Lva\Licence\VehiclesPsvController';

    /**
     * @group licence-vehicle-psv-controller
     */
    public function testAlterFormForLvaInIndexAction()
    {
        $this->mockAbstractVehiclePsvController();
        $this->assertEquals('RENDER', $this->sut->indexAction());
    }
}
