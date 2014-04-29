<?php

/**
 * Test OperatingCentreController
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use SelfServe\Controller\Finance\OperatingCentreController;

/**
 * Test OperatingCentreController
 */
class OperatingCentreControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * Build a mock controller
     *
     * @param array $methods
     */
    protected function getMockController($methods = array())
    {
        $this->controller = $this->getMock(
            'SelfServe\Controller\Finance\OperatingCentreController', $methods
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
     * Test indexAction With Crud Action
     */
    public function testIndexActionWithCrudAction()
    {
        $this->getMockController(array('checkForCrudAction'));

        $this->controller->expects($this->once())
            ->method('checkForCrudAction')
            ->will($this->returnValue('add'));

        $this->assertEquals('add', $this->controller->indexAction());
    }

    /**
     * Test indexAction With Missing Application
     */
    public function testIndexActionWithMissingApplication()
    {
        $applicationData = array();

        $applicationId = 7;

        $this->getMockController(array('checkForCrudAction', 'params', 'makeRestCall', 'notFoundAction'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('checkForCrudAction')
            ->will($this->returnValue(false));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($applicationData));

        $this->controller->expects($this->once())
            ->method('notFoundAction')
            ->will($this->returnValue(404));

        $this->assertEquals(404, $this->controller->indexAction());
    }

    /**
     * Test indexAction with 0 results
     */
    public function testIndexActionWithoutResults()
    {
        /**$applicationId = 7;

        $applicationData = array();

        $aocData = array(
            'Count' => 0,
            'Results' => array()
        );

        $this->getMockController(array('checkForCrudAction', 'params', 'makeRestCall'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('checkForCrudAction')
            ->will($this->returnValue(false));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($applicationData));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('ApplicationOperatingCentre', 'GET')
            ->will($this->returnValue($aocData));

        $mockServiceLocator = $this->getMock('\stdClass', array('get'));

        $mockTable = $this->getMock('\stdClass', array('buildTable'));

        $mockTable->expects($this->once())
            ->method('buildTable')
            ->with('operatingcentre', array())
            ->will($this->returnValue('<table></table>'));

        $mockServiceLocator->expects($this->once())
            ->method('get')
            ->with('Table')
            ->will($this->returnValue($mockTable));

        $this->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($mockServiceLocator));

        $this->controller->indexAction();*/
    }

    /**
     * Test completeAction
     */
    public function testCompleteAction()
    {
        $controller = new OperatingCentreController();

        $response = $controller->completeAction();

        $this->assertEquals(null, $response);
    }
}
