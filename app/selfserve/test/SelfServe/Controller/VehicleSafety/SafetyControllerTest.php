<?php

/**
 * Test SafetyController
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\View\Model\ViewModel;
use SelfServe\Controller\VehiclesSafety\SafetyController;

/**
 * Test SafetyController
 */
class SafetyControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * Build a mock controller
     *
     * @param array $methods
     */
    protected function getMockController($methods = array())
    {
        $this->controller = $this->getMock(
            'SelfServe\Controller\VehiclesSafety\SafetyController', $methods
        );
    }

    /**
     * Set up the unit tests
     */
    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../config/application.config.php'
        );

        parent::setUp();
    }

    /**
     * Test completeAction
     */
    public function testCompleteAction()
    {
        $controller = new SafetyController();

        $response = $controller->completeAction();

        $this->assertTrue($response instanceof ViewModel);
    }

    /**
     * Test indexAction Without data
     */
    public function testIndexActionWithoutData()
    {
        $applicationId = 3;

        $data = array();

        $this->getMockController(array('params', 'makeRestCall', 'notFoundAction'));

        $paramsMock = $this->getMock('\stdClass', array('fromRoute'));

        $paramsMock->expects($this->once())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($paramsMock));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Application', 'GET', array('id' => $applicationId))
            ->will($this->returnValue($data));

        $this->controller->expects($this->once())
            ->method('notFoundAction')
            ->will($this->returnValue(404));

        $this->assertEquals(404, $this->controller->indexAction());
    }

    /**
     * Test indexAction
     */
    public function testIndexAction()
    {
        $applicationId = 3;

        $data = array(
            'version' => 4,
            'safetyConfirmation' => 1,
            'licence' => array(
                'safetyInsVehicles' => 2,
                'safetyInsTrailers' => 3,
                'safetyInsVaries' => 1,
                'tachographIns' => 1,
                'tachographInsName' => 'Foo',
                'version' => 3
            )
        );

        $this->getMockController(array('params', 'makeRestCall', 'generateFormWithData', 'getViewModel'));

        $paramsMock = $this->getMock('\stdClass', array('fromRoute'));

        $paramsMock->expects($this->once())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($paramsMock));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Application', 'GET', array('id' => $applicationId))
            ->will($this->returnValue($data));

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('vehicle-safety');

        $viewMock = $this->getMock('\stdClass', array('setTemplate'));

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->will($this->returnValue($viewMock));

        $this->assertEquals($viewMock, $this->controller->indexAction());
    }

    /**
     * Test processVehicleSafety
     */
    public function testProcessVehicleSafety()
    {
        $applicationId = 3;

        $data = array(
            'data' => array(
                'version' => 4,
                'safetyConfirmation' => array(1),
                'licence.safetyInsVehicles' => 'inspection_interval_vehicle.2',
                'licence.safetyInsTrailers' => 'inspection_interval_trailer.3',
                'licence.safetyInsVaries' => 1,
                'licence.tachographIns' => 'tachograph_analyser.1',
                'licence.tachographInsName' => 'Foo',
                'licence.version' => 3
            )
        );

        $this->expectedApplicationData = array(
            'id' => $applicationId,
            'version' => 4,
            'safetyConfirmation' => 1
        );

        $this->expectedLicenceData = array(
            'safetyInsVehicles' => '2',
            'safetyInsTrailers' => '3',
            'safetyInsVaries' => 1,
            'tachographIns' => '1',
            'tachographInsName' => 'Foo',
            'version' => 3
        );

        $this->getMockController(array('params', 'makeRestCall', 'redirect'));

        $paramsMock = $this->getMock('\stdClass', array('fromRoute'));

        $paramsMock->expects($this->once())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($paramsMock));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will(
                $this->returnCallback(
                    function ($service, $method, $data) {
                        $this->assertEquals('PUT', $method);

                        if ($service == 'Application') {

                            $this->assertEquals($this->expectedApplicationData, $data);

                        } elseif ($service == 'Licence') {

                            $this->assertEquals($this->expectedLicenceData, $data);
                        }
                    }
                )
            );

        $redirectMock = $this->getMock('\stdClass', array('toRoute'));

        $redirectMock->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('redirect'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirectMock));

        $this->assertEquals('redirect', $this->controller->processVehicleSafety($data));
    }
}
