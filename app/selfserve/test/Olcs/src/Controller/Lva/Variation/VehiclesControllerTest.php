<?php

/**
 * Variation Vehicles Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Variation;

use OlcsTest\Controller\Lva\AbstractVehiclesControllerTestCase;

/**
 * Variation Vehicles Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VehiclesControllerTest extends AbstractVehiclesControllerTestCase
{
    protected $controllerName = '\Olcs\Controller\Lva\Variation\VehiclesController';

    /**
     * @group variation-vehicle-controller
     */
    public function testAlterFormForLvaInIndexAction()
    {
        $this->mockAbstractVehicleController();
        $this->assertEquals('RENDER', $this->sut->indexAction());
    }
}
