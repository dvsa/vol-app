<?php

namespace OlcsTest\Controller\Ebsr;

use Olcs\Controller\Ebsr\BusRegistrationController;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Dvsa\Olcs\Transfer\Query\Ebsr\SubmissionList as SubmissionListQuery;

/**
 * Class BusRegistrationControllerTest
 * @package OlcsTest\Controller\Ebsr
 */
class BusRegistrationControllerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->markTestSkipped();
    }

    public function tearDown()
    {
        m::close();
    }

    private function generateUserDetails($localAuthority = null)
    {
        if (!empty($localAuthority)) {
            $localAuthority = [
                'id' => $localAuthority
            ];
        }

        $userDetails = [
            'id' => 1,
            'localAuthority' => $localAuthority,
        ];

        return $userDetails;
    }

    public function testDetailsActionLatestPublication()
    {
        $busRegId = 5;
        $regNo = 123123;
        $registrationDetails = $this->generateRegistrationDetails($busRegId, $regNo);

        $variationHistory = [
            [
                'id' => 2
            ]
        ];

        // Mock the auth service
        $mockAuthService = m::mock();
        $mockAuthService->shouldReceive('isGranted')
            ->with('selfserve-ebsr-documents')
            ->andReturn(true);

        $mockUserEntity = m::mock('Entity\User');
        $mockUserEntity->shouldReceive('getUserDetails')
            ->withAnyArgs()
            ->andReturn($this->generateUserDetails());

        $mockTxcInbox = m::mock('Entity\TxcInbox');
        $mockTxcInbox->shouldReceive('fetchBusRegDocuments')->with(
            $registrationDetails['id'], null
        )->andReturnNull();

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
        $mockDataService->shouldReceive('fetchVariationHistory')->with($regNo)->andReturn($variationHistory);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($mockDataService);
        $mockSl->shouldReceive('get')->with('ZfcRbac\Service\AuthorizationService')->andReturn($mockAuthService);
        $mockSl->shouldReceive('get')->with('Entity\TxcInbox')->andReturn($mockTxcInbox);
        $mockSl->shouldReceive('get')->with('Entity\User')->andReturn($mockUserEntity);

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
     * @expectedException \Common\Exception\ResourceNotFoundException
     */
    public function testDetailsActionInvalidBusRegId()
    {
        $busRegId = 5;
        $regNo = 123123;

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
        $mockDataService->shouldReceive('fetchVariationHistory')->with($regNo)->andReturn($variationHistory);

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
        $regNo = 123123;
        $registrationDetails = $this->generateRegistrationDetails($busRegId, $regNo);
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

        // Mock the auth service
        $mockAuthService = m::mock();
        $mockAuthService->shouldReceive('isGranted')
            ->with('selfserve-ebsr-documents')
            ->andReturn(true);

        $mockUserEntity = m::mock('Entity\User');
        $mockUserEntity->shouldReceive('getUserDetails')
            ->withAnyArgs()
            ->andReturn($this->generateUserDetails());

        $mockTxcInbox = m::mock('Entity\TxcInbox');
        $mockTxcInbox->shouldReceive('fetchBusRegDocuments')->with(
            $registrationDetails['id'], null
        )->andReturnNull();

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
        $mockDataService->shouldReceive('fetchVariationHistory')->with($regNo)->andReturn($variationHistory);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($mockDataService);
        $mockSl->shouldReceive('get')->with('ZfcRbac\Service\AuthorizationService')->andReturn($mockAuthService);
        $mockSl->shouldReceive('get')->with('Entity\TxcInbox')->andReturn($mockTxcInbox);
        $mockSl->shouldReceive('get')->with('Entity\User')->andReturn($mockUserEntity);

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
        $status = 'ebsrs_expired';
        $busRegResult = [
            'id' => 2
        ];

        $resultCount = 1;

        $busRegistrations = [
            'result' => $busRegResult,
            'count' => $resultCount
        ];

        $sut = new BusRegistrationController();

        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            ['url' => 'Url','params' => 'Params', 'handleQuery' => 'HandleQuery']
        );
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('subType')->andReturn($subType);
        $mockParams->shouldReceive('fromRoute')->with('status')->andReturn($status);
        $mockParams->shouldReceive('fromRoute')->with('sort')->andReturn('foo');
        $mockParams->shouldReceive('fromRoute')->with('limit')->andReturn(10);
        $mockParams->shouldReceive('fromRoute')->with('order')->andReturn('DESC');
        $mockParams->shouldReceive('fromRoute')->with('page')->andReturn(1);

        $mockHandleQuery = $mockPluginManager->get('handleQuery', '');
        $mockHandleQuery->shouldReceive('getResult')->andReturn($busRegistrations);

        $sut->setPluginManager($mockPluginManager);

        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('bus-registrations', ['Results' => $busRegResult, 'Count' => $resultCount], m::type('array'), false)
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

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $mockSl->shouldReceive('get')->with('Helper\String')->andReturn($mockStringHelper);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();

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
        $status = 'st1';

        $busRegResult = [
            'id' => 2
        ];

        $resultCount = 1;

        $busRegistrations = [
            'result' => $busRegResult,
            'count' => $resultCount
        ];

        $postArray = [
            'fields' => [
                'subType' => $subType,
                'status' => $status
            ]
        ];

        $redirectParams = [
            'subType' => $subType,
            'status' => $status
        ];

        $sut = new BusRegistrationController();

        $sut->getRequest()->setMethod('post');
        $sut->getRequest()->getPost()->set('fields', $postArray['fields']);

        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'url' => 'Url',
                'params' => 'Params',
                'redirect' => 'Redirect',
                'handleQuery' => 'HandleQuery'
            ]
        );
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('subType')->andReturn($subType);
        $mockParams->shouldReceive('fromRoute')->with('status')->andReturn($status);
        $mockParams->shouldReceive('fromRoute')->with('sort')->andReturn('foo');
        $mockParams->shouldReceive('fromRoute')->with('limit')->andReturn(10);
        $mockParams->shouldReceive('fromRoute')->with('order')->andReturn('DESC');
        $mockParams->shouldReceive('fromRoute')->with('page')->andReturn(1);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(null, $redirectParams, [], false)->andReturn($subType);

        $mockHandleQuery = $mockPluginManager->get('handleQuery', '');
        $mockHandleQuery->shouldReceive('getResult')->andReturn($busRegistrations);

        $sut->setPluginManager($mockPluginManager);

        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('bus-registrations', ['Results' => $busRegResult, 'Count' => $resultCount], m::type('array'), false)
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

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $mockSl->shouldReceive('get')->with('Helper\String')->andReturn($mockStringHelper);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();

        $sut->setServiceLocator($mockSl);

        $result = $sut->indexAction();

        $children = $result->getChildren();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertNotEmpty($children);
        $this->assertEquals('table', $children[0]->busRegistrationTable);
    }

    /**
     * @dataProvider roleDocumentsProvider
     * @param bool $permission
     */
    public function testDetailsActionDocuments($permission)
    {
        $busRegId = 5;
        $regNo = 123123;
        $registrationDetails = $this->generateRegistrationDetails($busRegId, $regNo, true);

        $variationHistory = [
            [
                'id' => 2
            ]
        ];

        $documents = [0 => ['doc1']];

        // Mock the auth service to allow form test to pass through

        // Mock the auth service
        $mockAuthService = m::mock();
        $mockAuthService->shouldReceive('isGranted')
            ->with('selfserve-ebsr-documents')
            ->andReturn($permission);

        $mockUserEntity = m::mock('Entity\User');
        $mockUserEntity->shouldReceive('getUserDetails')
            ->withAnyArgs()
            ->andReturn($this->generateUserDetails());

        $mockTxcInbox = m::mock('Entity\TxcInbox');
        $mockTxcInbox->shouldReceive('fetchBusRegDocuments')->with(
            $registrationDetails['id'], null
        )->andReturn($documents);

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
        $mockDataService->shouldReceive('fetchVariationHistory')->with($regNo)->andReturn($variationHistory);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($mockDataService);
        $mockSl->shouldReceive('get')->with('ZfcRbac\Service\AuthorizationService')->andReturn($mockAuthService);
        $mockSl->shouldReceive('get')->with('Entity\TxcInbox')->andReturn($mockTxcInbox);
        $mockSl->shouldReceive('get')->with('Entity\User')->andReturn($mockUserEntity);

        $sut = new BusRegistrationController();
        $sut->setServiceLocator($mockSl);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->detailsAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertEquals('table', $result->variationHistoryTable);
        $this->assertEquals(9912, $result->registrationDetails['npRreferenceNo']);
        $this->assertEquals($permission, !empty($result->registrationDetails['documents']));
    }

    public function roleDocumentsProvider()
    {
        return [
            [
                true
            ],
            [
                false
            ]
        ];
    }

    private function generateRegistrationDetails($busRegId, $regNo, $includeDocs = false)
    {
        $data = [
            'id' => $busRegId,
            'regNo' => $regNo,
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

        // add some docs
        if ($includeDocs) {
            $data['documents'] = [
                0 => [
                    'identifier' => 'AB1234',
                    'filename' => 'abc.pdf',
                    'description' => 'Some test file'
                ]
            ];
        }
        return $data;
    }
}
