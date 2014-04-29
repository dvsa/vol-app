<?php

/**
 * Test FinancialEvidenceController
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use SelfServe\Controller\Finance\FinancialEvidenceController;

/**
 * Test FinancialEvidenceController
 */
class FinancialEvidenceControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * Build a mock controller
     *
     * @param array $methods
     */
    protected function getMockController($methods = array())
    {
        $this->controller = $this->getMock(
            'SelfServe\Controller\Finance\FinancialEvidenceController', $methods
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
     * Test indexAction
     */
    public function testIndexAction()
    {
        $this->getMockController(array('getViewModel', 'renderLayout'));

        $mockViewModel = $this->getMock('\stdClass', array('setTemplate'));

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->will($this->returnValue($mockViewModel));

        $this->controller->expects($this->once())
            ->method('renderLayout')
            ->with($mockViewModel)
            ->will($this->returnValue('LAYOUT'));

        $this->assertEquals('LAYOUT', $this->controller->indexAction());

    }

    /**
     * Test completeAction
     */
    public function testCompleteAction()
    {
        $controller = new FinancialEvidenceController();

        $response = $controller->completeAction();

        $this->assertEquals(null, $response);
    }
}
