<?php

/**
 * Test AbstractVehicleSafetyController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller;

use PHPUnit_Framework_TestCase;

/**
 * Test AbstractVehicleSafetyController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractVehicleSafetyControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Build a mock controller
     *
     * @param array $methods
     */
    protected function getMockController($methods = array())
    {
        $this->controller = $this->getMockForAbstractClass(
            'SelfServe\Controller\VehicleSafety\AbstractVehicleSafetyController',
            array(),
            '',
            true,
            true,
            true,
            $methods
        );
    }

    public function testRenderLayoutWithSubSections()
    {
        $applicationId = 3;

        $completion = array(
            'Count' => 1,
            'Results' => array(
                array()
            )
        );

        $view = $this->getMock('Zend\View\Model\ViewModel');

        $this->getMockController(array('getApplicationId', 'makeRestCall', 'getViewModel'));

        $this->controller->expects($this->any())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('ApplicationCompletion', 'GET')
            ->will($this->returnValue($completion));

        $mockView = $this->getMock('\stdClass', array('setTemplate', 'addChild'));

        $mockView->expects($this->once())
            ->method('setTemplate');

        $mockView->expects($this->once())
            ->method('addChild')
            ->with($view);

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->will($this->returnValue($mockView));

        $this->assertEquals($mockView, $this->controller->renderLayoutWithSubSections($view, 'vehicle'));

        $subSections = $this->controller->getSubSections();

        $this->assertEquals(true, isset($subSections['vehicle']));

        $this->assertEquals(true, isset($subSections['safety']));

        $this->assertEquals(2, count($subSections));
    }

    public function testBackToSafety()
    {
        $applicationId = 3;

        $this->getMockController(array('getApplicationId', 'redirectToRoute'));

        $this->controller->expects($this->once())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->controller->backToSafety());
    }

    public function testRedirectToVehicles()
    {
        $applicationId = 3;

        $this->getMockController(array('getApplicationId', 'redirectToRoute'));

        $this->controller->expects($this->once())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->controller->redirectToVehicles());
    }
}
