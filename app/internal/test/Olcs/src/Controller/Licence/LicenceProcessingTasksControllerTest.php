<?php

/**
 * Licence controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Licence\Processing;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Licence controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceProcessingTasksControllerTest extends AbstractHttpControllerTestCase
{
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
                'setTableFilters'
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

        parent::setUp();
    }

    /**
     * Gets a mock version of translator
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

    public function testIndexActionWithNoQueryUsesDefaultParams()
    {
        $licenceData = array(
            'licNo' => 'TEST1234',
            'goodsOrPsv' => array(
                'id' => 'PSV',
                'description' => 'PSV'
            ),
            'licenceType' => array(
                'id' => 'L1',
                'description' => 'L1'
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

        $expectedParams = array(
            'assignedToUser' => 1,
            'assignedToTeam'  => 2,
            'date'  => 'today',
            'status' => 'open',
            'sort' => 'actionDate',
            'order' => 'ASC',
            'page' => 1,
            'limit' => 10,
            // @NOTE: I don't like the date variable here, maybe use
            // DateTime and a mock instead
            'actionDate' => '<= ' . date('Y-m-d'),
            'linkId' => 1234,
            'linkType' => 'Licence'
        );
        $this->controller->expects($this->at(4))
            ->method('makeRestCall')
            ->with('TaskSearchView', 'GET', $expectedParams)
            ->will($this->returnValue([]));

        $tableMock = $this->getMock('\stdClass', ['render', 'removeColumn']);
        $this->controller->expects($this->once())
            ->method('getTable')
            ->with(
                'tasks',
                [],
                array_merge(
                    $expectedParams,
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

        $this->controller->expects($this->once())
            ->method('setTableFilters')
            ->with($form);

        $response = [
            'Results' => [
                [
                    'id' => 123,
                    'name' => 'foo'
                ]
            ]
        ];

        $altResponse = [
            'Results' => [
                [
                    'id' => 123,
                    'description' => 'foo'
                ]
            ]
        ];

        $standardListData = [
            'limit' => 100,
            'sort' => 'name'
        ];

        $altListData = [
            'limit' => 100,
            'sort' => 'description'
        ];

        $extendedListData = [
            'assignedToUser' => 1,
            'assignedToTeam'  => 2,
            'team'  => 2,
            'date'  => 'today',
            'status' => 'open',
            'sort' => 'name',
            'order' => 'ASC',
            'page' => 1,
            'limit' => 100,
            'actionDate' => '<= ' . date('Y-m-d'),
            'linkId' => 1234,
            'linkType' => 'Licence'
        ];

        $this->controller->expects($this->at(8))
            ->method('makeRestCall')
            ->with('Team', 'GET', $standardListData)
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(9))
            ->method('makeRestCall')
            ->with('User', 'GET', $extendedListData)
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(10))
            ->method('makeRestCall')
            ->with('Category', 'GET', $altListData)
            ->will($this->returnValue($altResponse));

        $this->controller->expects($this->at(11))
            ->method('makeRestCall')
            ->with('TaskSubCategory', 'GET', $extendedListData)
            ->will($this->returnValue($response));

        $view = $this->controller->indexAction();
        list($header, $content) = $view->getChildren();

        $this->assertEquals('TEST1234', $header->getVariable('pageTitle'));
        $this->assertEquals('PSV, L1, S1', $header->getVariable('pageSubTitle'));
    }

    public function testIndexActionAjax()
    {
        $this->controller->expects($this->at(4))
            ->method('makeRestCall')
            ->will($this->returnValue([]));

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

        $response = [
            'Results' => [
                [
                    'id' => 123,
                    'name' => 'foo'
                ]
            ]
        ];

        $altResponse = [
            'Results' => [
                [
                    'id' => 123,
                    'description' => 'foo'
                ]
            ]
        ];

        $this->controller->expects($this->at(8))
            ->method('makeRestCall')
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(9))
            ->method('makeRestCall')
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(10))
            ->method('makeRestCall')
            ->will($this->returnValue($altResponse));

        $this->controller->expects($this->at(11))
            ->method('makeRestCall')
            ->will($this->returnValue($response));

        $this->request->expects($this->exactly(1))
            ->method('isXmlHttpRequest')
            ->will($this->returnValue(true));

        $view = $this->controller->indexAction();

        $this->assertTrue($view->terminate());
    }

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
            'licence' => null,
        ];
        $mockRoute = $this->getMock('\stdClass', ['toRoute']);
        $mockRoute->expects($this->once())
            ->method('toRoute')
            ->with('licence/task_action', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $response = $this->controller->indexAction();

        $this->assertEquals('mockResponse', $response);
    }

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
            'licence' => null,        // we don't mock it
            'task'  => 321
        ];
        $mockRoute = $this->getMock('\stdClass', ['toRoute']);
        $mockRoute->expects($this->once())
            ->method('toRoute')
            ->with('licence/task_action', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $response = $this->controller->indexAction();

        $this->assertEquals('mockResponse', $response);
    }
}
