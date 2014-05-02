<?php

/**
 * Test AbstractFinanceController
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use SelfServe\Controller\Finance\AbstractFinanceController;

/**
 * Test AbstractFinanceController
 */
class AbstractFinanceControllerTest extends AbstractHttpControllerTestCase
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
     * Test renderLayout
     */
    public function testRenderLayout()
    {
        $applicationId = 5;

        $view = $this->getMock('\stdClass');

        $this->getMockController(array('params', 'getViewModel', 'makeRestCall'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));
        
        $mockJourney=Array('Count'=>0,'Results'=>[]);
        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValue($mockJourney));

        $mockViewModel = $this->getMock('\stdClass', array('setTemplate', 'addChild'));

        $mockViewModel->expects($this->once())
            ->method('addChild')
            ->with($view, 'main');

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->will($this->returnValue($mockViewModel));

        $this->assertEquals($mockViewModel, $this->controller->renderLayout($view));

    }
}
