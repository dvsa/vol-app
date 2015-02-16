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
                'getDataBundle',
                'getQueryOrRouteParam',
                'redirectToRoute'
            )
        );

        parent::setUp();
    }

    /**
     * Tests process load for an existing record.
     */
    public function testProcessLoadWithId()
    {
        $bundle = array(
            'properties' => 'ALL'
        );

        $data = array(
            'Results' => array(
                0 => array(
                    'id' => 1
                )
            )
        );

        $result = array(
            'id' => '1'
        );

        $result['fields'] = $result;
        $result['base'] = $result;

        $this->controller->expects($this->once())->method('getDataBundle')
            ->will($this->returnValue($bundle));

        $this->assertEquals($result, $this->controller->processLoad($data));
    }

    /**
     * Tests the process load function where no Id is found.
     */
    public function testProcessLoadWithoutId()
    {
        $data = array();

        $result = array('case' => null);
        $result['fields']['case'] = null;
        $result['base']['case'] = null;

        $this->controller->expects($this->once())->method('getQueryOrRouteParam')
            ->with('case')->will($this->returnValue(null));

        $this->assertEquals($result, $this->controller->processLoad($data));
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
