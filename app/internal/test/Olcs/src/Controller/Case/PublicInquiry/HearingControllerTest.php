<?php

/**
 * Pi Hearing Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\Controller\Cases\PublicInquiry\HearingController;
use Common\Data\Object\Publication;

/**
 * Pi Hearing Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class HearingControllerTest extends MockeryTestCase
{
    /**
     * @var HearingController
     */
    protected $sut;

    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    public function setUp()
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->sut = new HearingController();

        parent::setUp();
    }

    public function testProcessSave()
    {
        $pi = 1;
        $caseId = 24;
        $piVenue = 2;
        $id = 3;
        $hearingDetails = 'hearing details field';
        $postData = [
            'fields' => [
                'piVenue' => $piVenue,
                'piVenueOther' => 'this data will be made null',
                'isCancelled' => 'N',
                'cancelledReason' => 'this data will be made null',
                'cancelledDate' => 'this data will be made null',
                'isAdjourned' => 'N',
                'adjournedReason' => 'this data will be made null',
                'adjournedDate' => 'this data will be made null',
                'pi' => [
                    'id' => $pi,
                    'piStatus' => 'pi_s_schedule'
                ],
                'details' => $hearingDetails
            ],
            'form-actions' => [
                'publish' => true
            ]
        ];

        $publishData = [
            'pi' => [
                'id' => $pi,
                'piStatus' => 'pi_s_schedule'
            ],
            'text2' =>  $hearingDetails,
            'hearingData' => [
                'piVenue' => $piVenue,
                'piVenueOther' => null,
                'isCancelled' => 'N',
                'cancelledReason' => null,
                'cancelledDate' => null,
                'isAdjourned' => 'N',
                'adjournedReason' => null,
                'adjournedDate' => null,
                'pi' => [
                    'id' => $pi,
                    'piStatus' => 'pi_s_schedule'
                ],
                'details' => $hearingDetails,
                'text2' => $hearingDetails,
                'id' => $id
            ],
            'publicationSectionConst' => 'hearingSectionId'
        ];

        $savedData = [
            'id' => $id
        ];

        $mockCase = new \Olcs\Data\Object\Cases();
        $mockCase['id'] = $id;

        $publication = new Publication();

        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->andReturn($mockCase);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockCase);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');
        $mockPublicationLink->shouldReceive('createWithData')->with($publishData)->andReturn($publication);
        $mockPublicationLink->shouldReceive('createFromObject')->with($publication, 'HearingPublicationFilter');

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

        $this->assertEquals('redirectResponse', $this->sut->processSave($postData));
    }

    public function testGetDataForForm()
    {
        $pi = 1;
        $data = [
            'fields' => [
                'pi' => $pi
            ]
        ];

        $controller = $this->getMock(
            'Olcs\Controller\Cases\PublicInquiry\HearingController',
            ['getFromRoute', 'getFormData']
        );

        $controller->expects($this->once())
            ->method('getFromRoute')
            ->with('pi')
            ->will($this->returnValue($pi));

        $controller->expects($this->once())
            ->method('getFormData')
            ->will($this->returnValue([]));

        $this->assertEquals($data, $controller->getDataForForm());
    }

    /**
     * Tests redirectToIndex
     */
    public function testRedirectToIndex()
    {
        $controller = $this->getMock(
            'Olcs\Controller\Cases\PublicInquiry\HearingController',
            ['redirectToRouteAjax']
        );

        $controller->expects($this->once())
            ->method('redirectToRouteAjax')
            ->with(
                $this->equalTo('case_pi'),
                $this->equalTo(['action'=>'details']),
                $this->equalTo(['code' => '303']),
                $this->equalTo(true)
            );

        $controller->redirectToIndex();
    }
}
