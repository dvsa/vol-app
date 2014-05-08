<?php

/**
 * Test Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller;

use PHPUnit_Framework_TestCase;

/**
 * Test Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractApplicationControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Build a mock controller
     *
     * @param array $methods
     */
    protected function getMockController($methods = array())
    {
        $this->controller = $this->getMockForAbstractClass(
            'SelfServe\Controller\AbstractApplicationController',
            array(),
            '',
            true,
            true,
            true,
            $methods
        );
    }

    /**
     * Test isButtonPressed without post
     */
    public function testIsButtonPressedWithoutPost()
    {
        $button = 'cancel';

        $mockRequest = $this->getMock('\stdClass', array('isPost'));

        $mockRequest->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(false));

        $this->getMockController(array('getRequest'));

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));

        $this->assertEquals(false, $this->controller->isButtonPressed($button));
    }

    /**
     * Test isButtonPressed Without button pressed
     */
    public function testIsButtonPressedWithoutButtonPressed()
    {
        $button = 'cancel';

        $post = array(
            'form-actions' => array(
                'add' => 'foo'
            )
        );

        $mockRequest = $this->getMock('\stdClass', array('isPost', 'getPost'));

        $mockRequest->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));

        $mockRequest->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue($post));

        $this->getMockController(array('getRequest'));

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));

        $this->assertEquals(false, $this->controller->isButtonPressed($button));
    }

    /**
     * Test isButtonPressed
     */
    public function testIsButtonPressed()
    {
        $button = 'cancel';

        $post = array(
            'form-actions' => array(
                'cancel' => 'foo'
            )
        );

        $mockRequest = $this->getMock('\stdClass', array('isPost', 'getPost'));

        $mockRequest->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));

        $mockRequest->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue($post));

        $this->getMockController(array('getRequest'));

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));

        $this->assertEquals(true, $this->controller->isButtonPressed($button));
    }
}
