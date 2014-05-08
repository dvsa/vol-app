<?php

/**
 * Test FinancialEvidenceController
 */

namespace SelfServe\test\Controller\Finance;

use PHPUnit_Framework_TestCase;

/**
 * Test FinancialEvidenceController
 */
class FinancialEvidenceControllerTest extends PHPUnit_Framework_TestCase
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
     * Test indexAction
     */
    public function testIndexAction()
    {
        $this->getMockController(array('getViewModel', 'renderLayoutWithSubSections'));

        $mockView = $this->getMock('\stdClass', array('setTemplate'));

        $mockView->expects($this->once())
            ->method('setTemplate');

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->will($this->returnValue($mockView));

        $this->controller->expects($this->once())
            ->method('renderLayoutWithSubSections')
            ->with($mockView)
            ->will($this->returnValue('VIEW'));

        $this->assertEquals('VIEW', $this->controller->indexAction());
    }
}
