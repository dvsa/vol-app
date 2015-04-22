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
            'closedDate' => 'im set',
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

    /**
     * @dataProvider processSaveDataProvider
     */
    public function testProcessSave($isOk)
    {
        // Data
        $id = 100;
        $caseId = 1;
        $data = [
            'fields' => ['id' => $id],
            'address' => ['id' => 200]
        ];

        // Mocks
        $mockEnvironmentalComplaintService = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $mockBusinessResponse = m::mock('\Common\BusinessService\Response');
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect',
                'FlashMessenger' => 'FlashMessenger',
                'params' => 'Params'
            ]
        );

        // Expectations
        $mockBusinessResponse->shouldReceive('isOk')
            ->once()
            ->andReturn($isOk);

        $mockEnvironmentalComplaintService->shouldReceive('process')
            ->once()
            ->andReturn($mockBusinessResponse);

        $mockServiceManager->shouldReceive('get')->with('BusinessServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->once()
            ->with('Cases\Complaint\EnvironmentalComplaint')
            ->andReturn($mockEnvironmentalComplaintService);
        $this->sut->setServiceLocator($mockServiceManager);

        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage')->times($isOk ? 1 : 0);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->times($isOk ? 1 : 0)->with(
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

        $this->sut->processSave($data);
    }

    public function processSaveDataProvider()
    {
        return [
            // success
            [true],
            // error
            [false],
        ];
    }

    /**
     * Tests the generate action
     *
     */
    public function testGenerateAction()
    {
        $id = 1;
        $caseId = 12;
        $complaintId = 123;
        $licenceId = 1234;

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'redirect' => 'Redirect'
            ]
        );
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('id')->andReturn($id);
        $mockParams->shouldReceive('fromRoute')->with('complaint')->andReturn($complaintId);
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('licence', null)->andReturn(null);
        $mockParams->shouldReceive('fromQuery')->with('licence', '')->andReturn(null);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->once()->with(
            'case_licence_docs_attachments/entity/generate',
            [
                'case' => $caseId,
                'licence' => $licenceId,
                'entityType' => 'complaint',
                'entityId' => $complaintId
            ]
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $caseData = [
            'id' => $caseId,
            'licence' => [
                'id' => $licenceId
            ]
        ];

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($caseData);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn($mockCaseService);

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->generateAction());
    }
}
