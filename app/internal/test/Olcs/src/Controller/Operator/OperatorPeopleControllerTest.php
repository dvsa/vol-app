<?php

/**
 * Operator people controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Operator;

use OlcsTest\Controller\Operator\AbstractOperatorControllerTest;

/**
 * Operator people controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorPeopleControllerTest extends AbstractOperatorControllerTest
{
    /**
     * @var string
     */
    protected $controllerName = '\Olcs\Controller\Operator\OperatorPeopleController';

    /**
     * @var array
     */
    protected $mockMethods = ['getViewWithOrganisation', 'renderView'];

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test index action
     *
     * @group operatorPeopleController
     */
    public function testIndexAction()
    {
        $mockView = $this->getMock('Zend\View\Model\ViewModel', ['setTemplate']);
        $mockView->expects($this->once())
            ->method('setTemplate')
            ->with('operator/index')
            ->will($this->returnSelf());

        $this->controller->expects($this->once())
            ->method('getViewWithOrganisation')
            ->will($this->returnValue($mockView));

        $this->controller->expects($this->once())
            ->method('renderView')
            ->with($this->equalTo($mockView))
            ->will($this->returnValue($mockView));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }
}
