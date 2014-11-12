<?php

/**
 * Licence controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Licence\Processing;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Common\Service\Entity\LicenceEntityService;

/**
 * Licence controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceProcessingTasksControllerTest extends AbstractHttpControllerTestCase
{
    private $taskSearchViewExpectedData = [
        'date'  => 'tdt_today',
        'status' => 'tst_open',
        'sort' => 'actionDate',
        'order' => 'ASC',
        'page' => 1,
        'limit' => 10,
        'actionDate' => '',
        'linkId' => 1234,
        'linkType' => 'Licence',
        'isClosed' => false
    ];
    private $standardListData = [
        'limit' => 100,
        'sort' => 'name'
    ];
    private $extendedListData = [
        'date'  => 'tdt_today',
        'status' => 'tst_open',
        'sort' => 'name',
        'order' => 'ASC',
        'page' => 1,
        'limit' => 100,
        'actionDate' => '',
        'linkId' => 1234,
        'linkType' => 'Licence',
        'isClosed' => false
    ];
    private $extendedListDataVariation1 = [
        'date'  => 'tdt_today',
        'status' => 'tst_open',
        'sort' => 'name',
        'order' => 'ASC',
        'page' => 1,
        'limit' => 100,
        'linkType' => 'Licence',
        'actionDate' => '',
        'isClosed' => false
    ];
    private $altListData = [
        'limit' => 100,
        'sort' => 'description'
    ];

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Licence\Processing\LicenceProcessingTasksController',
            array(
                'makeRestCall',
                'getLoggedInUser',
                'getTable',
                'getLicence',
                'getRequest',
                'getForm',
                'loadScripts',
                'getFromRoute',
                'params',
                'redirect',
                'getServiceLocator',
                'getSubNavigation',
                'setTableFilters',
                'getSearchForm',
                'setupMarkers'
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
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getServiceLocatorTranslator()));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnCallback(array($this, 'mockRestCall')));

        $this->taskSearchViewExpectedData['actionDate'] = '<= ' . date('Y-m-d');
        $this->extendedListData['actionDate'] = '<= ' . date('Y-m-d');
        $this->extendedListDataVariation1['actionDate'] = '<= ' . date('Y-m-d');

        parent::setUp();
    }

    /**
     * Gets a mock version of translator
     * @group task
     */
    private function getServiceLocatorTranslator()
    {
        $translatorMock = $this->getMock('\stdClass', array('translate'));
        $translatorMock->expects($this->any())
            ->method('translate')
            ->will($this->returnArgument(0));

        $serviceMock = $this->getMock('\stdClass', array('get'));
        $serviceMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo('translator'))
            ->will($this->returnValue($translatorMock));

        return $serviceMock;
    }

    /**
     * Test index acton with no query
     * @group task
     */
    public function testIndexActionWithNoQueryUsesDefaultParams()
    {

        $licenceData = array(
            'licNo' => 'TEST1234',
            'goodsOrPsv' => array(
                'id' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                'description' => 'PSV'
            ),
            'organisation' => array(
                'name' => 'O1'
            ),
            'status' => array(
                'id' => 'S1',
                'description' => 'S1'
            )
        );

        $this->controller->expects($this->any())
            ->method('getLicence')
            ->will($this->returnValue($licenceData));

        $this->controller->expects($this->any())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->with('licence')
            ->will($this->returnValue(1234));

        $tableMock = $this->getMock('\stdClass', ['render', 'removeColumn']);
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

        $tableMock->expects($this->at(0))
            ->method('removeColumn')
            ->with('name');

        $tableMock->expects($this->at(1))
            ->method('removeColumn')
            ->with('link');

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

        $this->assertEquals('TEST1234', $header->getVariable('pageTitle'));
        $this->assertEquals('O1 S1', $header->getVariable('pageSubTitle'));
    }

    /**
     * Test index action AJAX
     * @group task2
     */
    public function testIndexActionAjax()
    {

        $this->setUpAction('');

        $form = $this->getMock('\stdClass', ['get', 'setValueOptions', 'remove', 'setData']);

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $tableMock = $this->getMock('\stdClass', ['render', 'removeColumn']);

        $tableMock->expects($this->at(0))
            ->method('removeColumn');

        $tableMock->expects($this->at(1))
            ->method('removeColumn');

        $this->controller->expects($this->once())
            ->method('getTable')
            ->will($this->returnValue($tableMock));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->request->expects($this->any())
            ->method('isXmlHttpRequest')
            ->will($this->returnValue(true));

        $view = $this->controller->indexAction();

        $this->assertTrue($view->terminate());
    }

    /**
     * Test index action with add action submitted
     * @group task
     */
    public function testIndexActionWithAddActionSubmitted()
    {
        $this->request->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = $this->getMock('\stdClass', ['fromPost']);

        $params->expects($this->at(0))
            ->method('fromPost')
            ->will($this->returnValue('create task'));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $params = [
            'action' => 'add',
            'type' => 'licence',
            'typeId' => null
        ];
        $mockRoute = $this->getMock('\stdClass', ['toRoute']);
        $mockRoute->expects($this->once())
            ->method('toRoute')
            ->with('task_action', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $response = $this->controller->indexAction();

        $this->assertEquals('mockResponse', $response);
    }

    /**
     * Test index action with multi edit submitted
     * @group task
     */
    public function testIndexActionWithMultiEditSubmitted()
    {
        $this->request->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = $this->getMock('\stdClass', ['fromPost']);

        $params->expects($this->at(0))
            ->method('fromPost')
            ->will($this->returnValue('edit'));

        $params->expects($this->at(1))
            ->method('fromPost')
            ->will($this->returnValue([123, 456]));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));
        try {
            $this->controller->indexAction();
        } catch (\Exception $e) {
            $this->assertEquals('Please select a single task to edit', $e->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    /**
     * Test index action with no edit submitted
     * @group task
     */
    public function testIndexActionWithNoEditSubmitted()
    {
        $this->request->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = $this->getMock('\stdClass', ['fromPost']);

        $params->expects($this->at(0))
            ->method('fromPost')
            ->will($this->returnValue('edit'));

        $params->expects($this->at(1))
            ->method('fromPost')
            ->will($this->returnValue([]));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));
        try {
            $this->controller->indexAction();
        } catch (\Exception $e) {
            $this->assertEquals('Please select a single task to edit', $e->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    /**
     * Test index action with single edit submitted
     * @group task
     */
    public function testIndexActionWithSingleEditSubmitted()
    {
        $this->request->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = $this->getMock('\stdClass', ['fromPost']);

        $params->expects($this->at(0))
            ->method('fromPost')
            ->will($this->returnValue('edit'));

        $params->expects($this->at(1))
            ->method('fromPost')
            ->will($this->returnValue([321]));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $params = [
            'action' => 'edit',
            'task'  => 321,
            'type' => 'licence',
            'typeId' => null
        ];
        $mockRoute = $this->getMock('\stdClass', ['toRoute']);
        $mockRoute->expects($this->once())
            ->method('toRoute')
            ->with('task_action', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $response = $this->controller->indexAction();

        $this->assertEquals('mockResponse', $response);
    }

    /**
     * Test index action with multiple reassign submitted
     * @group task
     */
    public function testIndexActionWithMultipleReassignSubmitted()
    {
        $this->request->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));

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

        $params = [
            'action' => 'reassign',
            'task'  => '1-2',
            'type' => 'licence',
            'typeId' => null
        ];
        $mockRoute = $this->getMock('\stdClass', ['toRoute']);
        $mockRoute->expects($this->once())
            ->method('toRoute')
            ->with('task_action', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $response = $this->controller->indexAction();

        $this->assertEquals('mockResponse', $response);
    }

    /**
     * Test index action with multiple close
     * @group task1
     */
    public function testIndexActionWithMultipleCloseSubmitted()
    {
        $this->request->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = $this->getMock('\stdClass', ['fromPost']);

        $params->expects($this->at(0))
            ->method('fromPost')
            ->will($this->returnValue('close task'));

        $params->expects($this->at(1))
            ->method('fromPost')
            ->will($this->returnValue([1, 2]));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $params = [
            'action' => 'close',
            'task'  => '1-2',
            'type' => 'licence',
            'typeId' => null
        ];
        $mockRoute = $this->getMock('\stdClass', ['toRoute']);
        $mockRoute->expects($this->once())
            ->method('toRoute')
            ->with('task_action', $params)
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

        if ($service == 'TaskSearchView' && $method == 'GET') {
            return [];
        }
        if ($service == 'Team' && $method == 'GET' && $data == $this->standardListData) {
            return $standardResponse;
        }
        if ($service == 'User' && $method == 'GET') {
            return $standardResponse;
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
