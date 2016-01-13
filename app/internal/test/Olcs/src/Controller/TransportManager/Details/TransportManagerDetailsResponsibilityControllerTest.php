<?php

/**
 * Transport manager details responsibilities controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\TransportManager\Details;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Data\CategoryDataService;
use Zend\View\Model\ViewModel;
use Common\Service\Data\LicenceOperatingCentre;
use Common\Service\Entity\TransportManagerApplicationEntityService;

/**
 * Transport manager details responsibilities controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsResponsibilityControllerTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    protected $mockApplicationOcService;

    protected $mockLicenceOcService;

    protected $tmAppData = [
        'application' => [
            'licence' => [
                'organisation' => [
                    'name' => 'operator'
                ],
                'licNo' => 1,
                'id' => 1
            ],
            'id' => 1
        ],
        'operatingCentres' => [
            [
                'id' => 1
            ]
        ],
        'id' => 1,
        'version' => 1,
        'tmType' => [
            'id' => 'tm_t_i'
        ],
        'isOwner' => 'Y',
        'additionalInformation' => 'ai',
        'tmApplicationStatus' => ['id' => 'tmap_st_tm_signed'],
        'hoursMon' => 1,
        'hoursTue' => 1,
        'hoursWed' => 1,
        'hoursThu' => 1,
        'hoursFri' => 1,
        'hoursSat' => 1,
        'hoursSun' => 1
    ];

    protected $tmLicData = [
        'licence' => [
            'organisation' => [
                'name' => 'operator'
            ],
            'licNo' => 1,
            'id' => 1
        ],
        'operatingCentres' => [
            ['id' => 1]
        ],
        'id' => 1,
        'version' => 1,
        'tmType' => [
            'id' => 'tm_t_i'
        ],
        'additionalInformation' => 'ai',
        'hoursMon' => 1,
        'hoursTue' => 1,
        'hoursWed' => 1,
        'hoursThu' => 1,
        'hoursFri' => 1,
        'hoursSat' => 1,
        'hoursSun' => 1
    ];

    /**
     * Set up action
     */
    public function setUp()
    {
        $this->markTestSkipped();
        $this->sut =
            m::mock('\Olcs\Controller\TransportManager\Details\TransportManagerDetailsResponsibilityController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setEnabledCsrf(false);
    }

    /**
     * Test index action
     *
     * @group tmResponsibility
     */
    public function testIndexAction()
    {
        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/multi-tables')
            ->getMock();

        $mockTable1 = m::mock();
        $mockTable2 = m::mock();

        $this->sut
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getTable')
            ->with('tm.applications', 'applications')
            ->andReturn($mockTable1)
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getTable')
            ->with('tm.licences', 'licences')
            ->andReturn($mockTable2)
            ->shouldReceive('getViewWithTm')
            ->with(['tables' => [$mockTable1, $mockTable2]])
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn(new ViewModel());

        $applicationStatus = [
            'apsts_consideration',
            'apsts_not_submitted',
            'apsts_granted'
        ];
        $mockTransportManagerApplication = m::mock()
            ->shouldReceive('getTransportManagerApplications')
            ->with(1, $applicationStatus)
            ->andReturn('applications')
            ->getMock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);

        $licenceStatus = [
            'lsts_valid',
            'lsts_suspended',
            'lsts_curtailed'
        ];
        $mockTransportManagerLicence = m::mock()
            ->shouldReceive('getTransportManagerLicences')
            ->with(1, $licenceStatus)
            ->andReturn('licences')
            ->getMock();

        $this->sm->setService('Entity\TransportManagerLicence', $mockTransportManagerLicence);

        $response = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test index action
     *
     * @group tmResponsibility
     */
    public function testIndexActionPost()
    {
        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->getMock()
            )
            ->shouldReceive('checkForCrudAction')
            ->andReturn(new \Zend\Http\Response);

        $this->assertInstanceOf('\Zend\Http\Response', $this->sut->indexAction());
    }

    /**
     * Test get documents
     *
     * @group tmResponsibility
     */
    public function testGetDocumentsLicence()
    {
        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('action')
            ->andReturn('edit-tm-licence')
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(2);

        $mockTransportManagerLicence = m::mock()
            ->shouldReceive('getTransportManagerLicence')
            ->with(2)
            ->andReturn(
                [
                    'licence' => [
                        'id' => 2
                    ]
                ]
            )
            ->getMock();

        $this->sm->setService('Entity\TransportManagerLicence', $mockTransportManagerLicence);

        $mockTransportManager = m::mock()
            ->shouldReceive('getDocuments')
            ->with(
                1,
                2,
                'licence',
                CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
                CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL
            )
            ->andReturn('documents')
            ->getMock();

        $this->sm->setService('Entity\TransportManager', $mockTransportManager);

        $this->assertEquals('documents', $this->sut->getDocuments());
    }

    /**
     * Test get documents
     *
     * @group tmResponsibility
     */
    public function testGetDocumentsApplication()
    {
        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('action')
            ->andReturn('edit-tm-application')
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(2);

        $mockTransportManagerHelper = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTransportManagerHelper);

        $mockTransportManagerHelper->shouldReceive('getResponsibilityFiles')
            ->once()
            ->with(1, 2)
            ->andReturn('documents');

        $this->assertEquals('documents', $this->sut->getDocuments());
    }

    /**
     * Action data provider
     *
     */
    public function actionProvider()
    {
        return [
            ['edit-tm-application'],
            ['edit-tm-licence']
        ];
    }

    /**
     * Test process additional information file upload
     *
     * @dataProvider actionProvider
     * @group tmResponsibility
     */
    public function testProcessAdditionalInformationFileUpload($action)
    {
        $mockTransportManagerApplication = m::mock()
            ->shouldReceive('getTransportManagerApplication')
            ->with(2)
            ->andReturn(
                [
                    'application' => [
                        'id' => 1,
                        'licence' => [
                            'id' => 2
                        ]
                    ]
                ]
            )
            ->getMock();

        $mockTransportManagerLicence = m::mock()
            ->shouldReceive('getTransportManagerLicence')
            ->with(2)
            ->andReturn(
                [
                    'licence' => [
                        'id' => 2
                    ]
                ]
            )
            ->getMock();

        $mockTransportManagerHelper = m::mock();

        $this->sm->setService('Helper\TransportManager', $mockTransportManagerHelper);
        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);
        $this->sm->setService('Entity\TransportManagerLicence', $mockTransportManagerLicence);

        $fileData = [
            'transportManager' => 1,
            'issuedDate' => '2015-01-01 10:10:10',
            'description' => 'Additional information',
            'category'    => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL
        ];

        $mockTransportManagerHelper->shouldReceive('getResponsibilityFileData')
            ->once()
            ->with(1, 'file')
            ->andReturn($fileData);

        $fileParams = [
            'transportManager' => 1,
            'licence' => 2,
            'issuedDate' => '2015-01-01 10:10:10',
            'description' => 'Additional information',
            'category'    => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL
        ];
        if ($action == 'edit-tm-application') {
            $fileParams['application'] = 1;
        }
        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('action')
            ->andReturn($action)
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(2)
            ->shouldReceive('uploadFile')
            ->with('file', $fileParams)
            ->andReturn('documents');

        $this->assertEquals('documents', $this->sut->processAdditionalInformationFileUpload('file'));
    }

    /**
     * Test add action
     *
     * @group tmResponsibility
     */
    public function testAddAction()
    {
        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/form')
            ->getMock();

        $this->sut
            ->shouldReceive('getForm')
            ->with('transport-manager-application-small')
            ->andReturn('form')
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('getViewWithTm')
            ->with(['form' => 'form'])
            ->andReturn($mockView)
            ->shouldReceive('formPost')
            ->with('form', 'processAddForm')
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('renderView')
            ->with($mockView, 'Add application')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->addAction());
    }

    /**
     * Test add action with post and cancel button pressed
     *
     * @group tmResponsibility
     */
    public function testAddActionWithPostCancel()
    {
        $this->sut
            ->shouldReceive('getForm')
            ->with('transport-manager-application-small')
            ->andReturn('form')
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->getMock()
            )
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(true)
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRouteAjax')
                ->with(null, ['transportManager' => 1])
                ->andReturn('redirect')
                ->getMock()
            );

        $this->assertEquals('redirect', $this->sut->addAction());
    }

    /**
     * Test add action with post
     *
     * @group tmResponsibility
     */
    public function testAddActionWithPost()
    {
        $applicationId = 1;
        $tmId = 2;
        $licenceId = 3;
        $post = [
            'details' => [
                'application' => $applicationId
            ],
            'form-actions' => [
                'submit'
            ]
        ];

        $appData = [
            'licenceType' => [
                'id' => 'ltyp_sn'
            ],
            'status' => [
                'id' => 'status'
            ]
        ];
        $tmAppData = [
            'Results' => ['foo', 'bar']
        ];

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/form')
            ->getMock();

        $mockApplication = m::mock()
            ->shouldReceive('getLicenceType')
            ->with(1)
            ->andReturn($appData)
            ->shouldReceive('getHeaderData')
            ->with(1)
            ->andReturn(
                [
                    'licence' => [
                        'id' => $licenceId
                    ]
                ]
            )
            ->getMock();

        $mockValidator = m::mock()
            ->shouldReceive('setAppData')
            ->with($appData)
            ->once()
            ->shouldReceive('setTmAppData')
            ->with($tmAppData['Results'])
            ->once()
            ->getMock();

        $this->sm->setService('Entity\Application', $mockApplication);
        $this->sm->setService('applicationIdValidator', $mockValidator);

        $mockForm = m::mock()
            ->shouldReceive('getInputFilter')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('details')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('get')
                    ->with('application')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('getValidatorChain')
                        ->andReturn(
                            m::mock()
                            ->shouldReceive('attach')
                            ->with($mockValidator)
                            ->getMock()
                        )
                        ->getMock()
                    )
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('remove')
            ->with('csrf')
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->getMock();

        $tmApplciation = [
            'application' => $applicationId,
            'transportManager' => $tmId,
            'action' => 'U',
            'tmApplicationStatus' => TransportManagerApplicationEntityService::STATUS_POSTAL_APPLICATION,
        ];

        $mockTransportManagerApplication = m::mock()
            ->shouldReceive('save')
            ->with($tmApplciation)
            ->andReturn(['id' => 1])
            ->once()
            ->shouldReceive('getByApplicationTransportManager')
            ->with($applicationId, $tmId)
            ->andReturn($tmAppData)
            ->once()
            ->getMock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);

        $routeParams = ['transportManager' => $tmId, 'action' => 'edit-tm-application', 'title' => 1, 'id' => 1];

        $this->sut
            ->shouldReceive('getForm')
            ->with('transport-manager-application-small')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($post)
                ->getMock()
            )
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getViewWithTm')
            ->with(['form' => $mockForm])
            ->andReturn($mockView)
            ->shouldReceive('getPersist')
            ->andReturn(true)
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn($tmId)
            ->shouldReceive('redirectToRoute')
            ->with('transport-manager/details/responsibilities', $routeParams)
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('redirect')
                ->getMock()
            );

        $this->sm->setService(
            'Entity\TransportManagerLicence',
            m::mock()
            ->shouldReceive('getByTransportManagerAndLicence')
            ->with($tmId, $licenceId)
            ->andReturn('A')
            ->getMock()
        );

        $response = $this->sut->addAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test edit action
     *
     * @group tmResponsibility
     */
    public function testEditTmApplicationActionWithCancel()
    {
        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('title', 0)
            ->andReturn(0)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(true)
            ->shouldReceive('redirectToIndex')
            ->andReturn('redirect');

        $this->assertEquals('redirect', $this->sut->editTmApplicationAction());
    }

    /**
     * Test editTmApplication action
     *
     * @group tmResponsibility
     */
    public function testEditTmApplicationAction()
    {
        $data = [
            'details' => [
                'id' => 1,
                'version' => 1,
                'tmType' => 'tm_t_i',
                'additionalInformation' => 'ai',
                'tmApplicationStatus' => 'tmap_st_tm_signed',
                'operatingCentres' => [1],
                'isOwner' => 'Y',
                'hoursOfWeek' => [
                    'hoursPerWeekContent' => [
                        'hoursMon' => 1,
                        'hoursTue' => 1,
                        'hoursWed' => 1,
                        'hoursThu' => 1,
                        'hoursFri' => 1,
                        'hoursSat' => 1,
                        'hoursSun' => 1,
                    ]
                ]
            ]
        ];

        $stubbedValueOptions = ['foo' => 'bar'];

        // Mocks
        $mockFieldset = m::mock();
        $mockTmHelper = m::mock();
        $mockForm = m::mock();
        $mockView = $this->getMockEditView();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        $this->mockServicesForApplicationOc(false);

        // Expectations
        $this->mockApplicationOcService->shouldReceive('getForSelect')
            ->with(1)
            ->andReturn($stubbedValueOptions);

        $mockTmHelper->shouldReceive('alterResponsibilitiesFieldset')
            ->with($mockFieldset, $stubbedValueOptions, 'table');

        $mockForm->shouldReceive('get')
            ->with('details')
            ->andReturn($mockFieldset)
            ->shouldReceive('setData')
            ->with($data);

        $this->mockOtherLicenceTable('tm.otherlicences-applications');

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('title', 0)
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('action')
            ->andReturn('edit-tm-application')
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getForm')
            ->with('TransportManagerApplicationOrLicenceFull')
            ->andReturn($mockForm)
            ->shouldReceive('processFiles')
            ->with(
                $mockForm,
                'details->file',
                [$this->sut, 'processAdditionalInformationFileUpload'],
                [$this->sut, 'deleteFile'],
                [$this->sut, 'getDocuments']
            )
            ->andReturn(0)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('formPost')
            ->with($mockForm, 'processEditForm')
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('\Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('loadScripts')
            ->with(['forms/crud-table-handler'])
            ->shouldReceive('getViewWithTm')
            ->with(
                [
                    'form' => $mockForm,
                    'operatorName' => 'operator',
                    'applicationId' => 1,
                    'licNo' => 1
                ]
            )
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView, 'Add application')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->editTmApplicationAction());
    }

    /**
     * Test editTmApplication action with post
     *
     * @group tmResponsibility
     */
    public function testEditTmApplicationActionWithPost()
    {
        $post = [
            'details' => [
                'id' => 1,
                'version' => 1,
                'isOwner' => 'Y',
                'operatingCentres' => [1],
                'tmType' => 'tm_t_i',
                'tmApplicationStatus' => 'tmap_st_tm_signed',
                'hoursOfWeek' => [
                    'hoursPerWeekContent' => [
                        'hoursMon' => 1,
                        'hoursTue' => 1,
                        'hoursWed' => 1,
                        'hoursThu' => 1,
                        'hoursFri' => 1,
                        'hoursSat' => 1,
                        'hoursSun' => 1
                    ]
                ],
                'additionalInformation' => 'ai',
                'file' => [
                    'list' => []
                ]
            ],
            'form-actions' => [
                'submit'
            ]
        ];

        $stubbedValueOptions = ['foo' => 'bar'];

        // Mocks
        $mockFieldset = m::mock();
        $mockTmHelper = m::mock();
        $mockForm = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        $this->mockServicesForApplicationOc(true);

        // Expectations
        $this->mockApplicationOcService->shouldReceive('getForSelect')
            ->with(1)
            ->andReturn($stubbedValueOptions);

        $mockTmHelper->shouldReceive('alterResponsibilitiesFieldset')
            ->with($mockFieldset, $stubbedValueOptions, 'table');

        $mockForm->shouldReceive('get')
            ->with('details')
            ->andReturn($mockFieldset)
            ->shouldReceive('remove')
            ->with('csrf')
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post);

        $this->mockOtherLicenceTable('tm.otherlicences-applications');

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('title', 0)
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('action')
            ->andReturn('edit-tm-application')
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getForm')
            ->with('TransportManagerApplicationOrLicenceFull')
            ->andReturn($mockForm)
            ->shouldReceive('processFiles')
            ->with(
                $mockForm,
                'details->file',
                [$this->sut, 'processAdditionalInformationFileUpload'],
                [$this->sut, 'deleteFile'],
                [$this->sut, 'getDocuments']
            )
            ->andReturn(0)
            ->shouldReceive('checkForCrudAction')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($post)
                ->getMock()
            )
            ->shouldReceive('fromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('flashMessenger')
            ->andReturn(
                m::mock()
                ->shouldReceive('addSuccessMessage')
                ->with('The application has been updated')
                ->getMock()
            )
            ->shouldReceive('loadScripts')
            ->with(['table-actions'])
            ->shouldReceive('redirectToIndex')
            ->andReturn('redirect')
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('redirect')
                ->getMock()
            );

        $response = $this->sut->editTmApplicationAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test index action with post and no action
     *
     * @group tmResponsibility
     */
    public function testIndexActionWithPostNoAction()
    {
        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/multi-tables')
            ->andReturn('view')
            ->getMock();

        $mockTable1 = m::mock();
        $mockTable2 = m::mock();

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->getMock()
            )
            ->shouldReceive('checkForCrudAction')
            ->andReturn(false)
            ->shouldReceive('getApplicationsTable')
            ->andReturn($mockTable1)
            ->shouldReceive('getLicencesTable')
            ->andReturn($mockTable2)
            ->shouldReceive('getViewWithTm')
            ->with(
                ['tables' => [$mockTable1, $mockTable2]]
            )
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn('renderedView');

        $this->assertEquals('renderedView', $this->sut->indexAction());

    }

    /**
     * Test delete TM application action
     *
     * @group tmResponsibility
     */
    public function testDeleteTmApplicationAction()
    {
        $mockView = m::mock('Zend\View\Model\ViewModel');

        $mockTranslator = m::mock()
            ->shouldReceive('translate')
            ->with('transport-manager.responsibilities.delete-question')
            ->andReturn('message')
            ->getMock();
        $this->sm->setService('translator', $mockTranslator);

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('confirm')
            ->with('message')
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn('rendered view');

        $this->assertEquals('rendered view', $this->sut->deleteTmApplicationAction());
    }

    /**
     * Test delete TM application with multiple ids action
     *
     * @group tmResponsibility
     */
    public function testDeleteTmApplicationMultipleAction()
    {
        $mockTranslator = m::mock()
            ->shouldReceive('translate')
            ->with('transport-manager.responsibilities.delete-question')
            ->andReturn('message')
            ->getMock();
        $this->sm->setService('translator', $mockTranslator);

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn('')
            ->shouldReceive('params')
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('fromQuery')
                ->andReturn([1, 2])
                ->getMock()
            )
            ->shouldReceive('confirm')
            ->with('message')
            ->andReturn('redirect')
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('addSuccessMessage')
            ->with('Deleted successfully')
            ->shouldReceive('redirectToIndex')
            ->andReturn('redirect');

        $mockTmApp = m::mock()
            ->shouldReceive('deleteListByIds')
            ->with(['id' => [1, 2]])
            ->getMock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTmApp);

        $this->assertEquals('redirect', $this->sut->deleteTmApplicationAction());
    }

    /**
     * Test delete TM application action with POST
     *
     * @group tmResponsibility
     */
    public function testDeleteTmApplicationActionWitPost()
    {
        $mockTranslator = m::mock()
            ->shouldReceive('translate')
            ->with('transport-manager.responsibilities.delete-question')
            ->andReturn('message')
            ->getMock();
        $this->sm->setService('translator', $mockTranslator);

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('confirm')
            ->with('message')
            ->andReturn('redirect')
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('addSuccessMessage')
            ->with('Deleted successfully')
            ->shouldReceive('redirectToIndex')
            ->andReturn('redirect');

        $mockTmApp = m::mock()
            ->shouldReceive('deleteListByIds')
            ->with(['id' => [1]])
            ->getMock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTmApp);

        $this->assertEquals('redirect', $this->sut->deleteTmApplicationAction());
    }

    /**
     * Test edit action with file upload
     *
     * @group tmResponsibility
     */
    public function testEditTmApplicationActionWithPostFileUpload()
    {
        $mockTransportManagerApplication = m::mock()
            ->shouldReceive('getTransportManagerApplication')
            ->with(1)
            ->andReturn($this->tmAppData)
            ->getMock();

        $post = [
            'details' => [
                'id' => 1,
                'version' => 1,
                'operatingCentres' => [1],
                'tmType' => 'tm_t_i',
                'hoursOfWeek' => [
                    'hoursPerWeekContent' => [
                        'hoursMon' => 1,
                        'hoursTue' => 1,
                        'hoursWed' => 1,
                        'hoursThu' => 1,
                        'hoursFri' => 1,
                        'hoursSat' => 1,
                        'hoursSun' => 1
                    ]
                ],
                'additionalInformation' => 'ai',
                'file' => [
                    'list' => []
                ]
            ],
            'form-actions' => [
                'submit'
            ]
        ];

        $mockTmHelper = m::mock();
        $mockFieldset = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);
        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);

        $mockApplicationOperatingService = m::mock();

        $this->sm->setService('Entity\ApplicationOperatingCentre', $mockApplicationOperatingService);

        $stubbedValueOptions = [
            'foo' => 'bar'
        ];

        $mockApplicationOperatingService->shouldReceive('getForSelect')
            ->with(1)
            ->andReturn($stubbedValueOptions);

        $mockTmHelper->shouldReceive('alterResponsibilitiesFieldset')
            ->with($mockFieldset, $stubbedValueOptions, 'table');

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('details')
            ->andReturn($mockFieldset)
            ->shouldReceive('setData')
            ->with($post)
            ->getMock();

        $mockView = $this->getMockEditView();

        $mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($post)
            ->getMock();

        $this->mockOtherLicenceTable('tm.otherlicences-applications');

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('title', 0)
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('action')
            ->andReturn('edit-tm-application')
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('alterEditForm')
            ->with($mockForm)
            ->andReturn($mockForm)
            ->shouldReceive('getForm')
            ->with('TransportManagerApplicationOrLicenceFull')
            ->andReturn($mockForm)
            ->shouldReceive('processFiles')
            ->andReturn(1)
            ->shouldReceive('checkForCrudAction')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('getViewWithTm')
            ->with(
                [
                    'form' => $mockForm,
                    'operatorName' => 'operator',
                    'applicationId' => 1,
                    'licNo' => 1,
                ]
            )
            ->andReturn($mockView)
            ->shouldReceive('loadScripts')
            ->with(['forms/crud-table-handler'])
            ->shouldReceive('renderView')
            ->with($mockView, 'Add application')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->editTmApplicationAction());
    }

    /**
     * Test delete TM licence action with POST
     *
     * @group tmResponsibility
     */
    public function testDeleteTmLicenceActionWitPost()
    {
        $mockTranslator = m::mock()
            ->shouldReceive('translate')
            ->with('transport-manager.responsibilities.delete-question')
            ->andReturn('message')
            ->getMock();
        $this->sm->setService('translator', $mockTranslator);

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('confirm')
            ->with('message')
            ->andReturn('redirect')
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('addSuccessMessage')
            ->with('Deleted successfully')
            ->shouldReceive('redirectToIndex')
            ->andReturn('redirect');

        $mockTmLic = m::mock()
            ->shouldReceive('deleteListByIds')
            ->with(['id' => [1]])
            ->getMock();

        $this->sm->setService('Entity\TransportManagerLicence', $mockTmLic);

        $this->assertEquals('redirect', $this->sut->deleteTmLicenceAction());
    }

    /**
     * Test editTmLicence action
     *
     * @group tmResponsibility
     */
    public function testEditTmLicenceAction()
    {
        $data = [
            'details' => [
                'id' => 1,
                'version' => 1,
                'tmType' => 'tm_t_i',
                'additionalInformation' => 'ai',
                'operatingCentres' => [1],
                'hoursOfWeek' => [
                    'hoursPerWeekContent' => [
                        'hoursMon' => 1,
                        'hoursTue' => 1,
                        'hoursWed' => 1,
                        'hoursThu' => 1,
                        'hoursFri' => 1,
                        'hoursSat' => 1,
                        'hoursSun' => 1,
                    ]
                ]
            ]
        ];

        $mockTmHelper = m::mock();
        $mockFieldset = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        $this->mockServicesForLicenceOc();
        $mockView = $this->getMockEditView();

        $stubbedValueOptions = [
            'foo' => 'bar'
        ];

        $this->mockLicenceOcService->shouldReceive('fetchListOptions')
            ->with([])
            ->andReturn($stubbedValueOptions);

        $mockTmHelper->shouldReceive('alterResponsibilitiesFieldset')
            ->with($mockFieldset, $stubbedValueOptions, 'table');

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('details')
            ->andReturn($mockFieldset)
            ->shouldReceive('setData')
            ->with($data)
            ->getMock();

        $this->mockFormHelper(true, $mockForm);
        $this->mockOtherLicenceTable('tm.otherlicences-licences');

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('title', 0)
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('action')
            ->andReturn('edit-tm-licence')
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getForm')
            ->with('TransportManagerApplicationOrLicenceFull')
            ->andReturn($mockForm)
            ->shouldReceive('processFiles')
            ->with(
                $mockForm,
                'details->file',
                [$this->sut, 'processAdditionalInformationFileUpload'],
                [$this->sut, 'deleteFile'],
                [$this->sut, 'getDocuments']
            )
            ->andReturn(0)
            ->shouldReceive('checkForCrudAction')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('formPost')
            ->with($mockForm, 'processEditForm')
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('\Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('getViewWithTm')
            ->with(
                [
                    'form' => $mockForm,
                    'operatorName' => 'operator',
                    'licNo' => 1
                ]
            )
            ->andReturn($mockView)
            ->shouldReceive('loadScripts')
            ->with(['forms/crud-table-handler'])
            ->shouldReceive('renderView')
            ->with($mockView, 'Edit licence')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->editTmLicenceAction());
    }

    /**
     * Test tmEditLicence action with cancel pressed
     *
     * @group tmResponsibility
     */
    public function testEditTmLicenceActionWithCancel()
    {
        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('title', 0)
            ->andReturn(0)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(true)
            ->shouldReceive('redirectToIndex')
            ->andReturn('redirect');

        $this->assertEquals('redirect', $this->sut->editTmLicenceAction());
    }

    /**
     * Test editTmLicence action with post
     *
     * @group tmResponsibility
     */
    public function testEditTmLicenceActionWithPost()
    {
        $mockTransportManagerLicence = m::mock()
            ->shouldReceive('getTransportManagerLicence')
            ->with(1)
            ->andReturn($this->tmLicData)
            ->shouldReceive('forceUpdate')
            ->with(
                1,
                [
                    'id' => 1,
                    'version' => 1,
                    'tmType' => 'tm_t_i',
                    'additionalInformation' => 'ai',
                    'hoursMon' => 1,
                    'hoursTue' => 1,
                    'hoursWed' => 1,
                    'hoursThu' => 1,
                    'hoursFri' => 1,
                    'hoursSat' => 1,
                    'hoursSun' => 1,
                    'operatingCentres' => [1]
               ]
            )
            ->getMock();

        $post = [
            'details' => [
                'id' => 1,
                'version' => 1,
                'operatingCentres' => [1],
                'tmType' => 'tm_t_i',
                'hoursOfWeek' => [
                    'hoursPerWeekContent' => [
                        'hoursMon' => 1,
                        'hoursTue' => 1,
                        'hoursWed' => 1,
                        'hoursThu' => 1,
                        'hoursFri' => 1,
                        'hoursSat' => 1,
                        'hoursSun' => 1
                    ]
                ],
                'additionalInformation' => 'ai',
                'file' => [
                    'list' => []
                ]
            ],
            'form-actions' => [
                'submit'
            ]
        ];
        $mockTmHelper = m::mock();
        $mockFieldset = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);
        $this->sm->setService('Entity\TransportManagerLicence', $mockTransportManagerLicence);

        $mockDataLicence = m::mock()
            ->shouldReceive('setId')
            ->with(1)
            ->getMock();

        $this->sm->setService('Common\Service\Data\Licence', $mockDataLicence);

        $mockLicenceOcService = m::mock()
            ->shouldReceive('setOutputType')
            ->with(LicenceOperatingCentre::OUTPUT_TYPE_PARTIAL)
            ->getMock();

        $this->sm->setService('Common\Service\Data\LicenceOperatingCentre', $mockLicenceOcService);

        $stubbedValueOptions = [
            'foo' => 'bar'
        ];

        $mockTmHelper->shouldReceive('alterResponsibilitiesFieldset')
            ->with($mockFieldset, $stubbedValueOptions, 'table');

        $mockLicenceOcService->shouldReceive('fetchListOptions')
            ->with([])
            ->andReturn($stubbedValueOptions);

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('details')
            ->andReturn($mockFieldset)
            ->shouldReceive('remove')
            ->with('csrf')
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->getMock();

        $this->mockFormHelper(true, $mockForm);
        $this->mockOtherLicenceTable('tm.otherlicences-licences');

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('title', 0)
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('action')
            ->andReturn('edit-tm-licence')
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getForm')
            ->with('TransportManagerApplicationOrLicenceFull')
            ->andReturn($mockForm)
            ->shouldReceive('processFiles')
            ->with(
                $mockForm,
                'details->file',
                [$this->sut, 'processAdditionalInformationFileUpload'],
                [$this->sut, 'deleteFile'],
                [$this->sut, 'getDocuments']
            )
            ->andReturn(0)
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromPost')
                ->with('action')
                ->andReturn('')
                ->shouldReceive('fromPost')
                ->with('table')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($post)
                ->getMock()
            )
            ->shouldReceive('fromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('flashMessenger')
            ->andReturn(
                m::mock()
                ->shouldReceive('addSuccessMessage')
                ->with('The licence has been updated')
                ->getMock()
            )
            ->shouldReceive('redirectToIndex')
            ->andReturn('redirect')
            ->shouldReceive('loadScripts')
            ->with(['table-actions'])
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('redirect')
                ->getMock()
            );

        $response = $this->sut->editTmLicenceAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test editTmLicenceAction action with file upload
     *
     * @group tmResponsibility
     */
    public function testEditTmLicenceActionWithPostFileUpload()
    {
        $post = [
            'details' => [
                'id' => 1,
                'version' => 1,
                'operatingCentres' => [1],
                'tmType' => 'tm_t_i',
                'hoursOfWeek' => [
                    'hoursPerWeekContent' => [
                        'hoursMon' => 1,
                        'hoursTue' => 1,
                        'hoursWed' => 1,
                        'hoursThu' => 1,
                        'hoursFri' => 1,
                        'hoursSat' => 1,
                        'hoursSun' => 1
                    ]
                ],
                'additionalInformation' => 'ai',
                'file' => [
                    'list' => []
                ]
            ],
            'form-actions' => [
                'submit'
            ]
        ];
        $this->mockServicesForLicenceOc();

        $stubbedValueOptions = [
            'foo' => 'bar'
        ];

        $this->mockLicenceOcService->shouldReceive('fetchListOptions')
            ->with([])
            ->andReturn($stubbedValueOptions);

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('details')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('tmType')
                ->andReturn('tmTyp')
                ->shouldReceive('get')
                ->with('operatingCentres')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setValueOptions')
                    ->with($stubbedValueOptions)
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('setData')
            ->with($post)
            ->getMock();

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/transport-manager/tm-responsibility-edit')
            ->getMock();

        $mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($post)
            ->getMock();

        $this->mockFormHelper();
        $this->mockOtherLicenceTable('tm.otherlicences-licences');

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('title', 0)
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('action')
            ->andReturn('edit-tm-licence')
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('alterEditForm')
            ->with($mockForm)
            ->andReturn($mockForm)
            ->shouldReceive('getForm')
            ->with('TransportManagerApplicationOrLicenceFull')
            ->andReturn($mockForm)
            ->shouldReceive('processFiles')
            ->andReturn(1)
            ->shouldReceive('checkForCrudAction')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('getViewWithTm')
            ->with(
                [
                    'form' => $mockForm,
                    'operatorName' => 'operator',
                    'licNo' => 1
                ]
            )
            ->andReturn($mockView)
            ->shouldReceive('loadScripts')
            ->with(['forms/crud-table-handler'])
            ->shouldReceive('renderView')
            ->with($mockView, 'Edit licence')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->editTmLicenceAction());
    }

    /**
     * Get mock form
     *
     * @param array $data
     * @return MockForm
     */
    protected function getMockEditForm($data)
    {
        return m::mock()
            ->shouldReceive('get')
            ->with('details')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('tmType')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValueOptions')
                    ->andReturn(
                        ['tm_t_i' => 'I', 'tm_t_b' => 'B', 'tm_t_e' => 'E']
                    )
                    ->shouldReceive('setValueOptions')
                    ->with(['tm_t_i' => 'I', 'tm_t_e' => 'E'])
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('setData')
            ->with($data)
            ->getMock();
    }

    /**
     * Get mock view
     *
     * @return MockView
     */
    protected function getMockEditView()
    {
        return m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/transport-manager/tm-responsibility-edit')
            ->getMock();
    }

    /**
     * Mock services for licence OC
     *
     */
    protected function mockServicesForLicenceOc()
    {
        $mockTransportManagerLicence = m::mock()
            ->shouldReceive('getTransportManagerLicence')
            ->with(1)
            ->andReturn($this->tmLicData)
            ->getMock();

        $this->sm->setService('Entity\TransportManagerLicence', $mockTransportManagerLicence);

        $mockDataLicence = m::mock()
            ->shouldReceive('setId')
            ->with(1)
            ->getMock();

        $this->sm->setService('Common\Service\Data\Licence', $mockDataLicence);

        $this->mockLicenceOcService = m::mock()
            ->shouldReceive('setOutputType')
            ->with(LicenceOperatingCentre::OUTPUT_TYPE_PARTIAL)
            ->getMock();

        $this->sm->setService('Common\Service\Data\LicenceOperatingCentre', $this->mockLicenceOcService);
    }

    /**
     * Mock services for application OC
     *
     * @param bool $mockSave
     */
    protected function mockServicesForApplicationOc($mockSave = false)
    {
        $mockTransportManagerApplication = m::mock()
            ->shouldReceive('getTransportManagerApplication')
            ->with(1)
            ->andReturn($this->tmAppData)
            ->getMock();

        if ($mockSave) {
            $mockTransportManagerApplication
                ->shouldReceive('forceUpdate')
                ->with(
                    1,
                    [
                        'id' => 1,
                        'version' => 1,
                        'tmType' => 'tm_t_i',
                        'isOwner' => 'Y',
                        'additionalInformation' => 'ai',
                        'tmApplicationStatus' => 'tmap_st_tm_signed',
                        'hoursMon' => 1,
                        'hoursTue' => 1,
                        'hoursWed' => 1,
                        'hoursThu' => 1,
                        'hoursFri' => 1,
                        'hoursSat' => 1,
                        'hoursSun' => 1,
                        'operatingCentres' => [1]
                   ]
                )
                ->getMock();
        }

        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);

        $this->mockApplicationOcService = m::mock();

        $this->sm->setService('Entity\ApplicationOperatingCentre', $this->mockApplicationOcService);
    }

    /**
     * Mock other licence table
     *
     * @param string $tableName
     */
    protected function mockOtherLicenceTable($tableName)
    {
        if ($tableName === 'tm.otherlicences-applications') {
            $method = 'getByTmApplicationId';
        } else {
            $method = 'getByTmLicenceId';
        }
        $mockOtherLicence = m::mock()
            ->shouldReceive($method)
            ->with(1)
            ->andReturn('data')
            ->getMock();
        $this->sm->setService('Entity\OtherLicence', $mockOtherLicence);

        $mockTable = m::mock()
            ->shouldReceive('prepareTable')
            ->with($tableName, 'data')
            ->andReturn('table')
            ->getMock();
        $this->sm->setService('Table', $mockTable);
    }

    /**
     * Mock form helper
     *
     */
    protected function mockFormHelper($isLicence = false, $mockForm = null)
    {
        $mockFormHelper = m::mock()
            ->shouldReceive('removeOption')
            ->with('tmType', 'tm_t_b')
            ->shouldReceive('populateFormTable')
            ->with('tableElement', 'table')
            ->getMock();
        if ($isLicence) {
            $mockFormHelper->shouldReceive('remove')
                ->with($mockForm, 'details->tmApplicationStatus')
                ->once()
                ->getMock();
        }
        $this->sm->setService('Helper\Form', $mockFormHelper);
    }

    /**
     * Test delete other licence action
     *
     * @param string $deleteType
     * @param string $method
     * @dataProvider deleteTypeProvider
     * @group tmResponsibility
     */
    public function testDeleteOtherLicenceLicencesAction($deleteType, $method)
    {
        $mockTranslator = m::mock()
            ->shouldReceive('translate')
            ->with('transport-manager.responsibilities.delete-question')
            ->andReturn('message')
            ->getMock();
        $this->sm->setService('translator', $mockTranslator);

        $this->sut
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromQuery')
                ->with('id')
                ->andReturn(null)
                ->shouldReceive('fromRoute')
                ->with('id')
                ->andReturn(1)
                ->getMock()
            )
            ->shouldReceive('confirm')
            ->with('message')
            ->andReturn(new ViewModel())
            ->shouldReceive('renderView')
            ->andReturn('view');
        $this->mockOtherLicenceService($deleteType);

        $this->assertEquals('view', $this->sut->$method());
    }

    /**
     * Test delete other licence action with post
     *
     * @param string $deleteType
     * @param string $method
     * @dataProvider deleteTypeProvider
     * @group tmResponsibility
     */
    public function testDeleteOtherLicenceLicencesActionWithPost($deleteType, $method)
    {
        $mockTranslator = m::mock()
            ->shouldReceive('translate')
            ->with('transport-manager.responsibilities.delete-question')
            ->andReturn('message')
            ->getMock();
        $this->sm->setService('translator', $mockTranslator);

        $action = ($deleteType == 'transportManagerApplication') ? 'edit-tm-application' : 'edit-tm-licence';
        $this->sut
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromQuery')
                ->with('id')
                ->andReturn(null)
                ->shouldReceive('fromRoute')
                ->with('id')
                ->andReturn([1, 2])
                ->shouldReceive('fromRoute')
                ->with('transportManager')
                ->andReturn(1)
                ->getMock()
            )
            ->shouldReceive('confirm')
            ->with('message')
            ->andReturn('')
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('addSuccessMessage')
            ->with('Deleted successfully')
            ->shouldReceive('getFromRoute')
            ->with('trnsportManager')
            ->andReturn(1)
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRouteAjax')
                ->with(null, ['transportManager' => 1, 'action' => $action, 'id' => 1])
                ->andReturn('redirect')
                ->getMock()
            );

        $this->mockOtherLicenceService($deleteType, true, [1, 2]);
        $this->assertEquals('redirect', $this->sut->$method());
    }

    /**
     * Delete type provider
     */
    public function deleteTypeProvider()
    {
        return [
            ['transportManagerLicence', 'deleteOtherLicenceLicencesAction'],
            ['transportManagerApplication', 'deleteOtherLicenceApplicationsAction']
        ];
    }

    /**
     * Mock other licence service
     *
     * @param string $key
     * @param bool $shouldDelete
     * @param mixed $ids
     */
    protected function mockOtherLicenceService($key, $shouldDelete = false, $ids = null)
    {
        $mockOtherLicence = m::mock()
            ->shouldReceive('getById')
            ->with(1)
            ->andReturn([$key => ['id' => 1]])
            ->getMock();

        if ($shouldDelete) {
            $mockOtherLicence
                ->shouldReceive('deleteListByIds')
                ->with(['id' => $ids])
                ->getMock();
        }

        $this->sm->setService('Entity\OtherLicence', $mockOtherLicence);
    }

    /**
     * Test edit tm application action with crud
     *
     * @group tmResponsibility
     */
    public function testEditTmApplicationWithCrud()
    {
        $post = ['table' => ['action' => 'edit-tm-application']];
        $mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($post)
            ->getMock();

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->getMock();

        $mockFormHelper = m::mock()
            ->shouldReceive('disableEmptyValidation')
            ->with($mockForm)
            ->andReturn($mockForm)
            ->getMock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getFromRoute')
            ->with('title', 0)
            ->andReturn(0)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('action')
            ->andReturn('edit-tm-application')
            ->shouldReceive('getForm')
            ->with('TransportManagerApplicationOrLicenceFull')
            ->andReturn($mockForm)
            ->shouldReceive('alterEditForm')
            ->with($mockForm, 1)
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('processFiles')
            ->andReturn(0)
            ->shouldReceive('checkForCrudAction')
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('\Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturnSelf()
                ->getMock()
            )
            ->shouldReceive('processEditForm')
            ->with($post, false);

        $tmAppData = [
            'application' => [
                'id' => 1,
                'licence' => [
                    'id' => 1
                ]
            ]
        ];
        $mockTransportManagerApplication = m::mock()
            ->shouldReceive('getTransportManagerApplication')
            ->with(1)
            ->andReturn($tmAppData)
            ->getMock();
        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);

        $this->assertInstanceOf('\Zend\Http\Response', $this->sut->editTmApplicationAction());
    }

    /**
     * Test edit tm application action with crud form not valid
     *
     * @group tmResponsibility
     */
    public function testEditTmApplicationWithCrudNotValid()
    {
        $post = ['table' => ['action' => 'edit-tm-application']];
        $mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($post)
            ->shouldReceive('isXmlHttpRequest')
            ->andReturn(true)
            ->getMock();

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->getMock();

        $mockFormHelper = m::mock()
            ->shouldReceive('disableEmptyValidation')
            ->with($mockForm)
            ->andReturn($mockForm)
            ->getMock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getFromRoute')
            ->with('title', 0)
            ->andReturn(0)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getForm')
            ->with('TransportManagerApplicationOrLicenceFull')
            ->andReturn($mockForm)
            ->shouldReceive('alterEditForm')
            ->with($mockForm, 1)
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('processFiles')
            ->andReturn(0)
            ->shouldReceive('checkForCrudAction')
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('\Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('processEditForm')
            ->with($post, false)
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromRoute')
                ->with('transportManager')
                ->andReturn(1)
                ->getMock()
            )
            ->shouldReceive('getView')
            ->andReturn(
                m::mock('\Zend\View\Model\ViewModel')
                ->shouldReceive('setTerminal')
                ->with(true)
                ->shouldReceive('setTemplate')
                ->with('pages/transport-manager/tm-responsibility-edit')
                ->getMock()
            )
            ->shouldReceive('loadScripts')
            ->with(['forms/crud-table-handler'])
            ->shouldReceive('renderView')
            ->andReturn('view');

        $tmAppData = [
            'application' => [
                'id' => 1,
                'licence' => [
                    'id' => 1,
                    'licNo' => '',
                    'organisation' => [
                        'name' => ''
                    ]
                ]
            ]
        ];
        $mockTransportManagerApplication = m::mock()
            ->shouldReceive('getTransportManagerApplication')
            ->with(1)
            ->andReturn($tmAppData)
            ->getMock();
        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);

        $this->assertEquals('view', $this->sut->editTmApplicationAction());
    }

    /**
     * Test edit tm licence action with crud
     *
     * @group tmResponsibility
     */
    public function testEditTmLicenceWithCrud()
    {
        $post = ['table' => ['action' => 'edit-tm-application', 'id' => 3]];
        $mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($post)
            ->getMock();

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->getMock();

        $mockFormHelper = m::mock()
            ->shouldReceive('disableEmptyValidation')
            ->with($mockForm)
            ->andReturn($mockForm)
            ->getMock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('alterEditForm')
            ->with($mockForm)
            ->andReturn($mockForm)
            ->shouldReceive('getForm')
            ->with('TransportManagerApplicationOrLicenceFull')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('processFiles')
            ->andReturn(0)
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock('\Zend\Http\Response')
                ->shouldReceive('toRoute')
                ->andReturnSelf()
                ->getMock()
            )
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('\Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturnSelf()
                ->getMock()
            )
            ->shouldReceive('processEditForm')
            ->with($post, false);

        $tmLicData = [
            'licence' => [
                'id' => 1
            ]
        ];
        $mockTransportManagerLicence = m::mock()
            ->shouldReceive('getTransportManagerLicence')
            ->with(1)
            ->andReturn($tmLicData)
            ->getMock();
        $this->sm->setService('Entity\TransportManagerLicence', $mockTransportManagerLicence);

        $mockLicenceOcService = m::mock()
            ->shouldReceive('setOutputType')
            ->with(LicenceOperatingCentre::OUTPUT_TYPE_PARTIAL)
            ->getMock();
        $this->sm->setService('Common\Service\Data\LicenceOperatingCentre', $mockLicenceOcService);

        $mockLicenceService = m::mock()
            ->shouldReceive('setId')
            ->with($tmLicData['licence']['id'])
            ->getMock();
        $this->sm->setService('Common\Service\Data\Licence', $mockLicenceService);

        $this->assertInstanceOf('\Zend\Http\Response', $this->sut->editTmLicenceAction());
    }

    /**
     * Test edit tm licence action with crud form not valid
     *
     * @group tmResponsibility
     * @param array $crudAction
     */
    public function testEditTmLicenceWithCrudNotValid()
    {
        $post = ['table' => ['action' => 'edit-tm-application', 'id' => 3]];
        $mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($post)
            ->getMock();

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->getMock();

        $mockFormHelper = m::mock()
            ->shouldReceive('disableEmptyValidation')
            ->with($mockForm)
            ->andReturn($mockForm)
            ->getMock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('alterEditForm')
            ->with($mockForm)
            ->andReturn($mockForm)
            ->shouldReceive('getForm')
            ->with('TransportManagerApplicationOrLicenceFull')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('processFiles')
            ->andReturn(0)
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromPost')
                ->with('table')
                ->andReturn(['action' => 'edit-tm-licence'])
                ->getMock()
            )
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock('Zend\Http\Response')
                ->shouldReceive('toRoute')
                ->andReturnSelf()
                ->getMock()
            )
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('\Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('processEditForm')
            ->with($post, false)
            ->shouldReceive('getViewWithTm')
            ->andReturn(
                m::mock()
                ->shouldReceive('setTemplate')
                ->with('pages/transport-manager/tm-responsibility-edit')
                ->getMock()
            )
            ->shouldReceive('loadScripts')
            ->with(['forms/crud-table-handler'])
            ->shouldReceive('renderView')
            ->andReturn('view');

        $tmLicData = [
            'licence' => [
                'id' => 1,
                'licNo' => '',
                'organisation' => [
                    'name' => ''
                ]
            ]
        ];
        $mockTransportManagerLicence = m::mock()
            ->shouldReceive('getTransportManagerLicence')
            ->with(1)
            ->andReturn($tmLicData)
            ->getMock();
        $this->sm->setService('Entity\TransportManagerLicence', $mockTransportManagerLicence);

        $mockLicenceOcService = m::mock()
            ->shouldReceive('setOutputType')
            ->with(LicenceOperatingCentre::OUTPUT_TYPE_PARTIAL)
            ->getMock();
        $this->sm->setService('Common\Service\Data\LicenceOperatingCentre', $mockLicenceOcService);

        $mockLicenceService = m::mock()
            ->shouldReceive('setId')
            ->with($tmLicData['licence']['id'])
            ->getMock();
        $this->sm->setService('Common\Service\Data\Licence', $mockLicenceService);

        $this->assertEquals('view', $this->sut->editTmLicenceAction());
    }

    /**
     * Test edit tm licence action with crud form not valid
     *
     * @group tmResponsibility
     * @param array $crudAction
     */
    public function testEditTmLicenceWithCrudNotValidAlternative()
    {
        $post = ['table' => ['action' => 'edit-tm-application', 'id' => 3]];
        $mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($post)
            ->getMock();

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->getMock();

        $mockFormHelper = m::mock()
            ->shouldReceive('disableEmptyValidation')
            ->with($mockForm)
            ->andReturn($mockForm)
            ->getMock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('alterEditForm')
            ->with($mockForm)
            ->andReturn($mockForm)
            ->shouldReceive('getForm')
            ->with('TransportManagerApplicationOrLicenceFull')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('processFiles')
            ->andReturn(0)
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRoute')
                ->andReturn('redirect')
                ->getMock()
            )
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('\Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('processEditForm')
            ->with($post, false)
            ->shouldReceive('getViewWithTm')
            ->andReturn(
                m::mock()
                ->shouldReceive('setTemplate')
                ->with('pages/transport-manager/tm-responsibility-edit')
                ->getMock()
            )
            ->shouldReceive('loadScripts')
            ->with(['forms/crud-table-handler'])
            ->shouldReceive('renderView')
            ->andReturn('view');

        $tmLicData = [
            'licence' => [
                'id' => 1,
                'licNo' => '',
                'organisation' => [
                    'name' => ''
                ]
            ]
        ];
        $mockTransportManagerLicence = m::mock()
            ->shouldReceive('getTransportManagerLicence')
            ->with(1)
            ->andReturn($tmLicData)
            ->getMock();
        $this->sm->setService('Entity\TransportManagerLicence', $mockTransportManagerLicence);

        $mockLicenceOcService = m::mock()
            ->shouldReceive('setOutputType')
            ->with(LicenceOperatingCentre::OUTPUT_TYPE_PARTIAL)
            ->getMock();
        $this->sm->setService('Common\Service\Data\LicenceOperatingCentre', $mockLicenceOcService);

        $mockLicenceService = m::mock()
            ->shouldReceive('setId')
            ->with($tmLicData['licence']['id'])
            ->getMock();
        $this->sm->setService('Common\Service\Data\Licence', $mockLicenceService);

        $this->assertEquals('view', $this->sut->editTmLicenceAction());
    }

    /**
     * Test edit tm application action with file upload failed
     *
     * @group tmResponsibility
     */
    public function testEditTmApplicationWithUploadFailed()
    {
        $mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn([])
            ->getMock();

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with([])
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->getMock();

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getFromRoute')
            ->with('title', 0)
            ->andReturn(0)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('alterEditForm')
            ->with($mockForm, 1)
            ->andReturn($mockForm)
            ->shouldReceive('getForm')
            ->with('TransportManagerApplicationOrLicenceFull')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('processFiles')
            ->andReturn(0)
            ->shouldReceive('checkForCrudAction')
            ->andReturn(false)
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('getViewWithTm')
            ->andReturn(
                m::mock()
                ->shouldReceive('setTemplate')
                ->andReturn('pages/transport-manager/tm-responsibility-edit')
                ->getMock()
            )
            ->shouldReceive('loadScripts')
            ->with(['forms/crud-table-handler'])
            ->shouldReceive('renderView')
            ->andReturn('view');

        $tmAppData = [
            'application' => [
                'id' => 1,
                'licence' => [
                    'id' => 1,
                    'licNo' => 'licNo',
                    'organisation' => [
                        'name' => 'name'
                    ]
                ]
            ]
        ];

        $mockTransportManagerApplication = m::mock()
            ->shouldReceive('getTransportManagerApplication')
            ->with(1)
            ->andReturn($tmAppData)
            ->getMock();
        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);

        $mockLicenceOc = m::mock();
        $this->sm->setService('Entity\LicenceOperatingCentre', $mockLicenceOc);

        $mockApplicationOcService = m::mock()
            ->shouldReceive('setApplicationId')
            ->with($tmAppData['application']['id'])
            ->shouldReceive('setLicenceId')
            ->with($tmAppData['application']['licence']['id'])
            ->shouldReceive('setLicenceOperatingCentreService')
            ->with($mockLicenceOc)
            ->getMock();
        $this->sm->setService('Common\Service\Data\ApplicationOperatingCentre', $mockApplicationOcService);

        $this->assertEquals('view', $this->sut->editTmApplicationAction());
    }

    /**
     * Test edit tm licence action with uploadFailed
     *
     * @group tmResponsibility
     */
    public function testEditTmLicenceWithUploadFailed()
    {
        $mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn([])
            ->getMock();

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with([])
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->getMock();

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('alterEditForm')
            ->with($mockForm)
            ->andReturn($mockForm)
            ->shouldReceive('getForm')
            ->with('TransportManagerApplicationOrLicenceFull')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('processFiles')
            ->andReturn(0)
            ->shouldReceive('checkForCrudAction')
            ->andReturn(false)
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('getViewWithTm')
            ->andReturn(
                m::mock()
                ->shouldReceive('setTemplate')
                ->andReturn('pages/transport-manager/tm-responsibility-edit')
                ->getMock()
            )
            ->shouldReceive('loadScripts')
            ->with(['forms/crud-table-handler'])
            ->shouldReceive('renderView')
            ->andReturn('view');

        $tmLicData = [
            'licence' => [
                'id' => 1,
                'licNo' => 'licNo',
                'organisation' => [
                    'name' => 'name'
                ]
            ]
        ];
        $mockTransportManagerLicence = m::mock()
            ->shouldReceive('getTransportManagerLicence')
            ->with(1)
            ->andReturn($tmLicData)
            ->getMock();
        $this->sm->setService('Entity\TransportManagerLicence', $mockTransportManagerLicence);

        $mockLicenceOcService = m::mock()
            ->shouldReceive('setOutputType')
            ->with(LicenceOperatingCentre::OUTPUT_TYPE_PARTIAL)
            ->getMock();
        $this->sm->setService('Common\Service\Data\LicenceOperatingCentre', $mockLicenceOcService);

        $mockLicenceService = m::mock()
            ->shouldReceive('setId')
            ->with($tmLicData['licence']['id'])
            ->getMock();
        $this->sm->setService('Common\Service\Data\Licence', $mockLicenceService);

        $this->assertEquals('view', $this->sut->editTmLicenceAction());
    }

    /**
     * Test other licence add action
     *
     * @dataProvider addActionProvider
     * @group tmResponsibility
     */
    public function testOtherLicenceAddAction($action, $redirectAction)
    {
        $data = [
            'data' => [
                'redirectAction' => $redirectAction,
                'redirectId' => 1
            ]
        ];
        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with($data)
            ->getMock();

        $this->sut
            ->shouldReceive('fromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getForm')
            ->with('TmOtherLicence')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->shouldReceive('getPost')
                ->andReturn($data)
                ->getMock()
            )
            ->shouldReceive('formPost')
            ->with($mockForm, 'processOtherLicenceForm')
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('renderView')
            ->andReturn('view');

        $mockTranslator = m::mock()
            ->shouldReceive('translate')
            ->with('internal.transport_manager.responsibilities.other_licence_add')
            ->andReturn('Add message')
            ->getMock();
        $this->sm->setService('translator', $mockTranslator);

        $this->assertEquals('view', $this->sut->$action());
    }

    /**
     * Add action provider
     */
    public function addActionProvider()
    {
        return [
            ['addOtherLicenceLicencesAction', 'edit-tm-licence'],
            ['addOtherLicenceApplicationsAction', 'edit-tm-application'],
        ];
    }

    /**
     * Test other licence add action with cancel
     *
     * @dataProvider addActionProvider
     * @group tmResponsibility
     */
    public function testOtherLicenceAddActionWithCancel($action, $redirectAction)
    {
        $this->sut
            ->shouldReceive('fromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(true)
            ->shouldReceive('redirectToAction')
            ->with($redirectAction, 1)
            ->andReturn('redirect');

        $this->assertEquals('redirect', $this->sut->$action());
    }

    /**
     * Test other licence add action
     *
     * @dataProvider addActionProvider
     * @group tmResponsibility
     */
    public function testOtherLicenceAddActionWithPost($action, $redirectAction)
    {
        $data = [
            'data' => [
                'redirectAction' => $redirectAction,
                'redirectId' => 1
            ]
        ];
        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with($data)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('remove')
            ->with('csrf')
            ->shouldReceive('getData')
            ->andReturn($data)
            ->getMock();

        $this->sut
            ->shouldReceive('fromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getForm')
            ->with('TmOtherLicence')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($data)
                ->getMock()
            )
            ->shouldReceive('redirectToAction')
            ->with($redirectAction, 1)
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('\Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturnSelf()
                ->getMock()
            );

        $key = ($action == 'addOtherLicenceApplicationsAction') ?
            'transportManagerApplication' : 'transportManagerLicence';
        $mockOtherLicence = m::mock()
            ->shouldReceive('save')
            ->with(array_merge($data['data'], [$key => 1]))
            ->getMock();
        $this->sm->setService('Entity\OtherLicence', $mockOtherLicence);

        $this->assertInstanceOf('\Zend\Http\Response', $this->sut->$action());
    }

    /**
     * Edit action provider
     */
    public function editActionProvider()
    {
        return [
            ['editOtherLicenceLicencesAction', 'edit-tm-licence'],
            ['editOtherLicenceApplicationsAction', 'edit-tm-application'],
        ];
    }

    /**
     * Test other licence edit action
     *
     * @dataProvider editActionProvider
     * @group tmResponsibility
     */
    public function testOtherLicenceEditAction($action, $redirectAction)
    {
        $data = [
            'data' => [
                'redirectAction' => $redirectAction,
                'redirectId' => 1,
                'some' => 'field'
            ]
        ];
        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with($data)
            ->getMock();

        $mockOtherLicence = m::mock()
            ->shouldReceive('getById')
            ->with(1)
            ->andReturn(['some' => 'field'])
            ->getMock();
        $this->sm->setService('Entity\OtherLicence', $mockOtherLicence);

        $this->sut
            ->shouldReceive('fromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getTmRecordId')
            ->with(1)
            ->andReturn(1)
            ->shouldReceive('getForm')
            ->with('TmOtherLicence')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->shouldReceive('getPost')
                ->andReturn($data)
                ->getMock()
            )
            ->shouldReceive('formPost')
            ->with($mockForm, 'processOtherLicenceForm')
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('renderView')
            ->andReturn('view');

        $mockTranslator = m::mock()
            ->shouldReceive('translate')
            ->with('internal.transport_manager.responsibilities.other_licence_edit')
            ->andReturn('Edit message')
            ->getMock();
        $this->sm->setService('translator', $mockTranslator);

        $this->assertEquals('view', $this->sut->$action());
    }

    /**
     * @dataProvider dataProviderTestGetActionFromFullActionName
     * @param string $action
     * @param bool   $expectAdd
     */
    public function testGetActionFromFullActionName($action, $expectAdd)
    {
        $shortAction = $this->sut->getActionFromFullActionName($action);

        if ($expectAdd) {
            $this->assertEquals('add', $shortAction);
        } else {
            $this->assertNotEquals('add', $shortAction);
        }
    }

    public function dataProviderTestGetActionFromFullActionName()
    {
        return [
            ['add-other-licence-applications', true],
            ['add-other-licence-licences', true],
            ['add-xxxx', false],
            ['edit-other-licence-licences', false],
            ['xxx', false],
        ];
    }
}
