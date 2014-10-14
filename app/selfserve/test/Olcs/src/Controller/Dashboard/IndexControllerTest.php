<?php

/**
 * Index Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Dashboard;

use OlcsTest\Bootstrap;
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
    private $organisationUserResponse;

    /**
     * SetUp the controller
     */
    public function setUpAction($action = 'index')
    {
        $this->controller = $this->getMock(
            'Olcs\Controller\Dashboard\IndexController',
            array('makeRestCall')
        );

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnCallback(array($this, 'mockRestCall')));

        $serviceManager = Bootstrap::getServiceManager();

        $this->request = new Request();
        $this->response = new Response();
        $this->routeMatch = new RouteMatch(array('action' => $action));

        $this->routeMatch->setMatchedRouteName('application_start');

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
     * Test createAction without organisation
     *
     * @expectedException \Exception
     */
    public function testCreateActionWithoutOrganisation()
    {
        $this->organisationUserResponse = array('Count' => 0);

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

        if ($service == 'OrganisationUser' && $method == 'GET' && $bundle == $organisationIdBundle) {

            if (empty($this->organisationUserResponse)) {
                return array(
                    'Count' => 1,
                    'Results' => array(
                        array(
                            'organisation' => array(
                                'id' => 1
                            )
                        )
                    )
                );
            } else {
                return $this->organisationUserResponse;
            }
        }

        $applicationsBundle = array(
            'properties' => array(),
            'children' => array(
                'organisationUsers' => array(
                    'properties' => null,
                    'children' => array(
                        'organisation' => array(
                            'properties' => array(),
                            'children' => array(
                                'licences' => array(
                                    'properties' => array(
                                        'id',
                                        'licNo'
                                    ),
                                    'children' => array(
                                        'applications' => array(
                                            'properties' => array(
                                                'id',
                                                'createdOn',
                                                'receivedDate',
                                                'isVariation'
                                            ),
                                            'children' => array(
                                                'status' => array(
                                                    'properties' => array(
                                                        'id'
                                                    )
                                                )
                                            )
                                        ),
                                        'licenceType' => array(
                                            'properties' => array(
                                                'id',
                                                'description'
                                            )
                                        ),
                                        'status' => array(
                                            'properties' => array(
                                                'id',
                                                'description'
                                            )
                                        )
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
                'organisationUsers' => array(
                    array(
                        'organisation' => array(
                            'licences' => array(
                                array(
                                    'id' => 1,
                                    'licNo' => 123,
                                    'applications' => array(
                                        array(
                                            'id' => 1,
                                            'createdOn' => '2014-01-01 00:00:00',
                                            'receivedDate' => '2014-01-01 00:00:00',
                                            'status' => array(
                                                'id' => 'apsts_new'
                                            ),
                                            'isVariation' => false
                                        )
                                    ),
                                    'licenceType' => array(
                                        'id' => 'ltyp_sn',
                                        'description' => 'blah'
                                    ),
                                    'status' => array(
                                        'id' => 'lsts_new',
                                        'description' => 'blah'
                                    ),
                                ),
                                array(
                                    'id' => 2,
                                    'licNo' => 456,
                                    'applications' => array(
                                        array(
                                            'id' => 1,
                                            'createdOn' => '2014-01-01 00:00:00',
                                            'receivedDate' => '2014-01-01 00:00:00',
                                            'status' => array(
                                                'id' => 'apsts_new'
                                            ),
                                            'isVariation' => true
                                        )
                                    ),
                                    'licenceType' => array(
                                        'id' => 'ltyp_sn',
                                        'description' => 'blah'
                                    ),
                                    'status' => array(
                                        'id' => 'lsts_new',
                                        'description' => 'blah'
                                    ),
                                )
                            )
                        )
                    )
                )
            );
        }
    }
}
