<?php

/**
 * Safety Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Journey\Application\VehicleSafety;

use CommonTest\Controller\Application\VehicleSafety\SafetyControllerTest as ParentTestCase;

/**
 * Safety Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyControllerTest extends ParentTestCase
{
    protected $controllerName = '\Olcs\Controller\Journey\Application\VehicleSafety\SafetyController';
    protected $routeName = 'Application/VehicleSafety/Safety';

    public function testIndexActionUpdatingStatusWithRows()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'index',
            null,
            array(
                'licence' => array(
                    'safetyInsVehicles' => '',
                    'safetyInsTrailers' => '',
                    'safetyInsVaries' => '',
                    'tachographIns' => ''
                ),
                'table' => array(
                    'rows' => 1
                ),
                'application' => array(
                    'id' => 1,
                    'version' => 1,
                    'safetyConfirmation' => ''
                )
            )
        );

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    public function testIndexActionUpdatingStatusWithSafetyConfirmation()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'index',
            null,
            array(
                'licence' => array(
                    'safetyInsVehicles' => '',
                    'safetyInsTrailers' => '',
                    'safetyInsVaries' => '',
                    'tachographIns' => ''
                ),
                'table' => array(
                    'rows' => 0
                ),
                'application' => array(
                    'id' => 1,
                    'version' => 1,
                    'safetyConfirmation' => 'Y'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    public function testIndexActionUpdatingStatusWithSafetyConfirmationUnchecked()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'index',
            null,
            array(
                'licence' => array(
                    'safetyInsVehicles' => '',
                    'safetyInsTrailers' => '',
                    'safetyInsVaries' => '',
                    'tachographIns' => ''
                ),
                'table' => array(
                    'rows' => 0
                ),
                'application' => array(
                    'id' => 1,
                    'version' => 1,
                    'safetyConfirmation' => 'N'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    public function testIndexActionUpdatingStatusWithoutFormChanges()
    {
        $this->goodsOrPsv = 'goods';

        $this->setUpAction(
            'index',
            null,
            array(
                'licence' => array(
                    'safetyInsVehicles' => '',
                    'safetyInsTrailers' => '',
                    'safetyInsVaries' => '',
                    'tachographIns' => ''
                ),
                'table' => array(
                    'rows' => 0
                ),
                'application' => array(
                    'id' => 1,
                    'version' => 1,
                    'safetyConfirmation' => ''
                )
            )
        );

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }
}
