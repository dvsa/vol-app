<?php

/**
 * Test Declarations Index Controller
 */

namespace SelfServe\test\Controller\Declarations;

use PHPUnit_Framework_TestCase;
use SelfServe\Controller\Declarations\IndexController;

/**
 * Test Declarations Index Controller
 */
class IndexControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Build a mock controller
     *
     * @param array $methods
     */
    protected function createMockController($methods = array())
    {
        $this->controller = $this->getMock(
            'SelfServe\Controller\Declarations\IndexController',
            $methods
        );
    }

    public function testConstructorSetsCurrentSection()
    {
        // we have to opt-out of our standard mock builder here
        // as we want to disable the constructor ahead of
        // setting our expectations
        $className = 'SelfServe\Controller\Declarations\IndexController';
        $stub = $this->getMockBuilder($className)
            ->setMethods(['setCurrentSection'])
            ->disableOriginalConstructor()
            ->getMock();

        $stub->expects($this->once())
            ->method('setCurrentSection')
            ->with('declarations');

        $stub->__construct();
    }

    public function testIndexActionWithInvalidApplication()
    {
        $this->createMockController(
            ['makeRestCall', 'notFoundAction']
        );

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(null));

        $this->controller->expects($this->once())
            ->method('notFoundAction')
            ->will($this->returnValue(404));

        $this->assertEquals(404, $this->controller->indexAction());
    }

    public function testIndexActionWithNILicence()
    {
    }

    public function testIndexActionWithNonNILicence()
    {
    }
}
