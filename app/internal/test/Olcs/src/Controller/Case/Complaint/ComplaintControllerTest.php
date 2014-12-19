<?php

/**
 *Complaint Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\Controller\Cases\Complaint\ComplaintController;

/**
 * Pi Register Decision Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ComplaintControllerTest extends MockeryTestCase
{
    /**
     * @var RegisterDecisionController
     */
    protected $sut;

    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    public function setUp()
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->sut = new ComplaintController();

        parent::setUp();
    }

    public function testProcessSaveExisting()
    {
        $id = 1;
        $personId = 2;
        $caseId = 24;
        $complainantContactDetails = 99;
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
                'person' => [
                    'forename' => $previousComplainantForename,
                    'familyName' => $previousComplainantFamilyName
                ]
            ]
        ];

        $personData = [
            'forename' => $complainantForename,
            'familyName' => $complainantFamilyName,
        ];

        $data = [
            'fields' => [
                'id' => $id,
                'complainantContactDetails' => $complainantContactDetails,
                'complainantForename' => $complainantForename,
                'complainantFamilyName' => $complainantFamilyName,
            ]
        ];

        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')
            ->with('Person', 'GET', $personId, $bundle)
            ->andReturn($existingData);
        $mockRestHelper->shouldReceive('makeRestCall')
            ->with('Complaint', 'GET', ['id' => $id], $bundle)
            ->andReturn($existingData);
        $mockRestHelper->shouldReceive('makeRestCall')->with('Complaint', 'POST', [], '')->andReturn($id);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);

        $mockPersonService = m::mock('Common\Service\Data\Interfaces\Updateable');
        $mockPersonService->shouldReceive('save')->with($personData)->andReturn($personId);

        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Generic\Service\Data\Person')
            ->andReturn($mockPersonService);

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
            '',
            ['action'=>'index', 'complaint' => ''],
            ['code' => '303'],
            true
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
        $contactId = 3;
        $caseId = 24;
        $complainantContactDetails = 99;
        $complainantForename = 'John';
        $complainantFamilyName = 'Smith';
        $contactType = 'ct_complainant';

        $personData = [
            'forename' => $complainantForename,
            'familyName' => $complainantFamilyName,
        ];

        $contactData = [
            'person' => $personId,
            'contactType' => $contactType
        ];

        $data = [
            'fields' => [
                'id' => $id,
                'complainantContactDetails' => $complainantContactDetails,
                'complainantForename' => $complainantForename,
                'complainantFamilyName' => $complainantFamilyName,
            ]
        ];

        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->with('Complaint', 'POST', [], '')->andReturn($id);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);

        $mockPersonService = m::mock('Common\Service\Data\Interfaces\Updateable');
        $mockPersonService->shouldReceive('save')->with($personData)->andReturn($personId);

        $mockContactDetailsService = m::mock('Common\Service\Data\Interfaces\Updateable');
        $mockContactDetailsService->shouldReceive('save')->with($contactData)->andReturn($contactId);

        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Generic\Service\Data\Person')
            ->andReturn($mockPersonService);
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
            '',
            ['action'=>'index', 'complaint' => ''],
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('complaint')->andReturn(1);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->processSave($data));
    }

    public function testProcessLoad()
    {
        $complainantForename = 'John';
        $complainantFamilyName = 'Smith';
        $caseId = 24;

        $data = [
            'complainantContactDetails' => [
                'person' => [
                    'forename' => $complainantForename,
                    'familyName' => $complainantFamilyName
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

        $this->sut->processLoad($data);
    }
}
