<?php

/**
 * Test ReportController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace AdminTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;

/**
 * Test ReportController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ReportControllerTest extends AbstractHttpControllerTestCase
{
    protected $sm;

    public function setUp()
    {
        $this->markTestSkipped();
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

        $this->sm = Bootstrap::getServiceManager();

        $this->controller->setServiceLocator($this->sm);
    }

    public function testIndexAction()
    {
        // mock out navigation
        $mockNavContainer = $this->getMock('\stdClass', ['set']);
        $mockPlaceholder = $this->getMock('\stdClass', ['getContainer']);
        $mockVhm = $this->getMock('\stdClass', ['get']);
        $mockVhm->expects($this->once())
            ->method('get')
            ->with('placeholder')
            ->will($this->returnValue($mockPlaceholder));
        $mockPlaceholder->expects($this->once())
            ->method('getContainer')
            ->with('navigationId')
            ->will($this->returnValue($mockNavContainer));
        $mockNavContainer->expects($this->once())
            ->method('set')
            ->with('admin-dashboard/admin-report');
        $this->sm->setService('viewHelperManager', $mockVhm);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->controller->indexAction());
    }
}
