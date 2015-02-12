<?php

/**
 * Pi Register Decision Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\Controller\Cases\PublicInquiry\RegisterDecisionController;
use Common\Data\Object\Publication;

/**
 * Pi Register Decision Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class RegisterDecisionControllerTest extends MockeryTestCase
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
        $this->sut = new RegisterDecisionController();

        parent::setUp();
    }

    public function testProcessSave()
    {
        $id = 1;
        $decisionNotes = 'Decision notes';
        $caseId = 24;
        $postData = [
            'form-actions' => [
                'publish' => true
            ]
        ];

        $data = [
            'fields' => [
                'id' => $id,
                'decisionNotes' => $decisionNotes
            ]
        ];

        $publishData = [
            'pi' => $id,
            'text2' =>  $decisionNotes,
            'publicationSectionConst' => 'decisionSectionId'
        ];

        $mockCase = new \Olcs\Data\Object\Cases();
        $mockCase['id'] = $id;

        $publication = new Publication();

        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->andReturn($mockCase);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn([]);

        $pluginHelper = new \Olcs\Service\Utility\PublicationHelper();

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');
        $mockPublicationLink->shouldReceive('createWithData')->with($publishData)->andReturn($publication);
        $mockPublicationLink->shouldReceive('createFromObject')->with($publication, 'DecisionPublicationFilter');

        $pluginHelper->setPublicationLinkService($mockPublicationLink);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Utility\PublicationHelper')
            ->andReturn($pluginHelper);
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($mockPublicationLink);
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn($mockCaseService);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseService);

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
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            'case_pi',
            ['action'=>'details'],
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromPost')->andReturn($postData);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->processSave($data));
    }


    public function testProcessSaveTm()
    {
        $id = 1;
        $decisionNotes = 'Decision notes';
        $caseId = 24;
        $postData = [
            'form-actions' => [
                'publish' => true
            ]
        ];

        $data = [
            'fields' => [
                'id' => $id,
                'decisionNotes' => $decisionNotes,
                'trafficAreas' => [
                    0 => 'B'
                ],
                'pubType' => 'A&D'
            ]
        ];

        $transportManagerId = 4;

        $mockCase = new \Olcs\Data\Object\Cases();
        $mockCase['id'] = $id;
        $mockCase['transportManager'] = [
            'id' => $transportManagerId
        ];

        $publishData = [
            'pi' => $id,
            'text2' =>  $decisionNotes,
            'publicationSectionConst' => 'tmDecisionSectionId',
            'trafficArea' => 'B',
            'pubType' => 'A&D',
            'case' => $mockCase
        ];

        $publication = new Publication();

        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->andReturn($mockCase);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn([]);

        $pluginHelper = new \Olcs\Service\Utility\PublicationHelper();

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');
        $mockPublicationLink->shouldReceive('createWithData')->with($publishData)->andReturn($publication);
        $mockPublicationLink->shouldReceive('createFromObject')->with($publication, 'TmDecisionPublicationFilter');

        $pluginHelper->setPublicationLinkService($mockPublicationLink);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Utility\PublicationHelper')
            ->andReturn($pluginHelper);
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($mockPublicationLink);
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn($mockCaseService);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseService);

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
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            'case_pi',
            ['action'=>'details'],
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromPost')->andReturn($postData);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->processSave($data));
    }

    public function testProcessSaveTmAllTrafficAreasAndPubTypes()
    {
        $id = 1;
        $decisionNotes = 'Decision notes';
        $caseId = 24;
        $postData = [
            'form-actions' => [
                'publish' => true
            ]
        ];

        $data = [
            'fields' => [
                'id' => $id,
                'decisionNotes' => $decisionNotes,
                'trafficAreas' => [
                    0 => 'all'
                ],
                'pubType' => 'all'
            ]
        ];

        $transportManagerId = 4;

        $mockCase = new \Olcs\Data\Object\Cases();
        $mockCase['id'] = $id;
        $mockCase['transportManager'] = [
            'id' => $transportManagerId
        ];

        $publication = new Publication();

        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->andReturn($mockCase);

        $allTrafficAreas = [
            0 => ['id' => 'B']
        ];
        $mockTrafficAreaService = m::mock('Generic\Service\Data\TrafficArea');
        $mockTrafficAreaService->shouldReceive('fetchList')->andReturn($allTrafficAreas);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn([]);

        $pluginHelper = new \Olcs\Service\Utility\PublicationHelper();

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');
        $mockPublicationLink->shouldReceive('createWithData')->with(m::type('array'))->andReturn($publication);
        $mockPublicationLink->shouldReceive('createFromObject')->with($publication, 'TmDecisionPublicationFilter');

        $pluginHelper->setPublicationLinkService($mockPublicationLink);
        $pluginHelper->setTrafficAreaDataService($mockTrafficAreaService);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Utility\PublicationHelper')
            ->andReturn($pluginHelper);
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Generic\Service\Data\TrafficArea')
            ->andReturn($mockTrafficAreaService);
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($mockPublicationLink);
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn($mockCaseService);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseService);

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
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            'case_pi',
            ['action'=>'details'],
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromPost')->andReturn($postData);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->processSave($data));
    }
}
