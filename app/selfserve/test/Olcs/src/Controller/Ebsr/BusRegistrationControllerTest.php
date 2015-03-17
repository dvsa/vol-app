<?php

namespace OlcsTest\Controller\Ebsr;

use Olcs\Controller\Ebsr\BusRegistrationController;
use Mockery as m;
use Sortable\Fixture\Transport\Bus;
use Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * Class BusRegistrationControllerTest
 * @package OlcsTest\Controller\Ebsr
 */
class BusRegistrationControllerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testDetailsActionLatestPublication()
    {
        $busRegId = 5;
        $routeNo = 123123;
        $registrationDetails = $this->generateRegistrationDetails($busRegId, $routeNo);

        $variationHistory = [
            [
                'id' => 2
            ]
        ];
        $pluginManagerHelper = new ControllerPluginManagerHelper();

        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(['url' => 'Url','params' => 'Params']);
        $mockParams = $mockPluginManager->get('params', '');

        $mockParams->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId);

        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('bus-reg-variation-history', $variationHistory, m::type('array'), false)
            ->andReturn('table');

        $mockDataService = m::mock('Common\Service\Data\BusReg');
        $mockDataService->shouldReceive('fetchDetail')->with($busRegId)->andReturn($registrationDetails);
        $mockDataService->shouldReceive('fetchVariationHistory')->with($routeNo)->andReturn($variationHistory);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($mockDataService);

        $sut = new BusRegistrationController();
        $sut->setServiceLocator($mockSl);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->detailsAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertEquals('table', $result->variationHistoryTable);
        $this->assertEquals(9912, $result->registrationDetails['npRreferenceNo']);
    }

    /**
     * Tests no BusRegId
     * @expectedException Common\Exception\ResourceNotFoundException
     */
    public function testDetailsActionInvalidBusRegId()
    {
        $busRegId = 5;
        $routeNo = 123123;

        $variationHistory = [
            [
                'id' => 2
            ]
        ];
        $pluginManagerHelper = new ControllerPluginManagerHelper();

        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(['url' => 'Url','params' => 'Params']);
        $mockParams = $mockPluginManager->get('params', '');

        $mockParams->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId);

        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('bus-reg-variation-history', $variationHistory, m::type('array'), false)
            ->andReturn('table');

        $mockDataService = m::mock('Common\Service\Data\BusReg');
        $mockDataService->shouldReceive('fetchDetail')->with($busRegId)->andReturnNull();
        $mockDataService->shouldReceive('fetchVariationHistory')->with($routeNo)->andReturn($variationHistory);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($mockDataService);

        $sut = new BusRegistrationController();
        $sut->setServiceLocator($mockSl);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->detailsAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertEquals('table', $result->variationHistoryTable);
        $this->assertEquals(9912, $result->registrationDetails['npRreferenceNo']);
    }

    /**
     * Tests no publication data
     */
    public function testDetailsActionNoValidPublicationData()
    {
        $busRegId = 5;
        $routeNo = 123123;
        $registrationDetails = $this->generateRegistrationDetails($busRegId, $routeNo);
        $registrationDetails['licence']['publicationLinks'] = [
            0 => [
                'publication' => [
                    'id' => 10,
                    'pubDate' => '2000-01-11',
                    'pubType' => 'A&D',
                    'publicationNo' => 9910
                ]
            ]
        ];

        $variationHistory = [
            [
                'id' => 2
            ]
        ];

        $pluginManagerHelper = new ControllerPluginManagerHelper();

        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(['url' => 'Url','params' => 'Params']);
        $mockParams = $mockPluginManager->get('params', '');

        $mockParams->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId);

        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('bus-reg-variation-history', $variationHistory, m::type('array'), false)
            ->andReturn('table');

        $mockDataService = m::mock('Common\Service\Data\BusReg');
        $mockDataService->shouldReceive('fetchDetail')->with($busRegId)->andReturn($registrationDetails);
        $mockDataService->shouldReceive('fetchVariationHistory')->with($routeNo)->andReturn($variationHistory);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($mockDataService);

        $sut = new BusRegistrationController();
        $sut->setServiceLocator($mockSl);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->detailsAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertEquals('table', $result->variationHistoryTable);
        $this->assertNull($result->registrationDetails['npRreferenceNo']);
    }

    public function testIndexAction()
    {
        $subType = 'ebsrt_new';
        $busRegistrations = [
            [
                'id' => 2
            ]
        ];

        $sut = new BusRegistrationController();

        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(['url' => 'Url','params' => 'Params']);
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('subType')->andReturn($subType);
        $mockParams->shouldReceive('fromRoute')->with('sort')->andReturn('foo');
        $mockParams->shouldReceive('fromRoute')->with('limit')->andReturn(10);
        $mockParams->shouldReceive('fromRoute')->with('order')->andReturn('DESC');
        $mockParams->shouldReceive('fromRoute')->with('page')->andReturn(1);

        $sut->setPluginManager($mockPluginManager);

        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('bus-registrations', ['Results' => $busRegistrations, 'Count' => 10], m::type('array'), false)
            ->andReturn('table');

        $mockForm = m::mock('Zend\Form\Form');

        $mockForm->shouldReceive('hasAttribute');
        $mockForm->shouldReceive('setAttribute');
        $mockForm->shouldReceive('getFieldsets')->andReturn([]);
        $mockForm->shouldReceive('setData')->withAnyArgs();

        $mockFormHelper = m::mock('Common\Form\View\Helper\Form');
        $mockFormHelper->shouldReceive('createForm')->with('BusRegFilterForm')->andReturn($mockForm);

        $mockStringHelper = m::mock('Common\Form\View\Helper\String');
        $mockStringHelper->shouldReceive('dashToCamel')->withAnyArgs()->andReturn('BusRegFilterForm');

        $mockEbsrService = m::mock('\Generic\Service\Data\EbsrSubmission');
        $mockEbsrService->shouldReceive('fetchList')->with(m::type('array'))->andReturn($busRegistrations);
        $mockEbsrService->shouldReceive('getCount')->with('list')->andReturn(10);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $mockSl->shouldReceive('get')->with('Helper\String')->andReturn($mockStringHelper);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Generic\Service\Data\EbsrSubmission')->andReturn($mockEbsrService);

        $sut->setServiceLocator($mockSl);

        $result = $sut->indexAction();

        $children = $result->getChildren();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertNotEmpty($children);
        $this->assertEquals('table', $children[0]->busRegistrationTable);
    }

    public function testPostSearchIndexAction()
    {
        $subType = 'foo';
        $busRegistrations = [
            [
                'id' => 2
            ]
        ];

        $postArray = [
            'fields' => [
                'subType' => $subType
            ]
        ];

        $redirectParams = [
            'subType' => $subType
        ];

        $sut = new BusRegistrationController();
        $sut->getRequest()->setMethod('post');
        $sut->getRequest()->getPost()->set('fields', $postArray['fields']);

        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'url' => 'Url',
                'params' => 'Params',
                'redirect' => 'Redirect'
            ]
        );
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('subType')->andReturn($subType);
        $mockParams->shouldReceive('fromRoute')->with('sort')->andReturn('foo');
        $mockParams->shouldReceive('fromRoute')->with('limit')->andReturn(10);
        $mockParams->shouldReceive('fromRoute')->with('order')->andReturn('DESC');
        $mockParams->shouldReceive('fromRoute')->with('page')->andReturn(1);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(null, $redirectParams, [], false)->andReturn($subType);

        $sut->setPluginManager($mockPluginManager);

        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('bus-registrations', ['Results' => $busRegistrations, 'Count' => 10], m::type('array'), false)
            ->andReturn('table');

        $mockForm = m::mock('Zend\Form\Form');

        $mockForm->shouldReceive('hasAttribute');
        $mockForm->shouldReceive('setAttribute');
        $mockForm->shouldReceive('getFieldsets')->andReturn([]);
        $mockForm->shouldReceive('setData')->withAnyArgs();
        $mockForm->shouldReceive('isValid')->andReturn(true);
        $mockForm->shouldReceive('getData')->andReturn($postArray);

        $mockFormHelper = m::mock('Common\Form\View\Helper\Form');
        $mockFormHelper->shouldReceive('createForm')->with('BusRegFilterForm')->andReturn($mockForm);

        $mockStringHelper = m::mock('Common\Form\View\Helper\String');
        $mockStringHelper->shouldReceive('dashToCamel')->withAnyArgs()->andReturn('BusRegFilterForm');

        $mockEbsrService = m::mock('\Generic\Service\Data\EbsrSubmission');
        $mockEbsrService->shouldReceive('fetchList')->with(m::type('array'))->andReturn($busRegistrations);
        $mockEbsrService->shouldReceive('getCount')->with('list')->andReturn(10);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $mockSl->shouldReceive('get')->with('Helper\String')->andReturn($mockStringHelper);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Generic\Service\Data\EbsrSubmission')->andReturn($mockEbsrService);

        $sut->setServiceLocator($mockSl);

        $result = $sut->indexAction();

        $children = $result->getChildren();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertNotEmpty($children);
        $this->assertEquals('table', $children[0]->busRegistrationTable);
    }

    private function generateRegistrationDetails($busRegId, $routeNo)
    {
        return [
            'id' => $busRegId,
            'routeNo' => $routeNo,
            'licence' => [
                'id' => 110,
                'publicationLinks' => [
                    0 => [
                        'publication' => [
                            'id' => 10,
                            'pubDate' => '2000-01-11',
                            'pubType' => 'A&D',
                            'publicationNo' => 9910
                        ]
                    ],
                    1 => [
                        'publication' => [
                            'id' => 11,
                            'pubDate' => '2000-01-03',
                            'pubType' => 'N&P',
                            'publicationNo' => 9911
                        ]
                    ],
                    2 => [
                        'publication' => [
                            'id' => 12,
                            'pubDate' => '2000-01-05',
                            'pubType' => 'N&P',
                            'publicationNo' => 9912 // latest
                        ]
                    ],
                    3 => [
                        'publication' => [
                            'id' => 13,
                            'pubDate' => '2000-01-04',
                            'pubType' => 'N&P',
                            'publicationNo' => 9913
                        ]
                    ]
                ]
            ]
        ];
    }
}
