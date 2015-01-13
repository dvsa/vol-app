<?php

/**
 * Bus Trc Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace OlcsTest\Controller\Bus\Trc;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Bus Trc Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusTrcControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../../config/application.config.php'
        );

        $this->controller = $this->getMock(
            '\Olcs\Controller\Bus\Trc\BusTrcController',
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
        $this->assertTrue(true);
    }
}
