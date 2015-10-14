<?php

/**
 * Test UserManagementController
 */

namespace AdminTest\Controller;

use Admin\Controller\UserManagementController as Sut;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerRouteMatchHelper;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Test UserManagementController
 */
class UserManagementControllerTest extends MockeryTestCase
{
    public function setUp()
    {
        $this->markTestSkipped();
        $this->controller = m::mock(Sut::class)->makePartial()->shouldAllowMockingProtectedMethods();
    }

    public function testIndexAction()
    {
        $userResults = ['data'];

        $placeholder = m::mock('Zend\View\Helper\Placeholder');
        $placeholder->shouldReceive('setPlaceholder')->with('pageTitle', 'User management')->once();
        $placeholder->shouldReceive('setPlaceholder')->with('table', $userResults)->once();
        $this->controller->shouldReceive('placeholder')->andReturn($placeholder);

        $mockSearchService = m::mock('Common\Service\Data\Search\Search');
        $mockSearchService->shouldReceive('setQuery')->with(m::type('object'))->andReturnSelf();
        $mockSearchService->shouldReceive('setRequest')->with(m::type('object'))->andReturnSelf();
        $mockSearchService->shouldReceive('setIndex')->with('user')->andReturnSelf();
        $mockSearchService->shouldReceive('setSearch')->with('*')->andReturnSelf();
        $mockSearchService->shouldReceive('fetchResultsTable')->andReturn($userResults);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\Search\Search')->andReturn($mockSearchService);
        $this->controller->setServiceLocator($mockSl);

        $viewBuilder = m::mock();
        $viewBuilder->shouldReceive('buildViewFromTemplate')->with('layout/admin-search-results')->once();
        $this->controller->shouldReceive('viewBuilder')->andReturn($viewBuilder);

        $this->controller->indexAction();
    }

    /**
     * Tests the getForm on initial request
     */
    public function testGetFormNoPost()
    {
        $mockForm = m::mock('\Zend\Form\Form');

        $mockFormHelper = m::mock();
        $mockFormHelper->shouldReceive('createForm')->andReturn($mockForm);
        $mockFormHelper->shouldReceive('setFormActionFromRequest');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturn(m::type('string'));
        $mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $this->controller->setServiceLocator($mockSl);

        $this->controller->shouldReceive('processApplicationTransportManagerLookup')->never();

        $form = $this->controller->getForm('user');
        $this->assertSame($form, $mockForm);

    }

    /**
     * Tests the getForm on POST of a valid application Id. Returns 2 TMs
     */
    public function testGetFormLookupValidApplication()
    {
        $applicationId = 99;

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

        $this->controller->getRequest()->setMethod('post');
        $this->controller->getRequest()->setPost(new \Zend\Stdlib\Parameters($post));

        $mockForm = m::mock('\Zend\Form\Form');

        $transportManagerElement = m::mock('\Zend\Form\Element');
        $transportManagerElement->shouldReceive('setValueOptions')->with($tmList);

        $userTypeFieldset = m::mock('\Zend\Form\Fieldset');
        $userTypeFieldset->shouldReceive('get')->with('transportManager')
            ->andReturn($transportManagerElement);

        $mockForm->shouldReceive('get')->with('userType')->andReturn($userTypeFieldset);

        $mockFormHelper = m::mock();
        $mockFormHelper->shouldReceive('createForm')->andReturn($mockForm);
        $mockFormHelper->shouldReceive('setFormActionFromRequest');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $this->controller->setServiceLocator($mockSl);

        $this->controller->shouldReceive('fetchTmListOptionsByApplicationId')
            ->with($applicationId)
            ->once()
            ->andReturn($tmList);

        $form = $this->controller->getForm('user');
        $this->assertSame($form, $mockForm);
    }

    /**
     * Tests the getForm on POST of an invalid application Id. Returns no TMs
     */
    public function testGetFormLookupInvalidApplication()
    {
        $applicationId = 99;

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

        $this->controller->getRequest()->setMethod('post');
        $this->controller->getRequest()->setPost(new \Zend\Stdlib\Parameters($post));

        $mockForm = m::mock('\Zend\Form\Form');

        $applicationElement = m::mock('\Zend\Form\Element');
        $applicationElement->shouldReceive('setMessages')->with(m::type('array'))->once();

        $applicationTransportManagerElement = m::mock('\Zend\Form\Element');
        $applicationTransportManagerElement->shouldReceive('get')->with('application')
            ->andReturn($applicationElement);

        $userTypeFieldset = m::mock('\Zend\Form\Fieldset');
        $userTypeFieldset->shouldReceive('get')->with('applicationTransportManagers')
            ->andReturn($applicationTransportManagerElement);

        $mockForm->shouldReceive('get')->with('userType')->andReturn($userTypeFieldset);

        $mockFormHelper = m::mock();
        $mockFormHelper->shouldReceive('createForm')->andReturn($mockForm);
        $mockFormHelper->shouldReceive('setFormActionFromRequest');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $this->controller->setServiceLocator($mockSl);

        $this->controller->shouldReceive('fetchTmListOptionsByApplicationId')
            ->with($applicationId)
            ->once()
            ->andReturn($tmList);

        $form = $this->controller->getForm('user');

        $this->assertSame($form, $mockForm);
    }

    public function testFetchTmListOptionsByApplicationId()
    {
        $applicationId = 99;

        $data = [
            'results' => [
                [
                    'transportManager' => [
                        'id' => 10,
                        'homeCd' => [
                            'person' => [
                                'forename' => 'fn1',
                                'familyName' => 'ln1',
                            ]
                        ]
                    ]
                ],
                [
                    'transportManager' => [
                        'id' => 11,
                        'homeCd' => [
                            'person' => [
                                'forename' => 'fn2',
                                'familyName' => 'ln2',
                            ]
                        ]
                    ]
                ]
            ],
            'count' => 2,
        ];

        $expected = [
            10 => 'fn1 ln1',
            11 => 'fn2 ln2'
        ];

        $response = m::mock()
            ->shouldReceive('isServerError')
            ->andReturn(false)
            ->shouldReceive('isClientError')
            ->andReturn(false)
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($data)
            ->getMock();

        $this->controller
            ->shouldReceive('handleQuery')
            ->once()
            ->andReturn($response);

        $optionData = $this->controller->fetchTmListOptionsByApplicationId($applicationId);

        $this->assertSame($expected, $optionData);
    }
}
