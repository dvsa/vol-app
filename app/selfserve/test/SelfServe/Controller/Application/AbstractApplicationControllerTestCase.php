<?php

/**
 * AbstractApplicationControllerTestCase
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Test\Controller\Application;

use SelfServe\Test\Bootstrap;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use SelfServe\Controller\Application\ApplicationController;
use Zend\View\Model\ViewModel;

/**
 * AbstractApplicationControllerTestCase
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractApplicationControllerTestCase extends PHPUnit_Framework_TestCase
{
    protected $controllerName = '';
    protected $defaultRestResponse = array();
    protected $restResponses = array();
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;
    protected $mockedMethods = array();

    /**
     * Reset all
     */
    protected function tearDown()
    {
        $this->controller = null;
        $this->request = null;
        $this->routeMatch = null;
        $this->event = null;
        $this->restResponses = $this->defaultRestResponse;
    }

    /**
     * Override a rest response
     *
     * @param string $service
     * @param string $method
     * @param mixed $response
     */
    protected function setRestResponse($service, $method, $response = null)
    {
        $this->restResponses[$service][$method] = $response;
    }

    /**
     * Setup an action
     *
     * @param string $action
     * @param int $id
     * @param array $data
     */
    protected function setUpAction($action = 'index', $id = null, $data = array(), $files = array())
    {
        $this->tearDown();

        $methods = array_merge($this->mockedMethods, array('makeRestCall', 'getNamespaceParts'));

        $this->controller = $this->getMock(
            $this->controllerName,
            $methods
        );

        $this->controller->expects($this->any())
            ->method('getNamespaceParts')
            ->will($this->returnValue(explode('\\', trim($this->controllerName, '\\'))));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnCallback(array($this, 'mockRestCall')));

        $serviceManager = Bootstrap::getServiceManager();

        $this->request = new Request();
        $this->response = new Response();
        $this->routeMatch = new RouteMatch(
            array(
            'controller' => trim($this->controllerName, '\\'),
            'action' => $action,
            'applicationId' => 1,
            'id' => $id
            )
        );

        $routeName = str_replace(
            array('\\SelfServe\\Controller\\', 'Controller', '\\'),
            array('', '', '/'),
            $this->controllerName
        );

        $this->routeMatch->setMatchedRouteName($routeName);

        $this->event = new MvcEvent();
        $config = $serviceManager->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->event->setRequest($this->request);
        $this->event->setResponse($this->response);

        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($serviceManager);

        if (!empty($data)) {

            $post = new \Zend\Stdlib\Parameters($data);

            $this->controller->getRequest()->setMethod('post')->setPost($post);
        }

        if (!empty($files)) {

            $files = new \Zend\Stdlib\Parameters($files);

            $this->controller->getRequest()->setFiles($files);
        }
    }

    /**
     * Mock the rest call
     *
     * @param string $service
     * @param string $method
     * @param array $data
     * @param array $bundle
     */
    public function mockRestCall($service, $method, $data = array(), $bundle = array())
    {
        if ($method == 'PUT' || $method == 'DELETE') {
            return null;
        }

        if (isset($this->restResponses[$service][$method])) {
            return $this->restResponses[$service][$method];
        }

        return $this->mockRestCalls($service, $method, $data, $bundle);
    }

    /**
     * Get form from response
     *
     * @param \Zend\View\Model\ViewModel $view
     */
    protected function getFormFromView($view)
    {
        if ($view instanceof ViewModel) {
            // We should have 2 children (Navigation and Main)
            $children = $view->getChildren();
            $this->assertEquals(2, count($children));

            $main = null;
            $navigation = null;

            foreach ($children as $child) {
                if ($child->captureTo() == 'navigation') {
                    $navigation = $child;
                    continue;
                }

                if ($child->captureTo() == 'main') {
                    $main = $child;
                }
            }

            return $main->getVariable('form');
        }

        $this->fail('Trying to get form of a Response object instead of a ViewModel');
    }

    /**
     * Abstract mock rest calls method
     *
     * @param string $service
     * @param string $method
     * @param array $data
     * @param array $bundle
     */
    abstract protected function mockRestCalls($service, $method, $data, $bundle);

    /**
     * Get licence data
     *
     * @param string $goodsOrPsv
     * @return array
     */
    protected function getLicenceData($goodsOrPsv = 'goods', $licenceType = 'ltyp_sn', $niFlag = 'N')
    {
        return array(
            'licence' => array(
                'id' => 10,
                'version' => 1,
                'goodsOrPsv' => array(
                    'id' => ($goodsOrPsv == 'goods' ? 'lcat_gv' : 'lcat_psv')
                ),
                'niFlag' => $niFlag,
                'licenceType' => array(
                    'id' => $licenceType
                ),
                'organisation' => array(
                    'type' => array(
                        'id' => ApplicationController::ORG_TYPE_REGISTERED_COMPANY
                    )
                )
            )
        );
    }

    /**
     * Get application completion data
     *
     * @return type
     */
    protected function getApplicationCompletionData($lastSection = '')
    {
        return array(
            'id' => '1',
            'version' => 1,
            'sectionTypeOfLicenceStatus' => 2,
            'sectionTypeOfLicenceOperatorLocationStatus' => 2,
            'sectionTypeOfLicenceOperatorTypeStatus' => 2,
            'sectionTypeOfLicenceLicenceTypeStatus' => 2,
            'sectionYourBusinessStatus' => 2,
            'sectionYourBusinessBusinessTypeStatus' => 2,
            'sectionYourBusinessBusinessDetailsStatus' => 2,
            'sectionYourBusinessAddressesStatus' => 2,
            'sectionYourBusinessPeopleStatus' => 2,
            'sectionTaxiPhvStatus' => 2,
            'sectionOperatingCentresStatus' => 2,
            'sectionOperatingCentresAuthorisationStatus' => 2,
            'sectionOperatingCentresFinancialEvidenceStatus' => 2,
            'sectionTransportManagersStatus' => 2,
            'sectionVehicleSafetyStatus' => 2,
            'sectionVehicleSafetyVehicleStatus' => 2,
            'sectionVehicleSafetySafetyStatus' => 2,
            'sectionPreviousHistoryStatus' => 2,
            'sectionPreviousHistoryFinancialHistoryStatus' => 2,
            'sectionPreviousHistoryLicenceHistoryStatus' => 2,
            'sectionPreviousHistoryConvictionPenaltiesStatus' => 2,
            'sectionReviewDeclarationsStatus' => 2,
            'sectionPaymentSubmissionStatus' => 0,
            'sectionPaymentSubmissionPaymentStatus' => 0,
            'sectionPaymentSubmissionSummaryStatus' => 0,
            'lastSection' => $lastSection
        );
    }
}
