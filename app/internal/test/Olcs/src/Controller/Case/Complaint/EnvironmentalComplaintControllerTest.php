<?php

/**
 * EnvironmentalComplaintController Test
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\Controller\Cases\Complaint\EnvironmentalComplaintController;

/**
 * EnvironmentalComplaintController Test
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class EnvironmentalComplaintControllerTest extends MockeryTestCase
{
    /**
     * @var EnvironmentalComplaintController
     */
    protected $sut;

    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    public function setUp()
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->sut = new EnvironmentalComplaintController();

        parent::setUp();
    }

    public function testProcessLoadOpenComplaint()
    {
        $complainantForename = 'John';
        $complainantFamilyName = 'Smith';
        $caseId = 24;

        $data = [
            'complainantContactDetails' => [
                'person' => [
                    'forename' => $complainantForename,
                    'familyName' => $complainantFamilyName
                ],
                'address' => 'address'
            ],
            'ocComplaints' => [
                0 => [
                    'operatingCentre' => [
                        'id' => 1
                    ]
                ]
            ]
        ];

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params'
            ]
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case', '')->andReturn($caseId);

        $mockParams->shouldReceive('fromQuery')->withAnyArgs();

        $this->sut->setPluginManager($mockPluginManager);

        $result = $this->sut->processLoad($data);

        $this->assertArrayHasKey('complainantContactDetails', $result);
        $this->assertArrayHasKey('person', $result['complainantContactDetails']);
        $this->assertArrayHasKey('address', $result['complainantContactDetails']);
        $this->assertEquals($result['address'], 'address');
        $this->assertEquals($result['ocComplaints'], [0 => 1]);
        $this->assertEquals($result['fields']['isCompliance'], 0);
        $this->assertEquals($result['fields']['case'], 24);
        $this->assertEquals($result['status'], 'ecst_open');
    }

    public function testProcessLoadClosedComplaint()
    {
        $complainantForename = 'John';
        $complainantFamilyName = 'Smith';
        $caseId = 24;

        $data = [
            'complainantContactDetails' => [
                'person' => [
                    'forename' => $complainantForename,
                    'familyName' => $complainantFamilyName
                ],
                'address' => 'address'
            ],
            'closeDate' => 'im set',
            'ocComplaints' => [
                0 => [
                    'operatingCentre' => [
                        'id' => 1
                    ]
                ]
            ]
        ];

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params'
            ]
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case', '')->andReturn($caseId);

        $mockParams->shouldReceive('fromQuery')->withAnyArgs();

        $this->sut->setPluginManager($mockPluginManager);

        $result = $this->sut->processLoad($data);

        $this->assertArrayHasKey('complainantContactDetails', $result);
        $this->assertArrayHasKey('person', $result['complainantContactDetails']);
        $this->assertArrayHasKey('address', $result['complainantContactDetails']);
        $this->assertEquals($result['address'], 'address');
        $this->assertEquals($result['ocComplaints'], [0 => 1]);
        $this->assertEquals($result['fields']['isCompliance'], 0);
        $this->assertEquals($result['fields']['case'], 24);
        $this->assertEquals($result['status'], 'ecst_closed');

    }


    public function testProcessSaveExisting()
    {
        $id = 1;
        $personId = 2;
        $contactDetailsId = 3;
        $caseId = 24;
        $addressId = 4;
        $complainantContactDetails = [
            'version' => 2
        ];
        $operatingCentreId = 5;
        $complainantForename = 'John';
        $complainantFamilyName = 'Smith';
        $previousComplainantForename = 'Alan';
        $previousComplainantFamilyName = 'Jones';
        $bundle = array(
            'properties' => 'ALL',
            'children' => array(
                'case' => [],
                'complaintType' => [],
                'status' => [],
                'complainantContactDetails' => [
                    'children' => [
                        'person' => [
                            'forename',
                            'familyName'
                        ]
                    ]
                ]
            )
        );

        $existingData = [
            'complainantContactDetails' => [
                'id' => $contactDetailsId,
                'version' => 2,
                'person' => [
                    'forename' => $previousComplainantForename,
                    'familyName' => $previousComplainantFamilyName
                ],
                'address' => [
                    'id' => $addressId
                ]
            ]
        ];

        $personData = [
            'forename' => $complainantForename,
            'familyName' => $complainantFamilyName,
        ];

        $contactDetailsData = [
            'id' => $contactDetailsId,
            'version' => 2,
            'address' => $addressId,
            'person' => $personId
        ];

        $data = [
            'fields' => [
                'id' => $id,
                'complainantContactDetails' => $complainantContactDetails,
                'complainantForename' => $complainantForename,
                'complainantFamilyName' => $complainantFamilyName,
                'status' => 'cst_closed',
                'ocComplaints' => [
                    0 => $operatingCentreId
                ]
            ],
            'address' => 'someAddress',
        ];

        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')
            ->with('Person', 'GET', $personId, $bundle)
            ->andReturn($existingData);
        $mockRestHelper->shouldReceive('makeRestCall')
            ->with('Complaint', 'GET', ['id' => $id], m::type('array'))
            ->andReturn($existingData);
        $mockRestHelper->shouldReceive('makeRestCall')->with('Complaint', 'POST', [], '')->andReturn($id);
        $mockRestHelper->shouldReceive('makeRestCall')->with('OcComplaint', 'DELETE', ['complaint'=> $id], '');
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'OcComplaint',
            'POST',
            [
                'complaint'=> $id,
                'operatingCentre' => $operatingCentreId
            ],
            ''
        );

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);

        $mockAddressEntity = m::mock('Common\Service\Data\Interfaces\Updateable');
        $mockAddressEntity->shouldReceive('save')->with($data['address'])->andReturnNull();

        $mockContactDetailsService = m::mock('Common\Service\Data\Interfaces\Updateable');
        $mockContactDetailsService->shouldReceive('save')->with($contactDetailsData)->andReturn($contactDetailsId);

        $mockPersonService = m::mock('Common\Service\Data\Interfaces\Updateable');
        $mockPersonService->shouldReceive('save')->with($personData)->andReturn($personId);

        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Generic\Service\Data\Person')
            ->andReturn($mockPersonService);

        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Entity\Address')
            ->andReturn($mockAddressEntity);

        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Generic\Service\Data\ContactDetails')
            ->andReturn($mockContactDetailsService);

        $this->sut->setServiceLocator($mockServiceManager);

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect',
                'FlashMessenger' => 'FlashMessenger',
                'DataServiceManager' => 'DataServiceManager',
                'params' => 'Params'
            ]
        );

        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage');

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            'case_opposition',
            ['action'=>'index', 'case' => $caseId],
            ['code' => '303'],
            false
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('complaint')->andReturn($id);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->processSave($data));
    }

    public function testProcessSaveAdd()
    {
        $id = null;
        $personId = 2;
        $contactDetailsId = 3;
        $caseId = 24;
        $addressId = 4;
        $complainantContactDetails = [
            'version' => 2
        ];
        $operatingCentreId = 5;
        $complainantForename = 'John';
        $complainantFamilyName = 'Smith';
        $previousComplainantForename = 'Alan';
        $previousComplainantFamilyName = 'Jones';
        $bundle = array(
            'properties' => 'ALL',
            'children' => array(
                'case' => [],
                'complaintType' => [],
                'status' => [],
                'complainantContactDetails' => [
                    'children' => [
                        'person' => [
                            'forename',
                            'familyName'
                        ]
                    ]
                ]
            )
        );

        $existingData = [
            'complainantContactDetails' => [
                'id' => $contactDetailsId,
                'version' => 2,
                'person' => [
                    'forename' => $previousComplainantForename,
                    'familyName' => $previousComplainantFamilyName
                ],
                'address' => [
                    'id' => $addressId
                ]
            ]
        ];

        $personData = [
            'forename' => $complainantForename,
            'familyName' => $complainantFamilyName,
        ];

        $contactDetailsData = [
            'person' => $personId,
            'address' => $addressId,
            'contactType' => 'ct_complainant'
        ];

        $data = [
            'fields' => [
                'id' => $id,
                'complainantContactDetails' => $complainantContactDetails,
                'complainantForename' => $complainantForename,
                'complainantFamilyName' => $complainantFamilyName,
                'status' => 'cst_open',
                'ocComplaints' => [
                    0 => $operatingCentreId
                ]
            ],
            'address' => 'someAddress',
        ];

        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')
            ->with('Person', 'GET', $personId, $bundle)
            ->andReturn($existingData);
        $mockRestHelper->shouldReceive('makeRestCall')
            ->with('Complaint', 'GET', ['id' => $id], m::type('array'))
            ->andReturn($existingData);
        $mockRestHelper->shouldReceive('makeRestCall')->with('Complaint', 'POST', [], '')->andReturn($id);
        $mockRestHelper->shouldReceive('makeRestCall')->with('OcComplaint', 'DELETE', ['complaint'=> $id], '');
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'OcComplaint',
            'POST',
            [
                'complaint'=> $id,
                'operatingCentre' => $operatingCentreId
            ],
            ''
        );

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);

        $mockAddressEntity = m::mock('Common\Service\Data\Interfaces\Updateable');
        $mockAddressEntity->shouldReceive('save')->with($data['address'])->andReturn(['id' => $addressId]);

        $mockContactDetailsService = m::mock('Common\Service\Data\Interfaces\Updateable');
        $mockContactDetailsService->shouldReceive('save')->with($contactDetailsData)->andReturn($contactDetailsId);

        $mockPersonService = m::mock('Common\Service\Data\Interfaces\Updateable');
        $mockPersonService->shouldReceive('save')->with($personData)->andReturn($personId);

        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Generic\Service\Data\Person')
            ->andReturn($mockPersonService);

        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Entity\Address')
            ->andReturn($mockAddressEntity);

        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Generic\Service\Data\ContactDetails')
            ->andReturn($mockContactDetailsService);

        $this->sut->setServiceLocator($mockServiceManager);

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect',
                'FlashMessenger' => 'FlashMessenger',
                'DataServiceManager' => 'DataServiceManager',
                'params' => 'Params'
            ]
        );

        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage');

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            'case_opposition',
            ['action'=>'index', 'case' => $caseId],
            ['code' => '303'],
            false
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('complaint')->andReturn($id);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->processSave($data));
    }
}
