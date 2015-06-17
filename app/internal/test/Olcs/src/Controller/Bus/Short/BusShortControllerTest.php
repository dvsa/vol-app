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
                'redirectToRoute'
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
