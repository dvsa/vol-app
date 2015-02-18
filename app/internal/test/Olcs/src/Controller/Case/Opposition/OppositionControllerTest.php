<?php

/**
 * Opposition Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerRouteMatchHelper;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\View\Helper\Placeholder;

/**
 * Opposition Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class OppositionControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    public function setUp()
    {
        $this->sut = new \Olcs\Controller\Cases\Opposition\OppositionController();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->routeMatchHelper = new ControllerRouteMatchHelper();
        parent::setUp();
    }

    /**
     * @dataProvider indexActionDataProvider
     *
     * @param $receivedDate
     * @param $adPlacedDate
     * @param $oorDate
     */
    public function testIndexAction($receivedDate, $adPlacedDate, $oorDate)
    {
        $id = 1;

        $listData = [
            'Results' => [
                0 => [
                    'application' => [
                        'receivedDate' => $receivedDate,
                        'operatingCentres' => [
                            0 => [
                                'adPlacedDate' => $adPlacedDate
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $expectedViewVars = [
            'oooDate' => null,
            'oorDate' => $oorDate
        ];

        $caseId = 24;
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params', 'url' => 'Url']);
        $mockParams = $mockPluginManager->get('params', '');
        $mockUrl = $mockPluginManager->get('url', '');
        $mockParams->shouldReceive('fromPost')->with('action')->andReturnNull();
        $mockParams->shouldReceive('fromQuery')->with('page', 1)->andReturn(1);
        $mockParams->shouldReceive('fromQuery')->with('sort', 'id')->andReturn('id');
        $mockParams->shouldReceive('fromQuery')->with('order', 'DESC')->andReturn('DESC');
        $mockParams->shouldReceive('fromQuery')->with('limit', '10')->andReturn(10);
        $mockParams->shouldReceive('fromQuery')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromQuery')->with('case', '')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Complaint',
            'GET',
            m::type('array'),
            m::type('array')
        )->andReturn([]);

        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Opposition',
            'GET',
            m::type('array'),
            m::type('array')
        )->andReturn([]);

        //placeholders
        $placeholder = new Placeholder();

        //add placeholders to view helper
        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        //mock table builder
        $mockTableBuilder = m::mock('Common\Service\Table\TableBuilder');
        $mockTableBuilder->shouldReceive('buildTable')->withAnyArgs();

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);
        $mockServiceManager->shouldReceive('get')->with('Table')->andReturn($mockTableBuilder);

        $this->sut->setPluginManager($mockPluginManager);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->sut->indexAction();
    }

    public function indexActionDataProvider()
    {
        return [
            ['2014-04-01T09:43:21+0100', '2014-04-01', '2014-04-22T00:00:00+0100'], //dates are fine
            //['2014-04-02T09:43:21+0100', '2014-04-01', null], //received is before the ad placed date
            //['2014-04-02T09:43:21+0100', null, null] //we don't have an ad placed date
        ];
    }

    public function testProcessLoadAddOpposition()
    {
        $mockFormData = [];

        $caseId = 24;

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params']);
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromQuery')->with('case', '')->andReturn($caseId);

        $this->sut->setPluginManager($mockPluginManager);

        $result = $this->sut->processLoad($mockFormData);
        $this->assertArrayHasKey('case', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertEquals($caseId, $result['case']);
        $this->assertEquals($caseId, $result['fields']['case']);
    }

    public function testProcessLoadEditOpposition()
    {
        $caseId = 24;
        $mockFormData = $this->getMockOppositionData();
        $caseData = ['id' => $caseId];

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params']);
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromQuery')->with('case', '')->andReturn($caseId);

        $mockOppositionService = new \Olcs\Service\Data\Mapper\Opposition();

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($caseData);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Mapper\Opposition')
            ->andReturn($mockOppositionService);
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn($mockCaseService);

        $this->sut->setServiceLocator($mockServiceManager);
        $this->sut->setPluginManager($mockPluginManager);

        $result = $this->sut->processLoad($mockFormData);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('opposer', $result);
        $this->assertArrayHasKey('grounds', $result);
        $this->assertArrayHasKey('operatingCentres', $result);
        $this->assertArrayHasKey('contactDetails', $result['opposer']);
        $this->assertArrayHasKey('person', $result['opposer']['contactDetails']);
        $this->assertArrayHasKey('phoneContacts', $result['opposer']['contactDetails']);
        $this->assertArrayHasKey('address', $result['opposer']['contactDetails']);
    }

    public function testProcessSaveAddOpposition()
    {
        $mockFormData = $this->getMockFormData();
        $caseId = 24;
        $caseData = ['id' => $caseId, 'application' => ['id' => 1], 'licence' => ['id' => 7]];

        $oppositionId = 1;

        $restResult = [
            'id' => $oppositionId,
        ];

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'FlashMessenger' => 'FlashMessenger',
                'redirect' => 'Redirect'
            ]
        );
        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage');

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromQuery')->with('case', '')->andReturn($caseId);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            '',
            ['action' => 'index', $this->sut->getIdentifierName() => ''],
            ['code' => '303'], true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $mockOppositionService = new \Olcs\Service\Data\Mapper\Opposition();

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($caseData);

        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($restResult);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Mapper\Opposition')
            ->andReturn($mockOppositionService);
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn($mockCaseService);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->processSave($mockFormData));
    }

    private function getMockOppositionData()
    {
        return [
            'id' => 1,
            'opposer' => [
                'id' => 1,
                'version' => 2,
                'opposerType' => [
                    'id' => 3,
                ],
                'contactDetails' => [
                    'id' => 4,
                    'version' => 5,
                    'description' => 'foo',
                    'emailAddress' => 'bar',
                    'person' => [
                        'id' => 6,
                        'version' => 7,
                        'forename' => 'john',
                        'familyName' => 'smith',
                    ],
                    'phoneContacts' => [
                        0 => [
                            'id' => 8,
                            'version' => 9,
                            'phoneNumber' => '1234'
                        ]
                    ],
                    'address' => 'someAddress'
                ]
            ],
            'grounds' => [
                0 => [
                    'id' => 10
                ]
            ],
            'operatingCentres' => [
                0 => [
                    'id' => 11
                ]
            ],
            [
                'application' => [
                    'id' => 1
                ]
            ]
        ];
    }

    private function getMockFormData()
    {
        return
            array (
                'fields' =>
                    array (
                        'case' => '29',
                        'contactDetailsDescription' => 'bar',
                        'raisedDate' => '2014-02-02',
                        'opposerType' => 'obj_t_rta',
                        'validNotes' => 'foo',
                        'operatingCentres' =>
                            array (
                                0 => '16',
                            ),
                        'grounds' =>
                            array (
                                0 => 'ogf_env',
                                1 => 'ogf_both',
                            ),
                        'notes' => 'foo bar',
                        'forename' => 'John',
                        'familyName' => 'Smith',
                        'phone' => '01234 567890',
                        'emailAddress' => 'test@foobar.com',
                        'id' => '3',
                        'version' => '4',
                        'application' => '',
                        'oppositionType' => 'otf_obj',
                        'opposerId' => '3',
                        'opposerVersion' => '1',
                        'isValid' => 'Y',
                        'isCopied' => 'Y',
                        'isWillingToAttendPi' => 'Y',
                        'isInTime' => 'Y',
                        'isWithdrawn' => 'Y',
                        'status' => 'opp_ack',
                        'contactDetailsType' => 'ct_obj',
                        'contactDetailsId' => '115',
                        'contactDetailsVersion' => '5',
                        'personId' => '79',
                        'personVersion' => '2',
                        'phoneContactId' => '1',
                        'phoneContactVersion' => '2',
                    ),
                'address' =>
                    array (
                        'searchPostcode' =>
                            array (
                                'postcode' => '',
                            ),
                        'addressLine1' => '123 Anystreet',
                        'addressLine2' => 'SomeDistrict',
                        'addressLine3' => '',
                        'addressLine4' => '',
                        'town' => 'Anytown',
                        'postcode' => 'AB12 3CD',
                        'countryCode' => 'GB',
                        'id' => '105',
                        'version' => '2',
                    ),
                'csrf' => '5a0902f53fe904865955f0d3c1153524-976d2c5f91a80272483ff56d4a051a12',
                'form-actions' =>
                    array (
                        'submit' => '',
                        'cancel' => null,
                    ),
            );
    }
}
