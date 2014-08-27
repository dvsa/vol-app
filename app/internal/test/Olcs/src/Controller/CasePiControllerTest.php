<?php

/**
 * Public Inquiry Controller tests
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

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

        $this->view = $this->getMock(
            'Zend\View\Model\ViewModel',
            [
                'setVariables',
                'setTemplate'
            ]
        );
        $this->controller->expects($this->any())
                         ->method('getView')
                         ->will($this->returnValue($this->view));
        $this->controller->expects($this->any())
                         ->method('redirect')
                         ->will($this->returnValue($this->getMockRedirect()));

        parent::setUp();
    }

    //
}
