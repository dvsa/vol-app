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
                'version' => 3
            ),
            'workshops' => array(

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

        $this->controller->expects($this->once())
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
}
