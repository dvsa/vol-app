<?php

/**
 * Licence Vehicles Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Licence;

use OlcsTest\Controller\Lva\AbstractVehiclesControllerTestCase;

/**
 * Licence Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VehicleControllerTest extends AbstractVehiclesControllerTestCase
{
    protected $controllerName = '\Olcs\Controller\Lva\Licence\VehiclesController';

    /**
     * @group licence-vehicle-controller
     */
    public function testAlterFormForLvaInIndexAction()
    {
        $this->mockAbstractVehicleController();
        $this->assertEquals('RENDER', $this->sut->indexAction());
    }
}
