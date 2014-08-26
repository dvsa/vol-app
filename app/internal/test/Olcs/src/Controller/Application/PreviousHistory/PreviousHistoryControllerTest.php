<?php

/**
 * PreviousHistory Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Application\PreviousHistory;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * PreviousHistory Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PreviousHistoryControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\Common\Controller\Application\PreviousHistory\PreviousHistoryController';

    protected $defaultRestResponse = array();

    /**
     * Test indexAction
     */
    public function testIndexAction()
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

            return $this->getApplicationCompletionData();
        }
    }
}
