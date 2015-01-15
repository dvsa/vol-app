<?php

/**
 * Description of OlcsIndexControllerTest
 *
 * @author adminmwc
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use CommonTest\Traits\MockDateTrait;

/**
 * Class OlcsIndexControllerTest
 * @package OlcsTest\Controller
 */
class OlcsIndexControllerTest extends AbstractHttpControllerTestCase
{
    use MockDateTrait;

    private $taskSearchViewExpectedData = [
        'assignedToUser'  => 1,
        'assignedToTeam'  => 2,
        'date'  => 'tdt_today',
        'status' => 'tst_open',
        'sort' => 'actionDate',
        'order' => 'ASC',
        'page' => 1,
        'limit' => 10,
        'actionDate' => '',
        'isClosed' => false
    ];

    private $taskSearchViewExpectedDataVar1 = [
        'assignedToTeam'  => 2,
        'date'  => 'tdt_today',
        'status' => 'tst_open',
        'sort' => 'actionDate',
        'order' => 'ASC',
        'page' => 1,
        'limit' => 10,
        'actionDate' => '',
        'isClosed' => false
    ];

    private $standardListData = [
        'limit' => 100,
        'sort' => 'name'
    ];

    private $extendedListData = [
        'assignedToUser' => 1,
        'assignedToTeam'  => 2,
        'team'  => 2,
        'date'  => 'tdt_today',
        'status' => 'tst_open',
        'sort' => 'name',
        'order' => 'ASC',
        'page' => 1,
        'limit' => 100,
        'actionDate' => '',
        'isClosed' => false
    ];

    private $extendedListDataVariation1 = [
        'assignedToTeam'  => 2,
        'date'  => 'tdt_today',
        'status' => 'tst_open',
        'sort' => 'name',
        'order' => 'ASC',
        'page' => 1,
        'limit' => 100,
        'actionDate' => '',
        'team'  => 2,
        'isClosed' => false
    ];

    private $altListData = [
        'limit' => 100,
        'sort' => 'description'
    ];

    private $userList = [
        'team' => 123,
        'limit' => 100,
        'sort' => 'loginId'
    ];

