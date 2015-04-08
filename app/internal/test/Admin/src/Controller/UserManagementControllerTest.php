<?php

/**
 * Test UserManagementController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace AdminTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use \Olcs\TestHelpers\ControllerRouteMatchHelper;
use Mockery as m;
use Zend\Stdlib\ArrayObject;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use \Common\Exception\BadRequestException;
use \Common\Exception\ResourceNotFoundException;

/**
 * Test UserManagementController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class UserManagementControllerTest extends MockeryTestCase
{
    /**
     * @var ControllerRouteMatchHelper
     */
    protected $pluginManagerHelper;

    /**
     * @var ControllerPluginManagerHelper
     */
    protected $routeMatchHelper;

    public function setUp()
    {
        $this->controller = new \Admin\Controller\UserManagementController();

        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->routeMatchHelper = new ControllerRouteMatchHelper();
    }

    public function testIndexAction()
    {
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'flashMessenger' => 'FlashMessenger', 'redirect' => 'Redirect',
                'viewHelperManager' => 'ViewHelperManager', 'script' => 'Script']
        );

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = $mockPluginManager->get('viewHelperManager', '');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($placeholder);

        $mockSearchService = m::mock('Common\Service\Data\Search\Search');
        $mockSearchService->shouldReceive('setQuery')->with(m::type('object'))->andReturnSelf();
        $mockSearchService->shouldReceive('setRequest')->with(m::type('object'))->andReturnSelf();
        $mockSearchService->shouldReceive('setIndex')->with('user')->andReturnSelf();
        $mockSearchService->shouldReceive('setSearch')->with('*')->andReturnSelf();
        $mockScriptService = m::mock();
        $mockScriptService->shouldReceive('loadFiles')->with(m::type('array'));

        $userResults = [];

        $mockSearchService->shouldReceive('fetchResultsTable')->andReturn($userResults);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\Search\Search')->andReturn($mockSearchService);
        $mockSl->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('Script')->andReturn($mockScriptService);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromPost')->with('action')->andReturnNull();

        $mockFlash = $mockPluginManager->get('flashMessenger', '');
        $mockFlash->shouldReceive('addErrorMessage')->with('Please provide a search term');

        $mockContainer = new ArrayObject();
        $mockSearchForm = m::mock('Zend\Form\Form');
        $mockSearchForm->shouldReceive('getObject')->andReturn($mockContainer);
        $mockSearchForm->shouldReceive('setData');

        $placeholder->getContainer('headerSearch')->set($mockSearchForm);

        $this->controller->setPluginManager($mockPluginManager);
        $this->controller->setServiceLocator($mockSl);

        $this->controller->indexAction();

    }

    /**
     * Tests the getForm on initial request
     */
    public function testGetFormNoPost()
    {
        $mockForm = m::mock('\Zend\Form\Form');

        $applicationTransportManagerElement = m::mock('\Zend\Form\Element');
        $applicationTransportManagerElement->shouldReceive('setMessages')->with(m::type('array'));

        $userTypeFieldset = m::mock('\Zend\Form\Fieldset');
        $userTypeFieldset->shouldReceive('get')->with('applicationTransportManagers')
            ->andReturn($applicationTransportManagerElement);

        $mockForm->shouldReceive('hasAttribute')->with('action')->andReturnNull();
        $mockForm->shouldReceive('setAttribute')->with('action', '');
        $mockForm->shouldReceive('getFieldsets')->andReturn([]);

        $mockForm->shouldReceive('get')->with('userType')->andReturn($userTypeFieldset);

        $mockFormHelper = m::mock();
        $mockFormHelper->shouldReceive('createForm')->andReturn($mockForm);

        $mockStringHelper = m::mock();
        $mockStringHelper->shouldReceive('dashToCamel')->andReturn('user');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturn(m::type('string'));
        $mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $mockSl->shouldReceive('get')->with('Helper\String')->andReturn($mockStringHelper);

        $this->controller->setServiceLocator($mockSl);

        $form = $this->controller->getForm('user');
        $this->assertSame($form, $mockForm);

    }

    /**
     * Tests the getForm on POST of a valid application Id. Returns 2 TMs
     */
    public function testGetFormLookupValidApplication()
    {
        $applicationId = 99;
        $event = $this->routeMatchHelper->getMockRouteMatch(
            array(
                'controller' => 'user_management'
            )
        );

        $tmList = [
            5 => 'John Smith',
            10 => 'Mr T'
        ];

        $post = [
            'userType' => [
                'applicationTransportManagers' => [
                    'search' => 'search',
                    'application' => $applicationId
                ]
            ]
        ];
        $this->controller->setEvent($event);

        $this->controller->getRequest()->setMethod('post');
        $this->controller->getRequest()->setPost(new \Zend\Stdlib\Parameters($post));

        $mockForm = m::mock('\Zend\Form\Form');

        $applicationTransportManagerElement = m::mock('\Zend\Form\Element');
        $applicationTransportManagerElement->shouldReceive('setMessages')->with(m::type('array'));

        $transportManagerElement = m::mock('\Zend\Form\Element');
        $transportManagerElement->shouldReceive('setValueOptions')->with($tmList);

        $userTypeFieldset = m::mock('\Zend\Form\Fieldset');
        $userTypeFieldset->shouldReceive('get')->with('applicationTransportManagers')
            ->andReturn($applicationTransportManagerElement);
        $userTypeFieldset->shouldReceive('get')->with('transportManager')
            ->andReturn($transportManagerElement);

        $mockForm->shouldReceive('hasAttribute')->with('action')->andReturnNull();
        $mockForm->shouldReceive('setAttribute')->with('action', '');
        $mockForm->shouldReceive('getFieldsets')->andReturn([]);

        $mockForm->shouldReceive('get')->with('userType')->andReturn($userTypeFieldset);

        $mockFormHelper = m::mock();
        $mockFormHelper->shouldReceive('createForm')->andReturn($mockForm);

        $mockStringHelper = m::mock();
        $mockStringHelper->shouldReceive('dashToCamel')->andReturn('user');

        $mockDataService = m::mock('Common\Service\Data\TransportManagerApplication');
        $mockDataService->shouldReceive('fetchTmListOptionsByApplicationId')
            ->with($applicationId)
            ->andReturn($tmList);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $mockSl->shouldReceive('get')->with('Helper\String')->andReturn($mockStringHelper);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\TransportManagerApplication')
            ->andReturn($mockDataService);
        $this->controller->setServiceLocator($mockSl);

        $form = $this->controller->getForm('user');
        $this->assertSame($form, $mockForm);

    }

    /**
     * Tests the getForm on POST of an invalid application Id. Returns no TMs
     */
    public function testGetFormLookupInvalidApplication()
    {
        $applicationId = 99;
        $event = $this->routeMatchHelper->getMockRouteMatch(
            array(
                'controller' => 'user_management'
            )
        );

        $tmList = [];

        $post = [
            'userType' => [
                'applicationTransportManagers' => [
                    'search' => 'search',
                    'application' => $applicationId
                ]
            ]
        ];
        $this->controller->setEvent($event);

        $this->controller->getRequest()->setMethod('post');
        $this->controller->getRequest()->setPost(new \Zend\Stdlib\Parameters($post));

        $mockForm = m::mock('\Zend\Form\Form');

        $applicationTransportManagerElement = m::mock('\Zend\Form\Element');
        $applicationTransportManagerElement->shouldReceive('setMessages')->with(m::type('array'));

        $applicationElement = m::mock('\Zend\Form\Element');
        $applicationElement->shouldReceive('setMessages')->with(m::type('array'));

        $userTypeFieldset = m::mock('\Zend\Form\Fieldset');
        $userTypeFieldset->shouldReceive('get')->with('applicationTransportManagers')
            ->andReturn($applicationTransportManagerElement);
        $applicationTransportManagerElement->shouldReceive('get')->with('application')
            ->andReturn($applicationElement);

        $mockForm->shouldReceive('hasAttribute')->with('action')->andReturnNull();
        $mockForm->shouldReceive('setAttribute')->with('action', '');
        $mockForm->shouldReceive('getFieldsets')->andReturn([]);

        $mockForm->shouldReceive('get')->with('userType')->andReturn($userTypeFieldset);

        $mockFormHelper = m::mock();
        $mockFormHelper->shouldReceive('createForm')->andReturn($mockForm);

        $mockStringHelper = m::mock();
        $mockStringHelper->shouldReceive('dashToCamel')->andReturn('user');

        $mockDataService = m::mock('Common\Service\Data\TransportManagerApplication');
        $mockDataService->shouldReceive('fetchTmListOptionsByApplicationId')
            ->with($applicationId)
            ->andReturn($tmList);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $mockSl->shouldReceive('get')->with('Helper\String')->andReturn($mockStringHelper);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\TransportManagerApplication')
            ->andReturn($mockDataService);
        $this->controller->setServiceLocator($mockSl);

        $form = $this->controller->getForm('user');

        $this->assertSame($form, $mockForm);
    }

    public function testProcessLoadNoData()
    {
        $mockData = [];
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params'
            ]
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case', '')->andReturnNull();
        $mockParams->shouldReceive('fromQuery')->with('case', '')->andReturnNull();

        $this->controller->setPluginManager($mockPluginManager);
        $data = $this->controller->processLoad($mockData);

        $this->assertEquals(0, $data['attempts']);
    }

    public function testProcessLoadWithData()
    {
        $userId = 101;
        $mockData = [
            'id' => $userId,
            'attempts' => 5
        ];
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params'
            ]
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case', '')->andReturnNull();
        $mockParams->shouldReceive('fromQuery')->with('case', '')->andReturnNull();

        $this->controller->setPluginManager($mockPluginManager);

        $mockUserService = m::mock('Common\Service\Data\User');
        $mockUserService->shouldReceive('formatDataForUserRoleForm')
            ->with($mockData)
            ->andReturn($mockData);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\User')
            ->andReturn($mockUserService);
        $this->controller->setServiceLocator($mockSl);


        $data = $this->controller->processLoad($mockData);

        $this->assertEquals($mockData['attempts'], $data['attempts']);
    }

    public function testProcessSave()
    {
        $id = 1;

        $postData = [
            'fields' => [
            ]
        ];

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect',
                'FlashMessenger' => 'FlashMessenger'
            ]
        );

        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage');

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            null,
            ['action'=>'index', 'user'=>null],
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');

        $this->controller->setPluginManager($mockPluginManager);

        $mockUserService = m::mock('Common\Service\Data\User');
        $mockUserService->shouldReceive('saveUserRole')->with($postData)->andReturn($id);

        $mockSl = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')
            ->with('Common\Service\Data\User')
            ->andReturn($mockUserService);

        $this->controller->setServiceLocator($mockSl);

        $this->assertEquals('redirectResponse', $this->controller->processSave($postData));

    }

    /**
     * Tests the processSave method
     *
     * @dataProvider processSaveExceptionProvider
     *
     * @param $expectedException
     * @param $message
     */
    public function testProcessSaveException($expectedException, $message)
    {
        $id = 1;
        $class = 'Common\Exception\\' . $expectedException;

        $postData = [
            'fields' => [
            ]
        ];

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect',
                'FlashMessenger' => 'FlashMessenger'
            ]
        );

        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addErrorMessage', $message);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            null,
            ['action'=>'index', 'user'=>null],
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');

        $this->controller->setPluginManager($mockPluginManager);

        $mockUserService = m::mock('Common\Service\Data\User');
        $mockUserService->shouldReceive('saveUserRole')->with($postData)->andThrow(new $class($message));

        $mockSl = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')
            ->with('Common\Service\Data\User')
            ->andReturn($mockUserService);

        $this->controller->setServiceLocator($mockSl);

        $this->assertEquals('redirectResponse', $this->controller->processSave($postData));

    }

    /**
     * Data provider for testProcessSaveException
     *
     * @return array
     */
    public function processSaveExceptionProvider()
    {
        return [
            [
                'BadRequestException',
                'Error message 1'
            ],
            [
                'ResourceNotFoundException',
                'Error message 2'
            ],
        ];
    }

}
