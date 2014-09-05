<?php

/**
 * Task controller tests
 *
 * @author <nick.payne@valtech.co..uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Task controller tests
 *
 * @author <nick.payne@valtech.co..uk>
 */
class TaskControllerTest extends AbstractHttpControllerTestCase
{
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
                'getLicence'
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

        parent::setUp();
    }

    public function testAddAction()
    {
        $form = $this->getMock('\stdClass', ['get', 'setValue', 'setValueOptions', 'remove', 'setData']);

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

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

        $standardListData = [
            'limit' => 100,
            'sort' => 'name'
        ];

        $altListData = [
            'limit' => 100,
            'sort' => 'description'
        ];

        $extendedListData = [
            'team' => 2,
            'limit' => 100,
            'sort' => 'name'
        ];

        $this->controller->expects($this->at(5))
            ->method('makeRestCall')
            ->with('Category', 'GET', $altListData)
            ->will($this->returnValue($altResponse));

        $this->controller->expects($this->at(6))
            ->method('makeRestCall')
            ->with('TaskSubCategory', 'GET', $extendedListData)
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(7))
            ->method('makeRestCall')
            ->with('Team', 'GET', $standardListData)
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(8))
            ->method('makeRestCall')
            ->with('User', 'GET', $extendedListData)
            ->will($this->returnValue($response));

        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->at(0))
            ->method('fromRoute')
            ->with('task')
            ->will($this->returnValue(null));

        $fromRoute->expects($this->at(1))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue(123));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($fromRoute));

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

    public function testEditAction()
    {
        $form = $this->getMock('\stdClass', ['get', 'setValue', 'setValueOptions', 'remove', 'setData']);

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

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

        $standardListData = [
            'limit' => 100,
            'sort' => 'name'
        ];

        $altListData = [
            'limit' => 100,
            'sort' => 'description'
        ];

        $extendedListData = [
            'team' => 10,
            'category' => 100,
            'limit' => 100,
            'sort' => 'name'
        ];

        $taskData = [
            'urgent' => 'Y',
            'category' => ['id' => 100],
            'taskSubCategory' => ['id' => 1],
            'assignedToTeam' => ['id' => 10],
            'assignedToUser' => ['id' => 1],
        ];

        $this->controller->expects($this->at(2))
            ->method('makeRestCall')
            ->with('Task', 'GET', ['id' => 456])
            ->will($this->returnValue($taskData));

        $this->controller->expects($this->at(6))
            ->method('makeRestCall')
            ->with('Category', 'GET', $altListData)
            ->will($this->returnValue($altResponse));

        $this->controller->expects($this->at(7))
            ->method('makeRestCall')
            ->with('TaskSubCategory', 'GET', $extendedListData)
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(8))
            ->method('makeRestCall')
            ->with('Team', 'GET', $standardListData)
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(9))
            ->method('makeRestCall')
            ->with('User', 'GET', $extendedListData)
            ->will($this->returnValue($response));

        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->at(0))
            ->method('fromRoute')
            ->with('task')
            ->will($this->returnValue(456));

        $fromRoute->expects($this->at(1))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue(123));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($fromRoute));

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
            'team' => 10,
            'category' => 100,
            'limit' => 100,
            'sort' => 'name'
        ];

        $taskData = [
            'isClosed' => 'Y',
            'category' => ['id' => 100],
            'taskSubCategory' => ['id' => 1],
            'assignedToTeam' => ['id' => 10],
            'assignedToUser' => ['id' => 1],
        ];

        $this->controller->expects($this->at(2))
            ->method('makeRestCall')
            ->with('Task', 'GET', ['id' => 456])
            ->will($this->returnValue($taskData));

        $this->controller->expects($this->at(6))
            ->method('makeRestCall')
            ->with('Category', 'GET', $altListData)
            ->will($this->returnValue($altResponse));

        $this->controller->expects($this->at(7))
            ->method('makeRestCall')
            ->with('TaskSubCategory', 'GET', $extendedListData)
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(8))
            ->method('makeRestCall')
            ->with('Team', 'GET', $standardListData)
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(9))
            ->method('makeRestCall')
            ->with('User', 'GET', $extendedListData)
            ->will($this->returnValue($response));

        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->at(0))
            ->method('fromRoute')
            ->with('task')
            ->will($this->returnValue(456));

        $fromRoute->expects($this->at(1))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue(123));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($fromRoute));

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
            'team' => 10,
            'category' => 100,
            'limit' => 100,
            'sort' => 'name'
        ];

        $taskData = [
            'category' => ['id' => 100],
            'taskSubCategory' => ['id' => 1],
            'assignedToTeam' => ['id' => 10],
            'assignedToUser' => ['id' => 1],
        ];

        $this->controller->expects($this->at(2))
            ->method('makeRestCall')
            ->with('Task', 'GET', ['id' => 456])
            ->will($this->returnValue($taskData));

        $this->controller->expects($this->once())
            ->method('processEdit')
            ->will($this->returnValue(['id' => 1234]));

        $this->controller->expects($this->at(6))
            ->method('makeRestCall')
            ->with('Category', 'GET', $altListData)
            ->will($this->returnValue($altResponse));

        $this->controller->expects($this->at(7))
            ->method('makeRestCall')
            ->with('TaskSubCategory', 'GET', $extendedListData)
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(8))
            ->method('makeRestCall')
            ->with('Team', 'GET', $standardListData)
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(9))
            ->method('makeRestCall')
            ->with('User', 'GET', $extendedListData)
            ->will($this->returnValue($response));

        $this->controller->expects($this->any())->method('getLicence')->willReturn(
            [
                'id' => 123,
                'description' => 'foo',
                'licNo' => 456
            ]
        );

        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->at(0))
            ->method('fromRoute')
            ->with('task')
            ->will($this->returnValue(456));

        $fromRoute->expects($this->at(1))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue(123));

        $fromRoute->expects($this->at(3))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue(123));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($fromRoute));

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

        $params = [
            'licence' => 123
        ];

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
            'team' => 10,
            'category' => 100,
            'limit' => 100,
            'sort' => 'name'
        ];

        $taskData = [
            'category' => ['id' => 100],
            'taskSubCategory' => ['id' => 1],
            'assignedToTeam' => ['id' => 10],
            'assignedToUser' => ['id' => 1],
        ];
        $this->controller->expects($this->once())
            ->method('processAdd')
            ->will($this->returnValue(['id' => 1234]));

        $this->controller->expects($this->at(2))
            ->method('makeRestCall')
            ->with('Task', 'GET', ['id' => 456])
            ->will($this->returnValue($taskData));

        $this->controller->expects($this->at(6))
            ->method('makeRestCall')
            ->with('Category', 'GET', $altListData)
            ->will($this->returnValue($altResponse));

        $this->controller->expects($this->at(7))
            ->method('makeRestCall')
            ->with('TaskSubCategory', 'GET', $extendedListData)
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(8))
            ->method('makeRestCall')
            ->with('Team', 'GET', $standardListData)
            ->will($this->returnValue($response));

        $this->controller->expects($this->at(9))
            ->method('makeRestCall')
            ->with('User', 'GET', $extendedListData)
            ->will($this->returnValue($response));

        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->at(0))
            ->method('fromRoute')
            ->with('task')
            ->will($this->returnValue(456));

        $fromRoute->expects($this->at(1))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue(123));

        $fromRoute->expects($this->at(3))
            ->method('fromRoute')
            ->with('licence')
            ->will($this->returnValue(123));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($fromRoute));

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

        $params = [
            'licence' => 123
        ];

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
}
