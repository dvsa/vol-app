<?php

/**
 * Bus Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace OlcsTest\Controller\Bus;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Bus Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );

        $this->controller = $this->getMock(
            '\Olcs\Controller\Bus\BusController',
            array(
                'redirectToRoute',
                'getServiceLocator',
                'getViewWithBusReg'
            )
        );

        $this->view = $this->getMock(
            '\Zend\View\Model\ViewModel',
            array(
                'getVariables',
            )
        );

        $this->layout = $this->getMock(
            '\Zend\View\Model\ViewModel',
            array(
                'setTemplate',
                'addChild'
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
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('licence/bus-details'),
                $this->equalTo([]),
                $this->equalTo([]),
                $this->equalTo(true)
            );

        $this->controller->indexAction();
    }

    public function testRenderView()
    {
        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getServiceLocatorNavigation()));

        $this->controller->expects($this->once())
            ->method('getViewWithBusReg')
            ->will($this->returnValue($this->layout));

        $this->view->expects($this->once())
            ->method('getVariables');

        $this->layout->expects($this->once())
            ->method('setTemplate');

        $this->layout->expects($this->once())
            ->method('addChild')
            ->with($this->view, 'content');

        $this->controller->renderView($this->view, null, null);
    }

    /**
     * Gets a mock version of translator
     */
    private function getServiceLocatorNavigation()
    {
        $navigationMock = $this->getMock('\stdClass', array('findOneBy'));
        $navigationMock->expects($this->once())
            ->method('findOneBy');

        $serviceMock = $this->getMock('\stdClass', array('get'));
        $serviceMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo('Navigation'))
            ->will($this->returnValue($navigationMock));

        return $serviceMock;
    }
}
