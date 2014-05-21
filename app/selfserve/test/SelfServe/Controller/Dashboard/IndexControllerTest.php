<?php

/**
 * Index Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Test\Controller\Dashboard;

use SelfServe\Test\Bootstrap;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;

/**
 * Index Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class IndexControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * SetUp the controller
     */
    public function setUpAction($action = 'index')
    {
        $this->controller = $this->getMock(
            'SelfServe\Controller\Dashboard\IndexController',
            array('makeRestCall')
        );

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnCallback(array($this, 'mockRestCall')));

        $serviceManager = Bootstrap::getServiceManager();

        $this->request = new Request();
        $this->response = new Response();
        $this->routeMatch = new RouteMatch(array('action' => $action));

        $this->routeMatch->setMatchedRouteName('home/dashboard');

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
    }

    /**
     * Test indexAction
     */
    public function testIndexAction()
    {
        $this->setUpAction();

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test createAction
     */
    public function testCreateAction()
    {
        $this->setUpAction('create');

        $response = $this->controller->createAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Mock rest calls
     *
     * @param string $service
     * @param string $method
     * @param array $data
     * @param array $bundle
     */
    public function mockRestCall($service, $method, $data = array(), $bundle = array())
    {
        if ($method == 'POST') {
            return array('id' => 1);
        }

        $organisationIdBundle = array(
            'properties' => array(

            ),
            'children' => array(
                'organisation' => array(
                    'properties' => array('id')
                )
            )
        );

        if ($service == 'User' && $method == 'GET' && $bundle == $organisationIdBundle) {
            return array(
                'organisation' => array(
                    'id' => 1
                )
            );
        }

        $applicationsBundle = array(
            'properties' => array(),
            'children' => array(
                'organisation' => array(
                    'properties' => array(),
                    'children' => array(
                        'licences' => array(
                            'properties' => array(
                                'licenceNumber'
                            ),
                            'children' => array(
                                'applications' => array(
                                    'properties' => array(
                                        'id',
                                        'createdOn',
                                        'receivedDate',
                                        'status'
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        if ($service == 'User' && $method == 'GET' && $bundle == $applicationsBundle) {
            return array(
                'organisation' => array(
                    'licences' => array(
                        array(
                            'licenceNumber' => 123,
                            'applications' => array(
                                array(
                                    'id' => 1,
                                    'createdOn' => '2014-01-01 00:00:00',
                                    'receivedDate' => '2014-01-01 00:00:00',
                                    'status' => 'app_status.new'
                                )
                            )
                        )
                    )
                )
            );
        }
    }
}
