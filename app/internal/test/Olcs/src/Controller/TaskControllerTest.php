<?php

/**
 * Task controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co..uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Task controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co..uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TaskControllerTest extends AbstractHttpControllerTestCase
{

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
                'getFromRoute'
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
     * @group task
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
     * @group task
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
     * @group task
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
     * @group task
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
     * @group task
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

        $e1 = $this->getMock('\stdClass', ['setAttribute']);
        $e1->expects($this->once())
            ->method('setAttribute')
            ->with('disabled', 'disabled');

        $e2 = $this->getMock('\stdClass', ['setAttribute']);
        $e2->expects($this->once())
            ->method('setAttribute')
            ->with('disabled', 'disabled');

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

    /**
     * Test edit action post
     * @group task
     */
    public function testEditActionPost()
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

        $mockRoute = $this->getMock('\stdClass', ['toRoute']);
        $mockRoute->expects($this->once())
            ->method('toRoute')
            ->with('licence/processing', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $this->controller->editAction();
    }

    /**
     * Test add action post
     * @group task
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

        $mockRoute = $this->getMock('\stdClass', ['toRoute']);
        $mockRoute->expects($this->once())
            ->method('toRoute')
            ->with('licence/processing', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $this->controller->addAction();
    }

    /**
     * Test reassign action
     * @group task
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
     * @group task
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

        $mockRoute = $this->getMock('\stdClass', ['toRoute']);
        $mockRoute->expects($this->once())
            ->method('toRoute')
            ->with('licence/processing', $params)
            ->will($this->returnValue('mockResponse'));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRoute));

        $this->controller->reassignAction();
    }

    /**
     * Test reassign action post from dashboard with redirect back
     * @group task
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

        $this->request->expects($this->any())
            ->method('isXmlHttpRequest')
            ->will($this->returnValue(true));

        $this->controller->reassignAction();
    }

    /**
     * Test edit action post from dashboard with redirect back
     * @group task
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

        $this->request->expects($this->any())
            ->method('isXmlHttpRequest')
            ->will($this->returnValue(true));

        $this->controller->editAction();
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
        if ($service == 'Task' && $method == 'GET' && $data == ['id' => 456]) {
            return [
                'urgent' => 'Y',
                'version' => 1,
                'isClosed' => $this->isClosed,
                'category' => ['id' => 100],
                'taskSubCategory' => ['id' => 1],
                'assignedToTeam' => ['id' => 10],
                'assignedToUser' => ['id' => 1],
            ];
        }
    }
}
