<?php

/**
 * Bus Details Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace OlcsTest\Controller\Bus\Details;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Bus Details Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../../config/application.config.php'
        );

        $this->controller = $this->getMock(
            '\Olcs\Controller\Bus\Details\BusDetailsController',
            array(
                'getViewWithBusReg',
                'renderView',
                'redirectToRoute',
                'isFromEbsr',
                'isLatestVariation'
            )
        );

        $this->form = $this->getMock(
            '\Zend\Form\Form',
            array(
                'setOption'
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

    public function testRedirectToIndex()
    {
        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo(null),
                $this->equalTo(['action'=>'edit']),
                $this->equalTo(['code' => '303']),
                $this->equalTo(true)
            );

        $this->controller->redirectToIndex();
    }
}
