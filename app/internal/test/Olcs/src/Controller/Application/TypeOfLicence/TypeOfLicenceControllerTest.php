<?php

/**
 * TypeOfLicence Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Application\TypeOfLicence;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * TypeOfLicence Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\Common\Controller\Application\TypeOfLicence\TypeOfLicenceController';

    protected $defaultRestResponse = array();

    /**
     * Test indexAction
     */
    public function testIndexAction()
    {
        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a response which redirects us to the first
        // sub section
        $this->assertInstanceOf('Zend\Http\Response', $response);
        $this->assertEquals(302, $response->getStatusCode());
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

            return $this->getApplicationCompletionData();
        }
    }
}
