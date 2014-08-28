<?php

/**
 * Public Inquiry Controller tests
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Mvc\Controller\Plugin\Redirect as Redirect;

/**
 * Public Inquiry Controller tests
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class CasePiControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\CasePiController',
            array(
                'redirect',
                'getView'
            )
        );

        $view = $this->getMock(
            'Zend\View\Model\ViewModel',
            [
                'setVariables',
                'setTemplate'
            ]
        );

        $mockRedirect = $this->getMock(get_class(new Redirect()), ['toRoute', 'toUrl']);

        $this->controller->expects($this->any())
                         ->method('getView')
                         ->will($this->returnValue($view));
        $this->controller->expects($this->any())
                         ->method('redirect')
                         ->will($this->returnValue($mockRedirect));

        parent::setUp();
    }

    public function testIndexAction()
    {
        $this->markTestSkipped();
    }
}
