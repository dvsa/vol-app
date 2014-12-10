<?php

/**
 * Bus Docs Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace OlcsTest\Controller\Bus\Docs;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Bus Docs Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDocsControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../../config/application.config.php'
        );

        $this->controller = $this->getMock(
            '\Olcs\Controller\Bus\Docs\BusDocsController',
            array(
                'getViewWithBusReg',
                'renderView'
            )
        );

        $this->view = $this->getMock(
            '\Zend\View\Model\ViewModel',
            array(
                'setTemplate'
            )
        );

        parent::setUp();
    }

    public function testDocumentsAction()
    {
        $this->markTestIncomplete();
        $this->controller->expects($this->once())
            ->method('getViewWithBusReg')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('licence/bus/index');

        $this->controller->expects($this->once())
            ->method('renderView')
            ->with($this->view);

        $this->controller->indexAction();
    }
}
