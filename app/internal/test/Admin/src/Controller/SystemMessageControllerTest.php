<?php

/**
 * Test SystemMessageController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace AdminTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Test SystemMessageController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class SystemMessageControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->markTestSkipped();
        $this->controller = $this->getMock(
            '\Admin\Controller\SystemMessageController',
            [
                'getView'
            ]
        );

        $this->view = $this->getMock(
            'Zend\View\Model\ViewModel',
            [
                'setTemplate'
            ]
        );
    }

    public function testIndexAction()
    {
        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('placeholder');

        $this->assertSame($this->view, $this->controller->indexAction());
    }
}
