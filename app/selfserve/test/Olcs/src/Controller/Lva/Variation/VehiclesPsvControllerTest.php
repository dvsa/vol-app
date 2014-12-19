<?php

/**
 * Variation Vehicles PSV Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Variation;

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
        $this->assertEquals('RENDER', $this->sut->indexAction());
    }
}
