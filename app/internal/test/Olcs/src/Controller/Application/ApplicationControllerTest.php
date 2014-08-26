<?php

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Application;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\Common\Controller\Application\ApplicationController';

    protected $defaultRestResponse = array();

    private $lastSection = null;

    /**
     * Test that getNamespaceParts does what is expected
     */
    public function testGetNamespaceParts()
    {
        $controller = new \Common\Controller\Application\ApplicationController();
        $parts = $controller->getNamespaceParts();

        $expected = array(
            'Common',
            'Controller',
            'Application',
            'ApplicationController'
        );

        $this->assertEquals($expected, $parts);
    }

    /**
     * Test processDataMap without map
     */
    public function testProcessDataMapForSaveWithoutMap()
    {
        $input = array(
            'foo' => 'bar'
        );

        $controller = new \Common\Controller\Application\ApplicationController();
        $output = $controller->processDataMapForSave($input);

        $this->assertEquals($input, $output);
    }

    /**
     * Test processDataMap
     */
    public function testProcessDataMapForSave()
    {
        $input = array(
            'foo' => array(
                'a' => 'A',
                'b' => 'B'
            ),
            'bar' => array(
                'c' => 'C',
                'd' => 'D'
            ),
            'bob' => array(
                'e' => 'E',
                'f' => 'F'
            )
        );

        $map = array(
            'main' => array(
                'mapFrom' => array('foo', 'bar'),
                'values' => array('cake' => 'cats'),
                'children' => array(
                    'bobs' => array(
                        'mapFrom' => array('bob')
                    )
                )
            )
        );

        $expected = array(
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
            'cake' => 'cats',
            'bobs' => array(
                'e' => 'E',
                'f' => 'F'
            )
        );

        $controller = new \Common\Controller\Application\ApplicationController();
        $output = $controller->processDataMapForSave($input, $map);

        $this->assertEquals($expected, $output);
    }

    /**
     * Test indexAction
     */
    public function testIndexAction()
    {
        $this->setUpAction('index');

        $this->lastSection = 'Application/YourBusiness/BusinessDetails';

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction without last section
     */
    public function testIndexActionWithoutLastSection()
    {
        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Mock the rest call
     *
     * @param string $service
     * @param string $method
     * @param array $data
     * @param array $bundle
     */
    protected function mockRestCalls($service, $method, $data = array(), $bundle = array())
    {
        if ($service == 'Application' && $method == 'GET' && $bundle == ApplicationController::$licenceDataBundle) {

            return $this->getLicenceData('goods');
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return $this->getApplicationCompletionData($this->lastSection);
        }
    }
}
