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

    protected $testClass = 'Olcs\Controller\Cases\PublicInquiry\HearingController';

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

        $mockCase = new \Olcs\Data\Object\Cases();
        $mockCase['id'] = $id;

        $publication = new Publication();

        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->andReturn($mockCase);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockCase);

        $pluginHelper = new \Olcs\Service\Utility\PublicationHelper();

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');
        $mockPublicationLink->shouldReceive('createWithData')->with($publishData)->andReturn($publication);
        $mockPublicationLink->shouldReceive('createFromObject')->with($publication, 'HearingPublicationFilter');

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

    public function testProcessSaveTm()
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
                'details' => $hearingDetails,
                'trafficAreas' => [
                    0 => 'B'
                ],
                'pubType' => 'A&D'
            ],
            'form-actions' => [
                'publish' => true
            ]
        ];
        $transportManagerId = 4;

        $mockCase = new \Olcs\Data\Object\Cases();
        $mockCase['id'] = $id;
        $mockCase['transportManager'] = [
            'id' => $transportManagerId
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
                'trafficAreas' => [
                    0 => 'B'
                ],
                'pubType' => 'A&D',
                'text2' => $hearingDetails,
                'id' => $id
            ],
            'publicationSectionConst' => 'tmHearingSectionId',
            'case' => $mockCase,
            'trafficArea' => 'B',
            'pubType' => 'A&D',
            'transportManager' => $transportManagerId
        ];

        $publication = new Publication();

        $mockDataService = m::mock('Common\Service\Helper\DataHelperService');
        $mockDataService->shouldReceive('processDataMap')->andReturn([]);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->andReturn($mockCase);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockCase);

        $pluginHelper = new \Olcs\Service\Utility\PublicationHelper();

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Utility\PublicationHelper')
            ->andReturn($pluginHelper);

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');
        $mockPublicationLink->shouldReceive('createWithData')->with($publishData)->andReturn($publication);
        $mockPublicationLink->shouldReceive('createFromObject')->with($publication, 'TmHearingPublicationFilter');

        $pluginHelper->setPublicationLinkService($mockPublicationLink);

        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($mockPublicationLink);
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn($mockCaseService);
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn($mockCaseService);

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

    public function testProcessSaveTmAllTrafficAreasAndPubTypes()
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
                'details' => $hearingDetails,
                'trafficAreas' => [
                    0 => 'all'
                ],
                'pubType' => 'all'
            ],
            'form-actions' => [
                'publish' => true
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
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockCase);

        $pluginHelper = new \Olcs\Service\Utility\PublicationHelper();

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataService);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Utility\PublicationHelper')
            ->andReturn($pluginHelper);
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Generic\Service\Data\TrafficArea')
            ->andReturn($mockTrafficAreaService);

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');
        $mockPublicationLink->shouldReceive('createWithData')->with(m::type('array'))->andReturn($publication);
        $mockPublicationLink->shouldReceive('createFromObject')->with($publication, 'TmHearingPublicationFilter');

        $pluginHelper->setPublicationLinkService($mockPublicationLink);
        $pluginHelper->setTrafficAreaDataService($mockTrafficAreaService);

        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($mockPublicationLink);
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn($mockCaseService);
        $mockServiceManager->shouldReceive('get')
            ->with('Generic\Service\Data\TrafficArea')
            ->andReturn($mockTrafficAreaService);
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn($mockCaseService);

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

    /**
     * Test alterForm for TMs
     * @dataProvider providerAlterForm
     *
     * @param $input
     * @param $expected
     */
    public function testAlterForm($input, $expected)
    {
        $pluginHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginHelper->getMockPluginManager(['params' => 'Params']);

        $caseId = 84;

        $mockResult = $input;

        $mockCase = new \Olcs\Data\Object\Cases();
        $mockCase['id'] = $caseId;
        $mockCase['transportManager'] = $input['transportManager'];

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->andReturn($mockCase);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('id')->andReturn($input['id']);
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockResult);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn($mockCaseService);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->sut->setPluginManager($mockPluginManager);

        $form = new \Zend\Form\Form();

        $fieldset = new \Zend\Form\Fieldset('fields');
        $formActionsFieldset = new \Zend\Form\Fieldset('form-actions');

        $pubTypeField = new \Zend\Form\Element\Select('pubType');
        $trafficAreasField = new \Zend\Form\Element\Select('trafficAreas');
        $publishButtonField = new \Zend\Form\Element\Button('publish');
        $publishButtonField->setLabel('Publish');

        $fieldset->add($pubTypeField);
        $fieldset->add($trafficAreasField);
        $formActionsFieldset->add($publishButtonField);

        $form->add($fieldset);
        $form->add($formActionsFieldset);

        $alteredForm = $this->sut->alterForm($form);

        $newPublishLabel = $alteredForm->get('form-actions')
            ->get('publish')
            ->getLabel();

        $newPubTypeClass = $alteredForm->get('fields')->get('pubType')->getAttribute('class');
        $newTrafficAreasClass = $alteredForm->get('fields')->get('trafficAreas')->getAttribute('class');

        $this->assertEquals($expected['publishLabel'], $newPublishLabel);
        $this->assertEquals($expected['pubTypeClass'], $newPubTypeClass);
        $this->assertEquals($expected['trafficAreasClass'], $newTrafficAreasClass);
    }


    /**
     * Test alterForm for Non-TMs
     *
     */
    public function testAlterFormNonTms()
    {
        $pluginHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginHelper->getMockPluginManager(['params' => 'Params']);

        $caseId = 24;

        $mockResult = [
            'id' => 5,
            'pi' => [
                'publicationLinks' => []
            ]
        ];

        $mockCase = new \Olcs\Data\Object\Cases();
        $mockCase['id'] = $caseId;

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->andReturn($mockCase);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('id')->andReturn(5);
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockResult);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn($mockCaseService);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->sut->setPluginManager($mockPluginManager);

        $form = new \Zend\Form\Form();

        $fieldset = new \Zend\Form\Fieldset('fields');
        $formActionsFieldset = new \Zend\Form\Fieldset('form-actions');

        $pubTypeField = new \Zend\Form\Element\Select('pubType');
        $trafficAreasField = new \Zend\Form\Element\Select('trafficAreas');
        $publishButtonField = new \Zend\Form\Element\Button('publish');
        $publishButtonField->setLabel('Publish');

        $fieldset->add($pubTypeField);
        $fieldset->add($trafficAreasField);
        $formActionsFieldset->add($publishButtonField);

        $form->add($fieldset);
        $form->add($formActionsFieldset);

        $alteredForm = $this->sut->alterForm($form);

        try {
            $pubTypeField= $alteredForm->get('fields')->has('pubType');
        } catch (Exception $e) {
            $this->fail('pubType field still exists');
        }
        try {
            $trafficAreasField = $alteredForm->get('fields')->has('trafficAreas');
        } catch (Exception $e) {
            $this->fail('trafficAreas field still exists');
        }
        $this->assertFalse($pubTypeField);
        $this->assertFalse($trafficAreasField);
    }

    public function providerAlterForm()
    {
        return [
            [
                [
                    'id' => 5,
                    'transportManager' => [
                        'id' => 55
                    ],
                    'pi' => [
                        'publicationLinks' => []
                    ]
                ],
                [
                    'publishLabel' => 'Publish',
                    'pubTypeClass' => null,
                    'trafficAreasClass' => null,
                ]
            ],
            [
                [
                    'id' => 4,
                    'transportManager' => [
                        'id' => 44
                    ],
                    'pi' => [
                        'publicationLinks' => [
                            0 => [
                                'publication' => [
                                    'pubStatus' => [
                                        'id' => 'pub_s_new'
                                    ]
                                ]
                            ],
                            1 => [
                                'publication' => [
                                    'pubStatus' => [
                                        'id' => 'pub_s_generated'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'publishLabel' => 'Republish',
                    'pubTypeClass' => null,
                    'trafficAreasClass' => null,
                ]
            ],
            [
                [
                    'id' => 4,
                    'transportManager' => [
                        'id' => 44
                    ],
                    'pi' => [
                        'publicationLinks' => [
                            0 => [
                                'publication' => [
                                    'pubStatus' => [
                                        'id' => 'pub_s_printed'
                                    ]
                                ]
                            ],
                            1 => [
                                'publication' => [
                                    'pubStatus' => [
                                        'id' => 'pub_s_generated'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'publishLabel' => 'Republish',
                    'pubTypeClass' => null,
                    'trafficAreasClass' => null,
                ]
            ],
            [
                [
                    'id' => 4,
                    'transportManager' => [
                        'id' => 44
                    ],
                    'pi' => [
                        'publicationLinks' => [
                            0 => [
                                'publication' => [
                                    'pubStatus' => [
                                        'id' => 'pub_s_printed'
                                    ]
                                ]
                            ],
                            1 => [
                                'publication' => [
                                    'pubStatus' => [
                                        'id' => 'pub_s_printed'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'publishLabel' => 'Publish',
                    'pubTypeClass' => null,
                    'trafficAreasClass' => null,
                ]
            ]
        ];
    }

    /**
     * Tests the generate action
     *
     */
    public function testGenerateAction()
    {
        $sut = $this->getMock(
            $this->testClass,
            array(
                'getFromRoute',
                'getQueryOrRouteParam',
                'getCase',
                'redirect'
            )
        );

        $getFromRouteValues = [
            'case' => 12,
            'id' => 34
        ];
        $sut->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnCallback(
                    function ($key) use ($getFromRouteValues) {
                        return $getFromRouteValues[$key];
                    }
                )
            );

        $sut->expects($this->once())
            ->method('getQueryOrRouteParam')
            ->with('licence')
            ->will($this->returnValue(null));

        $sut->expects($this->once())
            ->method('getCase')
            ->will(
                $this->returnValue(
                    [
                        'id' => 1234,
                        'licence' => [
                            'id' => 56
                        ]
                    ]
                )
            );

        $redirect = $this->getMock('\stdClass', ['toRoute']);
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_licence_docs_attachments/entity/generate',
                ['case' => 12, 'licence' => 56, 'entityType' => 'hearing', 'entityId' => 34]
            );
        $sut->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $sut->generateAction();
    }
}
