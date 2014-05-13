<?php

/**
 * Test SafetyController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller;

use PHPUnit_Framework_TestCase;

/**
 * Test SafetyController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Build a mock controller
     *
     * @param array $methods
     */
    protected function getMockController($methods = array())
    {
        $this->controller = $this->getMock(
            'SelfServe\Controller\VehicleSafety\SafetyController', $methods
        );
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
                'version' => 3,
                'goodsOrPsv' => 'goods'
            ),
            'workshops' => array(
                array(
                    'contactDetails' => array(
                        'id' => 1
                    ),
                    'address' => array(
                        'addressLine1' => 'foo'
                    )
                )
            )
        );

        $this->getMockController(
            array(
                'getApplicationId',
                'makeRestCall',
                'generateTableFormWithData',
                'getViewModel',
                'getUrlFromRoute',
                'renderLayoutWithSubSections'
            )
        );

        $url = '/foo';

        $mockHomeLink = $this->getMock('\stdClass', array('setValue'));

        $mockHomeLink->expects($this->once())
            ->method('setValue')
            ->with($url);

        $this->controller->expects($this->once())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('getUrlFromRoute')
            ->will($this->returnValue($url));

        $mockFormActions = $this->getMock('\stdClass', array('get'));

        $mockFormActions->expects($this->once())
            ->method('get')
            ->with('home')
            ->will($this->returnValue($mockHomeLink));

        $mockForm = $this->getMock('\stdClass', array('get'));

        $mockForm->expects($this->once())
            ->method('get')
            ->with('form-actions')
            ->will($this->returnValue($mockFormActions));

        $this->controller->expects($this->exactly(2))
            ->method('makeRestCall')
            ->with('Application', 'GET', array('id' => $applicationId))
            ->will($this->returnValue($data));

        $this->controller->expects($this->once())
            ->method('generateTableFormWithData')
            ->with('vehicle-safety')
            ->will($this->returnValue($mockForm));

        $viewMock = $this->getMock('\stdClass', array('setTemplate'));

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->will($this->returnValue($viewMock));

        $this->controller->expects($this->once())
            ->method('renderLayoutWithSubSections')
            ->will($this->returnValue($viewMock));

        $this->assertEquals($viewMock, $this->controller->indexAction());
    }

    /**
     * test processVehicleSafetySuccess
     */
    public function testProcessVehicleSafetySuccess()
    {
        $applicationId = 2;

        $data = array(
            'foo' => 'bar'
        );

        $this->getMockController(array('getApplicationId', 'redirectToRoute', 'persistVehicleSafetyData'));

        $this->controller->expects($this->once())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('persistVehicleSafetyData')
            ->with($data);

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->controller->processVehicleSafetySuccess($data));
    }

    /**
     * Test processVehicleSafetyCrudAction with Add Action
     */
    public function testProcessVehicleSafetyCrudActionWithAddAction()
    {
        $data = array(
            'application' => array(
                'id' => 2
            ),
            'foo' => 'bar',
            'table' => array(
                'action' => 'Add'
            )
        );

        $this->getMockController(array('persistVehicleSafetyData', 'redirectToRoute'));

        $this->controller->expects($this->once())
            ->method('persistVehicleSafetyData')
            ->with($data);

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->controller->processVehicleSafetyCrudAction($data));
    }

    /**
     * Test processVehicleSafetyCrudAction with Edit Action Without Id
     */
    public function testProcessVehicleSafetyCrudActionWithEditActionWithoutId()
    {
        $data = array(
            'application' => array(
                'id' => 2
            ),
            'foo' => 'bar',
            'table' => array(
                'action' => 'Edit'
            )
        );

        $this->getMockController(array('persistVehicleSafetyData', 'crudActionMissingId'));

        $this->controller->expects($this->once())
            ->method('persistVehicleSafetyData')
            ->with($data);

        $this->controller->expects($this->once())
            ->method('crudActionMissingId')
            ->will($this->returnValue('MISSING'));

        $this->assertEquals('MISSING', $this->controller->processVehicleSafetyCrudAction($data));
    }

    /**
     * Test processVehicleSafetyCrudAction with Edit Action
     */
    public function testProcessVehicleSafetyCrudActionWithEditAction()
    {
        $data = array(
            'application' => array(
                'id' => 2
            ),
            'foo' => 'bar',
            'table' => array(
                'id' => 7,
                'action' => 'Edit'
            )
        );

        $this->getMockController(array('persistVehicleSafetyData', 'redirectToRoute'));

        $this->controller->expects($this->once())
            ->method('persistVehicleSafetyData')
            ->with($data);

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->controller->processVehicleSafetyCrudAction($data));
    }

    /**
     * Test persistVehicleSafetyData
     */
    public function testPersistVehicleSafetyData()
    {
        $applicationId = 2;

        $data = array(
            'application' => array(
                'version' => 2,
                'safetyConfirmation' => array(
                    '1'
                )
            ),
            'licence' => array(
                'licence.safetyInsVehicles' => 'inspection_interval_vehicle.1',
                'licence.safetyInsTrailers' => 'inspection_interval_trailer.2',
                'licence.tachographIns' => 'tachograph_analyser.3'
            )
        );

        $this->getMockController(array('getApplicationId', 'makeRestCall'));

        $this->controller->expects($this->once())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->at(1))
            ->method('makeRestCall')
            ->with('Application', 'PUT');

        $this->controller->expects($this->at(2))
            ->method('makeRestCall')
            ->with('Licence', 'PUT');

        $this->controller->persistVehicleSafetyData($data);
    }
}
