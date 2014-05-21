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
    protected function setUpAction($action = 'index', $id = null, $data = array())
    {
        $this->tearDown();

        $this->controller = $this->getMock(
            $this->controllerName,
            array('makeRestCall', 'getNamespaceParts')
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
     * @param Response $response
     */
    protected function getFormFromResponse($response)
    {
        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
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

    /**
     * Abstract mock rest calls method
     *
     * @param string $service
     * @param string $method
     * @param array $data
     * @param array $bundle
     */
    abstract protected function mockRestCalls($service, $method, $data, $bundle);
}
