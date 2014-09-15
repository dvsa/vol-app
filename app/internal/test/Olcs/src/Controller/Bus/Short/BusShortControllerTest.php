<?php

/**
 * Bus Short Notice Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace OlcsTest\Controller\Bus\Short;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Bus Short Notice Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusShortControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../../config/application.config.php'
        );

        $this->controller = $this->getMock(
            '\Olcs\Controller\Bus\Short\BusShortController',
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

    /**
     * Placeholder unit test for index action
     */
    public function testIndexAction()
    {
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
