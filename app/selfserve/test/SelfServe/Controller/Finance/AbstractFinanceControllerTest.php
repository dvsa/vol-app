<?php

/**
 * Test AbstractFinanceController
 */

namespace OlcsTest\Controller;

use PHPUnit_Framework_TestCase;

/**
 * Test AbstractFinanceController
 */
class AbstractFinanceControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Build a mock controller
     *
     * @param array $methods
     */
    protected function getMockController($methods = array())
    {
        $this->controller = $this->getMockForAbstractClass(
            'SelfServe\Controller\Finance\AbstractFinanceController',
            array(),
            '',
            true,
            true,
            true,
            $methods
        );
    }

    /**
     * Test renderLayout
     */
    public function testRenderLayoutWithSubSections()
    {
        $applicationId = 5;
        $current = '';
        $completionStatus = array(
            'Count' => 0,
            'Results' => array()
        );

        $view = $this->getMock('\stdClass');

        $mockLayout = $this->getMock('\stdClass', array('setTemplate', 'addChild'));

        $mockLayout->expects($this->once())
            ->method('setTemplate');

        $mockLayout->expects($this->once())
            ->method('addChild')
            ->with($view);

        $this->getMockController(array('getApplicationId', 'getViewModel', 'makeRestCall'));

        $this->controller->expects($this->any())
            ->method('getApplicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->will($this->returnValue($mockLayout));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($completionStatus));

        $this->assertEquals($mockLayout, $this->controller->renderLayoutWithSubSections($view, $current));
    }
}
