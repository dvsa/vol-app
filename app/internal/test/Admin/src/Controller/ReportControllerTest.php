<?php

/**
 * Test ReportController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace AdminTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Test ReportController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ReportControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->controller = $this->getMock(
            '\Admin\Controller\ReportController',
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
            ->with('admin/page/report');

        $this->assertSame($this->view, $this->controller->indexAction());
    }
}
