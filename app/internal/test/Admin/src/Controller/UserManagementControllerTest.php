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
                ],
                'userType' => 'transport-manager'
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
                ],
                'userType' => 'transport-manager'
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
        $mockData = $this->getMockExistingData();
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

        $mockUserBusinessService = m::mock('Common\BusinessService\Service\Admin\User');
        $mockUserBusinessService->shouldReceive('determineUserType')->with(m::type('array'))->andReturn('internal');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('BusinessServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\User')->andReturn($mockUserService);
        $mockSl->shouldReceive('get')->with('Admin\User')->andReturn($mockUserBusinessService);
        $this->controller->setServiceLocator($mockSl);

        $data = $this->controller->processLoad($mockData);
        $this->assertArrayHasKey('attempts', $data);

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

        $mockUserBusinessService = m::mock('Common\BusinessService\Service\Admin\User');
        $mockUserBusinessService->shouldReceive('process')->with($postData)->andReturn(
            $this->getResponse(
                ['id' => $id]
            )
        );

        $mockSl = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('BusinessServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\User')->andReturn($mockUserService);
        $mockSl->shouldReceive('get')->with('Admin\User')->andReturn($mockUserBusinessService);

        $this->controller->setServiceLocator($mockSl);

        $this->assertEquals('redirectResponse', $this->controller->processSave($postData));

    }


    public function testProcessSaveWithError()
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
        $mockFlashMessenger->shouldReceive('addErrorMessage');

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            null,
            ['action'=>'index', 'user'=>null],
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');

        $this->controller->setPluginManager($mockPluginManager);

        $mockUserService = m::mock('Common\Service\Data\User');
        $mockUserService->shouldReceive('saveUserRole')->with($postData)->andReturnNull();

        $mockUserBusinessService = m::mock('Common\BusinessService\Service\Admin\User');
        $mockUserBusinessService->shouldReceive('process')->with($postData)->andReturn(
            $this->getResponse(
                ['error' => 'Something went wrong'],
                true
            )
        );

        $mockSl = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('BusinessServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\User')->andReturn($mockUserService);
        $mockSl->shouldReceive('get')->with('Admin\User')->andReturn($mockUserBusinessService);

        $this->controller->setServiceLocator($mockSl);

        $this->assertEquals('redirectResponse', $this->controller->processSave($postData));

    }

    public function getResponse($data = [], $error = false)
    {
        $response = new \Common\BusinessService\Response();
        $type  = $error ? $response::TYPE_FAILED : $response::TYPE_SUCCESS;
        $response->setType($type);
        $response->setData($data);

        return $response;
    }

    public function getMockExistingData()
    {
        return [
            'id' => 1,
            'loginId' => 'l',
            'memorableWord' => 'mem',
            'mustRestPassword' => 'Y',
            'accountDisabled' => 'Y',
            'lockedDate' => '2015-01-01',
            'team' => 'test_team',
            'transportManager' => [
                'id' => 3
            ],
            'partnerContactDetails' => [
                'id' => 5
            ],
            'userRoles' => [
                0 => [
                    'role' => [
                        'id' => 99
                    ]
                ]
            ],
            'contactDetails' => [
                'id' => 111,
                'emailAddress' => 'someone@somewhere.com',
                'person' => [
                    'id' => 243,
                    'forename' => 'John',
                    'familyName' => 'Smith',
                    'birthDate' => ['1970-05-04']
                ],
                'address' => [
                    'id' => 244,
                    'addressLine1' => 'foo',
                    'postcode' => 'AB1 2CD'
                ],
                'phoneContacts' => [
                    0 => [
                        'phoneNumber' => '12345',
                        'phoneContactType' => [
                            'id' => 'phone_t_tel',
                        ]
                    ],
                    1 => [
                        'phoneNumber' => '54321',
                        'phoneContactType' => [
                            'id' => 'phone_t_fax',
                        ]
                    ]
                ]
            ],
            'lastSuccessfulLoginDate' => '2015-04-07 12:54:23',
            'attempts' => 2,
            'lockedDate' => '2015-06-07 17:00:00',
            'mustResetPassword' => 'Y',
            'resetPasswordExpiryDate' => '2015-01-02 19:00:00'
        ];
    }
}
