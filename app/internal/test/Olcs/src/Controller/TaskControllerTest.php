<?php

/**
 * Task controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Form\Fieldset;
use Zend\Form\Element\DateSelect;
use Mockery as m;

/**
 * Task controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TaskControllerTest extends AbstractHttpControllerTestCase
{
    use \OlcsTest\Traits\MockeryTestCaseTrait;

    private $altListData = [
        'limit' => 100,
        'sort' => 'description'
    ];

    private $standardListData = [
        'limit' => 100,
        'sort' => 'name'
    ];

    private $userList = [
        'team' => 123,
        'limit' => 100,
        'sort' => 'name'
    ];

    private $extendedListDataVariation1 = [
        'team' => 10,
        'category' => 100,
        'limit' => 100,
        'sort' => 'name'
    ];

    private $taskSearchViewExpectedData = [
        'assignedToUser'  => 1,
        'assignedToTeam'  => 2,
        'date'  => 'today',
        'status' => 'open',
        'sort' => 'actionDate',
        'order' => 'ASC',
        'page' => 1,
        'limit' => 10,
        'actionDate' => ''
    ];

    private $taskSearchViewExpectedDataVar1 = [
        'assignedToTeam'  => 2,
        'date'  => 'today',
        'status' => 'open',
        'sort' => 'actionDate',
        'order' => 'ASC',
        'page' => 1,
        'limit' => 10,
        'actionDate' => '',
    ];

    private $isClosed = 'N';

    private $testClickedButton = false;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\TaskController',
            array(
                'makeRestCall',
                'getLoggedInUser',
                'getRequest',
                'getForm',
                'loadScripts',
                'params',
                'url',
                'redirect',
                'processAdd',
                'processEdit',
                'getFromRoute',
                'getSearchForm',
                'getServiceLocator',
                'getLicenceIdForApplication',
                'getApplication',
                'getBusReg'
            )
        );

        $request = $this->getMock('\stdClass', ['isPost', 'getPost', 'isXmlHttpRequest']);

        $this->request = $request;

        $this->controller->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $this->controller->expects($this->any())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $url = $this->getMock('\stdClass', ['fromRoute']);

        $this->controller->expects($this->any())
            ->method('url')
            ->will($this->returnValue($url));

        $this->url = $url;

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnCallback(array($this, 'mockRestCall')));

        parent::setUp();
    }

    /**
     * Test add action
     */
    public function testAddAction()
    {
        $form = $this->getMock('\stdClass', ['get', 'setValue', 'setValueOptions', 'remove', 'setData']);

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('type', 'licence'),
                        array('typeId', null),
                        array('task', 123),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $view = $this->controller->addAction();

        list($header, $content) = $view->getChildren();

        $this->assertEquals('Add task', $header->getVariable('pageTitle'));
    }

    /**
     * Test edit action
     */
    public function testEditAction()
    {
        $form = $this->getMock('\stdClass', ['get', 'setValue', 'setValueOptions', 'remove', 'setData']);

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('type', 'licence'),
                        array('typeId', 123),
                        array('task', 456),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $view = $this->controller->editAction();

        list($header, $content) = $view->getChildren();

        $this->assertEquals('Edit task', $header->getVariable('pageTitle'));
    }

    /**
     * Test edit action from dashboard
     */
    public function testEditFromDashboardAction()
    {
        $form = $this->getMock('\stdClass', ['get', 'setValue', 'setValueOptions', 'remove', 'setData']);

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('type', ''),
                        array('typeId', 123),
                        array('task', 456),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $view = $this->controller->editAction();

        list($header, $content) = $view->getChildren();

        $this->assertEquals('Edit task', $header->getVariable('pageTitle'));
    }

    /**
     * Test edit action from dashboard with no task type id
     * @expectedException \Exception
     */
    public function testEditFromDashboardActionNoTaskTypeId()
    {

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('type', ''),
                        array('typeId', 123),
                        array('task', null),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $this->controller->editAction();
    }

    /**
     * Test edit action closed task
     */
    public function testEditActionClosedTask()
    {
        $form = $this->getMock(
            '\Zend\Form\Form',
            [
                'get', 'setValue', 'setValueOptions',
                'remove', 'setData', 'setAttribute',
                'getFieldsets', 'getElements'
            ]
        );
        $this->isClosed = 'Y';

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $e1 = $this->getMock('\stdClass', ['setAttribute', 'getName']);
        $e1->expects($this->once())
            ->method('setAttribute')
            ->with('disabled', 'disabled');

        $e1->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('name'));

        $e2 = $this->getMock('\stdClass', ['setAttribute', 'getName']);
        $e2->expects($this->once())
            ->method('setAttribute')
            ->with('disabled', 'disabled');

        $e2->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('name'));

        $form->expects($this->any())
            ->method('getFieldsets')
            ->will($this->returnValue([$e1]));

        $form->expects($this->any())
            ->method('getElements')
            ->will($this->returnValue([$e2]));

        $form->expects($this->once())
            ->method('setAttribute')
            ->with('disabled', 'disabled');

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('type', 'licence'),
                        array('typeId', 456),
                        array('task', 456),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $view = $this->controller->editAction();

        list($header, $content) = $view->getChildren();

        $this->assertEquals('Edit task', $header->getVariable('pageTitle'));
    }

    public function editActionPostDp()
    {
        return [
            'Licence task'           => ['licence', ['licence'=>123], 'licence/processing'],
            'Application task'       => ['application', ['application'=>123], 'lva-application/processing'],
            'Transport Manager task' => ['tm', ['transportManager'=>123], 'transport-manager/processing/tasks'],
            'Bus Registration task'  => ['busreg', ['busRegId'=>123, 'licence'=>987], 'licence/bus-processing/tasks'],
        ];
    }
    /**
     * Test edit action post
     *
     * @dataProvider editActionPostDp
     */
    public function testEditActionPost($type, $routeParams, $expectedRoute)
    {
        $form = $this->getMock(
            '\stdClass',
            [
                'get', 'setValue', 'setValueOptions',
                'remove', 'setData', 'isValid',
                'getData',
            ]
        );

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        $formData = [
            'details' => [
                'urgent' => 1
            ],
            'assignment' => [],
            'id' => 100,
            'version' => 200
        ];

        $form->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($formData));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->once())
            ->method('processEdit');

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('type', $type),
                        array('typeId', 123),
                        array('task', 456),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $mockRoute = $this->getMock('\stdClass', ['toRouteAjax']);
        $mockRoute->expects($this->once())
            ->method('toRouteAjax')
            ->with($expectedRoute, $routeParams)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('getLicenceIdForApplication')
            ->with(123)
            ->will($this->returnValue(987));

        $this->controller->expects($this->any())
            ->method('getApplication')
            ->with(123)
            ->will(
                $this->returnValue(
                    [
                        'id' => 123,
                        'licence' => [
                            'id' => 987,
                            'licNo' => 'AB1234',
                        ]
                    ]
                )
            );
        $this->controller->expects($this->any())
            ->method('getBusReg')
            ->with(123)
            ->will(
                $this->returnValue(
                    [
                        'id' => 123,
                        'regNo' => 'BR1234',
                        'licence' => [
                            'id' => 987,
                        ]
                    ]
                )
            );

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $this->controller->editAction();
    }

    /**
     * Test add action post
     */
    public function testAddActionPost()
    {
        $form = $this->getMock(
            '\stdClass',
            [
                'get', 'setValue', 'setValueOptions',
                'remove', 'setData', 'isValid',
                'getData'
            ]
        );

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        $formData = [
            'details' => [],
            'assignment' => [],
            'id' => 100,
            'version' => 200
        ];

        $form->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($formData));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->once())
            ->method('processAdd')
            ->will($this->returnValue(['id' => 1234]));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('type', 'licence'),
                        array('typeId', 1),
                        array('task', 123),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = ['licence' => 1];

        $mockRoute = $this->getMock('\stdClass', ['toRouteAjax']);
        $mockRoute->expects($this->once())
            ->method('toRouteAjax')
            ->with('licence/processing', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $this->controller->addAction();
    }

    /**
     * Test reassign action
     */
    public function testReassignAction()
    {
        $form = $this->getMock('\stdClass', ['get', 'setValue', 'setValueOptions', 'remove', 'setData']);

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('type', 'licence'),
                        array('typeId', 123),
                        array('task', 456),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $view = $this->controller->reassignAction();

        list($header, $content) = $view->getChildren();

        $this->assertEquals('Re-assign task', $header->getVariable('pageTitle'));
    }

    /**
     * Test reassign action post
     */
    public function testReassignActionPost()
    {
        $form = $this->getMock(
            '\stdClass',
            [
                'get', 'setValue', 'setValueOptions',
                'remove', 'setData', 'isValid',
                'getData'
            ]
        );

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        $formData = [
            'assignment' => ['assignedToTeam' => 1, 'assignedToUser' => 1],
            'id' => 100,
            'version' => 200
        ];

        $form->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($formData));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('type', 'licence'),
                        array('typeId', 123),
                        array('task', 456),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = ['licence' => 123];

        $mockRoute = $this->getMock('\stdClass', ['toRouteAjax']);
        $mockRoute->expects($this->once())
            ->method('toRouteAjax')
            ->with('licence/processing', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $this->controller->reassignAction();
    }

    /**
     * Test reassign action post from dashboard with redirect back
     */
    public function testReassignActionPostFromDashboard()
    {
        $form = $this->getMock(
            '\stdClass',
            [
                'get', 'setValue', 'setValueOptions',
                'remove', 'setData', 'isValid',
                'getData'
            ]
        );

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        $formData = [
            'assignment' => ['assignedToTeam' => 1, 'assignedToUser' => 1],
            'id' => 100,
            'version' => 200
        ];

        $form->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($formData));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('task', 456),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = [];

        $mockRoute = $this->getMock('\stdClass', ['toRouteAjax']);
        $mockRoute->expects($this->once())
            ->method('toRouteAjax')
            ->with('dashboard', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $this->controller->reassignAction();
    }

    /**
     * Test edit action post from dashboard with redirect back
     */
    public function testEditActionPostFromDashboard()
    {
        $form = $this->getMock(
            '\stdClass',
            [
                'get', 'setValue', 'setValueOptions',
                'remove', 'setData', 'isValid',
                'getData'
            ]
        );

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        $formData = [
            'details' => [
                'urgent' => 1
            ],
            'assignment' => [],
            'id' => 100,
            'version' => 200
        ];

        $form->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($formData));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->once())
            ->method('processEdit')
            ->will($this->returnValue(['id' => 1234]));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('task', 456),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = [];

        $mockRoute = $this->getMock('\stdClass', ['toRouteAjax']);
        $mockRoute->expects($this->once())
            ->method('toRouteAjax')
            ->with('dashboard', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $this->controller->editAction();
    }

    /**
     * Test close action
     * @dataProvider closeTaskProvider
     */
    public function testCloseAction($taskId, $titleExpected)
    {
        $form = $this->getMock('\stdClass', ['get', 'setValue', 'setValueOptions', 'remove', 'setData', 'setLabel']);

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('type', 'licence'),
                        array('typeId', 123),
                        array('task', $taskId),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $view = $this->controller->closeAction();

        list($header, $content) = $view->getChildren();

        $this->assertEquals($titleExpected, $header->getVariable('pageTitle'));
    }

    /**
     * Data provider for testCloseAction
     */
    public function closeTaskProvider()
    {
        return array(
            array('456', 'Close task'),
            array('456-789', 'Close (2) tasks')
        );
    }

    /**
     * Test close action post
     */
    public function testCloseActionPost()
    {
        $form = $this->getMock(
            '\stdClass',
            [
                'get', 'setValue', 'setValueOptions',
                'remove', 'setData', 'isValid',
                'getData'
            ]
        );

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        $formData = [
            'assignment' => ['assignedToTeam' => 1, 'assignedToUser' => 1],
            'id' => 100,
            'version' => 200,
            'buttonClicked' => 'form-actions[close]'
        ];

        $form->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($formData));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('type', 'licence'),
                        array('typeId', 123),
                        array('task', 456),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = ['licence' => 123];

        $mockRoute = $this->getMock('\stdClass', ['toRouteAjax']);
        $mockRoute->expects($this->once())
            ->method('toRouteAjax')
            ->with('licence/processing', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $this->controller->closeAction();
    }

    /**
     * Test close action post from dashboard with redirect back
     */
    public function testCloseActionPostFromDashboard()
    {
        $form = $this->getMock(
            '\stdClass',
            [
                'get', 'setValue', 'setValueOptions',
                'remove', 'setData', 'isValid',
                'getData'
            ]
        );

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        $formData = [
            'details' => [
                'urgent' => 1
            ],
            'assignment' => [],
            'id' => 100,
            'version' => 200
        ];

        $form->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($formData));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('task', 456),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = [];

        $mockRoute = $this->getMock('\stdClass', ['toRouteAjax']);
        $mockRoute->expects($this->once())
            ->method('toRouteAjax')
            ->with('dashboard', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $this->controller->closeAction();
    }

    /**
     * Test close action post from modal
     */
    public function testCloseActionPostFromModal()
    {
        $form = $this->getMock(
            '\stdClass',
            [
                'get', 'setValue', 'setValueOptions',
                'remove', 'setData', 'isValid',
                'getData'
            ]
        );
        $this->testClickedButton = true;
        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        $formData = [
            'details' => [
                'urgent' => 1
            ],
            'assignment' => [],
            'id' => 100,
            'version' => 200
        ];

        $form->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($formData));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnValueMap(
                    array(
                        array('type', 'licence'),
                        array('typeId', 123),
                        array('task', 456),
                    )
                )
            );

        $toArray = $this->getMock('\stdClass', ['toArray']);
        $toArray->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue([]));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($toArray));

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $params = ['licence' => 123];

        $mockRoute = $this->getMock('\stdClass', ['toRouteAjax']);
        $mockRoute->expects($this->once())
            ->method('toRouteAjax')
            ->with('licence/processing', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $this->controller->editAction();
    }

    /**
     * Test disable form elements
     */
    public function testDisableFormElements()
    {
        $fieldset = new Fieldset();
        $dateSelect = new DateSelect();
        $dateSelect->setName('dateSelect');
        $fieldset->add($dateSelect);
        $this->controller->disableFormElements($fieldset);
        $this->assertEquals($fieldset->get('dateSelect')->getAttribute('disabled'), 'disabled');
    }

    /**
     * Test edit action for application
     */
    public function testEditActionForApplication()
    {
        $sut = m::mock('\Olcs\Controller\TaskController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // mock form
        $sut->shouldReceive('getForm')->andReturn(
            m::mock('\Zend\Form\Form')
                ->shouldReceive('get')
                    ->andReturnSelf()
                ->shouldReceive('setValue')
                ->shouldReceive('setValueOptions')
                ->shouldReceive('remove')
                ->shouldReceive('setData')
                ->getMock()
        );

        // mock route params
        $sut->shouldReceive('getFromRoute')
            ->with('type')
            ->andReturn('application');
        $sut->shouldReceive('getFromRoute')
            ->with('typeId')
            ->andReturn(123);
        $sut->shouldReceive('getFromRoute')
            ->with('task')
            ->andReturn(456);


        // mock URL helper
        $sut->shouldReceive('url')->andReturn(
            m::mock('\StdClass')
                ->shouldReceive('fromRoute')
                    ->with('lva-licence', ['licence' => 987])
                    ->andReturn('licenceURL987')
                ->shouldReceive('fromRoute')
                    ->with('lva-application', ['application' => 123])
                    ->andReturn('applicationURL123')
                ->getMock()
            );

        // mock request (getPost->toArray())
        $sut->shouldReceive('getRequest')->andReturn(
            m::mock('\StdClass')
                ->shouldReceive('isPost')
                    ->andReturn(false)
                ->shouldReceive('getPost')
                    ->andReturn(
                        m::mock('StdClass')->shouldReceive('toArray')->andReturn([])->getMock()
                    )
                ->shouldReceive('isXmlHttpRequest')
                    ->andReturn(true)
                ->getMock()
        );

        // mock REST calls for task details
        $sut->shouldReceive('makeRestCall')
            ->with(
                'TaskSearchView',
                'GET',
                ['id' => 456],
                ['properties' => ['linkType', 'linkId', 'linkDisplay', 'licenceId']]
            )
            ->andReturn(
                [
                    // task details, but we only need licence stuff
                    'licenceId' => 987,
                    'licenceNo' => 'AB1234',
                ]
            );

        $sut->shouldReceive('makeRestCall')
            ->with(
                'Task',
                'GET',
                ['id' => 456],
                m::any() // bundle
            )
            ->andReturn([]);

        // mock lookup licence details for application
        $sut->shouldReceive('getApplication')->with('123')->andReturn(
            [
                'id' => 123,
                'licence' => ['id' => 987, 'licNo' => 'AB1234'],
            ]
        );

        // stub rest calls for dropdowns
        $sut->shouldReceive('getListDataFromBackend')->andReturn([]);

        // check scripts are loaded
        $sut->shouldReceive('loadScripts')->with(['forms/task']);

        $sm = \OlcsTest\Bootstrap::getServiceManager();
        $sut->setServiceLocator($sm);

        $view = $sut->editAction();

        list($header, $content) = $view->getChildren();

        $this->assertEquals('Edit task', $header->getVariable('pageTitle'));
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
                    'name' => 'foo'
                ],
                [
                    'id' => 456,
                    'name' => 'bar'
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
        if ($service == 'User' && $method == 'GET' && $data == $this->standardListData) {
            return $standardResponse;
        }
        if ($service == 'User' && $method == 'GET' && $data == $this->extendedListDataVariation1) {
            return $standardResponse;
        }
        if ($service == 'User' && $method == 'GET' && $data == $this->userList) {
            return $userListResponse;
        }
        if ($service == 'TaskSubCategory' && $method == 'GET' && $data == $this->standardListData) {
            return $standardResponse;
        }
        if ($service == 'TaskSubCategory' && $method == 'GET' && $data == $this->extendedListDataVariation1) {
            return $standardResponse;
        }
        if ($service == 'Category' && $method == 'GET' && $data == $this->altListData) {
            return $altResponse;
        }
        if ($service == 'Task' &&
            $method == 'GET' &&
            ($data == ['id' => 456] || $data == ['id' => 789] || $data == ['id' => 123])
            ) {
            $retv = [
                'urgent' => 'Y',
                'version' => 1,
                'isClosed' => $this->isClosed,
                'category' => ['id' => 100],
                'taskSubCategory' => ['id' => 1],
                'assignedToTeam' => ['id' => 10],
                'assignedToUser' => [],
            ];
            if ($this->testClickedButton) {
                $retv['buttonClicked'] = 'form-actions[close]';
            }
            return $retv;
        }
    }
}
