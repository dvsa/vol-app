<?php

/**
 * Application Processing controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Application\Processing;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Common\Service\Entity\LicenceEntityService;

/**
 * Application Processing controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingTasksControllerTest extends AbstractHttpControllerTestCase
{
    // (all this expected data is duplicated from LicenceProcessingTasksControllerTest)

    private $taskSearchViewExpectedData = [
        'date'  => 'tdt_today',
        'status' => 'tst_open',
        'sort' => 'actionDate',
        'order' => 'ASC',
        'page' => 1,
        'limit' => 10,
        'actionDate' => '',
        'licenceId' => 1234,
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
            '\Olcs\Controller\Application\Processing\ApplicationProcessingTasksController',
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

        $mockServiceLocator = $this->getMock('\StdClass', ['get']);
        $mockServiceLocator->expects($this->any())
            ->method('get')
            ->will($this->returnCallback([$this, 'getServiceCallback']));
        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($mockServiceLocator));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnCallback(array($this, 'mockRestCall')));

        $this->taskSearchViewExpectedData['actionDate'] = '<= ' . date('Y-m-d');
        $this->extendedListData['actionDate'] = '<= ' . date('Y-m-d');
        $this->extendedListDataVariation1['actionDate'] = '<= ' . date('Y-m-d');

        parent::setUp();
    }

    public function getServiceCallback($name)
    {
        switch ($name) {
            case 'Entity\Application':
                return $this->getMockApplicationEntityService();
            case 'router':
                return $this->getMock('Zend\Mvc\Router\RouteStackInterface');
            default:
                break;
        }
    }
    /**
     * Gets a mock version of application entity service
     * @group task
     */
    private function getMockApplicationEntityService()
    {
        $mock = $this->getMock(
            '\StdClass',
            ['getLicenceIdForApplication','getDataForProcessing','getHeaderData']
        );
        $mock->expects($this->any())
            ->method('getLicenceIdForApplication')
            ->will($this->returnValue(1234));

        $applicationData = [
            'id' => 7,
            'status' => [
                'id' => 'AS1',
                'description' => 'appstatus'
            ],
            'licence' => [
                'id' => 1234,
                'licNo' => 'TEST1234',
                'goodsOrPsv' => [
                    'id' => LicenceEntityService::LICENCE_CATEGORY_PSV,
                    'description' => 'PSV'
                ],
                'organisation' => [
                    'name' => 'O1'
                ],
                'status' => [
                    'id' => 'LS1',
                    'description' => 'licstatus'
                ]
            ]
        ];
        $mock->expects($this->any())
            ->method('getDataForProcessing')
            ->will($this->returnValue($applicationData));

        $mock->expects($this->any())
            ->method('getHeaderData')
            ->will($this->returnValue($applicationData));

        return $mock;
    }

    /**
     * Test index action with no query
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

        // check that view has all data needed for new-style header
        $this->assertEquals('7', $header->getVariable('applicationId'));
        $this->assertEquals('1234', $header->getVariable('licenceId'));
        $this->assertEquals('O1', $header->getVariable('companyName'));

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
            'type' => 'application',
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