    /**
     * @var Zend\ServiceManager\ServiceManager
     */
    protected $sm;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\IndexController',
            array(
                'makeRestCall',
                'getLoggedInUser',
                'getTable',
                'getRequest',
                'getForm',
                'loadScripts',
                'params',
                'getFromRoute',
                'redirect',
                'getSearchForm'
            )
        );

        $query = new \Zend\Stdlib\Parameters();
        $request = $this->getMock('\stdClass', ['getQuery', 'isXmlHttpRequest', 'isPost']);
        $request->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->query = $query;
        $this->request = $request;

        $this->controller->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnCallback(array($this, 'mockRestCall')));

        $this->sm = \OlcsTest\Bootstrap::getServiceManager();

        $this->controller->setServiceLocator($this->sm);

        // mock date
        $date = '2014-12-14';
        $this->mockDate($date);

        $this->taskSearchViewExpectedData['actionDate'] = '<= 2014-12-14';
        $this->extendedListData['actionDate'] = '<= 2014-12-14';
        $this->extendedListDataVariation1['actionDate'] = '<= 2014-12-14';
        $this->taskSearchViewExpectedDataVar1['actionDate'] = '<= 2014-12-14';
        parent::setUp();
    }

    /**
     * Test index acton with no query
     * @group task
     */
    public function testIndexActionWithNoQueryUsesDefaultParams()
    {
        $this->controller->expects($this->any())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $tableMock = $this->getMock('\stdClass', ['render', 'getSettings', 'setSettings']);

        $settings = [
            'crud' => [
                'actions' => [
                    'create task' => [],
                    'edit' => []
                ]
            ]
        ];
        $tableMock->expects($this->once())
                ->method('getSettings')
                ->will($this->returnValue($settings));

        $this->controller->expects($this->once())
            ->method('getTable')
            ->with(
                'tasks',
                [],
                array_merge(
                    $this->taskSearchViewExpectedData,
                    array('query' => $this->query)
                )
            )
            ->will($this->returnValue($tableMock));

        $tableMock->expects($this->once())
            ->method('render');

        $form = $this->getMock('\stdClass', ['get', 'setValueOptions', 'remove', 'setData']);

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->setUpAction('');
        $view = $this->controller->indexAction();
        list($header, $content) = $view->getChildren();

        $this->assertEquals('Home', $header->getVariable('pageTitle'));
        $this->assertEquals('', $header->getVariable('pageSubTitle'));
    }

    /**
     * Test index acton with AJAX
     * @group task
     */
    public function testIndexActionAjax()
    {
        $this->setUpAction('');

        $form = $this->getMock('\stdClass', ['get', 'setValueOptions', 'remove', 'setData']);

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $tableMock = $this->getMock('\stdClass', ['render', 'getSettings', 'setSettings']);
        $settings = [
            'crud' => [
                'actions' => [
                    'create task' => [],
                    'edit' => []
                ]
            ]
        ];
        $tableMock->expects($this->once())
                ->method('getSettings')
                ->will($this->returnValue($settings));

        $this->controller->expects($this->once())
            ->method('getTable')
            ->will($this->returnValue($tableMock));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->request->expects($this->once())
            ->method('isXmlHttpRequest')
            ->will($this->returnValue(true));

        $view = $this->controller->indexAction();

        $this->assertTrue($view->terminate());
    }

    /**
     * Test entity filter action invalid type
     */
    public function testEntityFilterActionInvalidType()
    {
        $params = $this->getMock('\stdClass', ['fromRoute']);

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $params->expects($this->at(0))
            ->method('fromRoute')
            ->with('type')
            ->will($this->returnValue('invalid'));

        try {
            $this->controller->entityListAction();
        } catch (\Exception $e) {
            $this->assertEquals('Invalid entity filter key: invalid', $e->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    /**
     * Test entity filter action valid type
     */
    public function testEntityListActionValidType()
    {
        $params = $this->getMock('\stdClass', ['fromRoute']);

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $params->expects($this->at(0))
            ->method('fromRoute')
            ->with('type')
            ->will($this->returnValue('users'));

        $params->expects($this->at(1))
            ->method('fromRoute')
            ->with('value')
            ->will($this->returnValue('123'));

        $expectedData = [
            [
                'value' => '',
                'label' => 'All'
            ], [
                'value' => 123,
                'label' => 'foo'
            ], [
                'value' => 456,
                'label' => 'bar'
            ]
        ];

        $json = $this->controller->entityListAction();

        $this->assertEquals($expectedData, $json->getVariables());
    }

    /**
     * Test index action with multiple reassign submitted
     * @group task
     */
    public function testIndexActionWithMultipleReassignSubmitted()
    {
        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->with('licence')
            ->will($this->returnValue(1));

        $params = $this->getMock('\stdClass', ['fromPost']);

        $params->expects($this->at(0))
            ->method('fromPost')
            ->will($this->returnValue('re-assign task'));

        $params->expects($this->at(1))
            ->method('fromPost')
            ->will($this->returnValue([1, 2]));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $routeParams = [
            'action' => 'reassign',
            'task'  => '1-2'
        ];
        $mockRoute = $this->getMock('\stdClass', ['toRoute']);
        $mockRoute->expects($this->once())
            ->method('toRoute')
            ->with('task_action', $routeParams)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $response = $this->controller->indexAction();

        $this->assertEquals('mockResponse', $response);
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
        $standardResponse = ['Results' => [['id' => 123,'name' => 'foo']]];
        $altResponse = ['Results' => [['id' => 123,'description' => 'foo']]];
        $userListResponse = [
            'Results' => [
                [
                    'id' => 123,
                    'loginId' => 'foo'
                ],
                [
                    'id' => 456,
                    'loginId' => 'bar'
                ]
            ]
        ];

        if ($service == 'TaskSearchView' && $method == 'GET' && $data == $this->taskSearchViewExpectedData) {
            return [];
        }
        if ($service == 'TaskSearchView' && $method == 'GET' && $data == $this->taskSearchViewExpectedDataVar1) {
            return [];
        }
        if ($service == 'Team' && $method == 'GET' && $data == $this->standardListData) {
            return $standardResponse;
        }
        if ($service == 'User' && $method == 'GET' && $data == $this->extendedListData) {
            return $standardResponse;
        }
        if ($service == 'User' && $method == 'GET' && $data == $this->extendedListDataVariation1) {
            return $standardResponse;
        }
        if ($service == 'User' && $method == 'GET' && $data == $this->userList) {
            return $userListResponse;
        }
        if ($service == 'TaskSubCategory' && $method == 'GET' && $data == $this->extendedListData) {
            return $standardResponse;
        }
        if ($service == 'TaskSubCategory' && $method == 'GET' && $data == $this->extendedListDataVariation1) {
            return $standardResponse;
        }
        if ($service == 'Category' && $method == 'GET' && $data == $this->altListData) {
            return $altResponse;
        }
    }

    public function setUpAction($action = '')
    {
        $paramsMock = $this->getMock('\StdClass', array('fromPost'));

        $paramsMock->expects($this->any())
                ->method('fromPost')
                ->with('action')
                ->will($this->returnValue($action));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($paramsMock));
    }
}
