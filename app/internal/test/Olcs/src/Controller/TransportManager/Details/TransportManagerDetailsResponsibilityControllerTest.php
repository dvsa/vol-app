<?php

/**
 * Transport manager details responsibilities controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\TransportManager\Details;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;
use Common\Service\Data\CategoryDataService;
use Zend\View\Model\ViewModel;
use Common\Service\Data\LicenceOperatingCentre;

/**
 * Transport manager details responsibilities controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsResponsibilityControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ServiceManager
     */
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
                'id' => 'tm_t_I'
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
                'id' => 'tm_t_I'
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
    public function setUpAction()
    {
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
        $this->setUpAction();

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
        $this->setUpAction();

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
     * @dataProvider actionProvider
     * @group tmResponsibility
     */
    public function testGetDocuments($action, $key)
    {
        $this->setUpAction();
        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('action')
            ->andReturn($action)
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(2);

        $mockTransportManagerApplication = m::mock()
            ->shouldReceive('getTransportManagerApplication')
            ->with(2)
            ->andReturn(
                [
                    'application' => [
                        'id' => 2
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

        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);
        $this->sm->setService('Entity\TransportManagerLicence', $mockTransportManagerLicence);

        $mockTransportManager = m::mock()
            ->shouldReceive('getDocuments')
            ->with(
                1,
                2,
                $key,
                CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
                CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL
            )
            ->andReturn('documents')
            ->getMock();

        $this->sm->setService('Entity\TransportManager', $mockTransportManager);

        $this->assertEquals('documents', $this->sut->getDocuments());
    }

    /**
     * Action data provider
     *
     */
    public function actionProvider()
    {
        return [
            ['edit-tm-application', 'application'],
            ['edit-tm-licence', 'licence']
        ];
    }

    /**
     * Test process additional information file upload
     *
     * @dataProvider actionProvider
     * @group tmResponsibility
     */
    public function testProcessAdditionalInformationFileUpload($action, $key)
    {
        $this->setUpAction();

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

        $mockDateHelper = m::mock()
            ->shouldReceive('getDate')
            ->andReturn('2015-01-01')
            ->getMock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);
        $this->sm->setService('Entity\TransportManagerLicence', $mockTransportManagerLicence);
        $this->sm->setService('Helper\Date', $mockDateHelper);

        $fileParams = [
            'transportManager' => 1,
            'licence' => 2,
            'issuedDate' => '2015-01-01',
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
        $this->setUpAction();

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('partials/form')
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
        $this->setUpAction();

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

        $post = [
            'details' => [
                'application' => 1
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

        $this->setUpAction();

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('partials/form')
            ->getMock();

        $mockApplication = m::mock()
            ->shouldReceive('getLicenceType')
            ->with(1)
            ->andReturn($appData)
            ->getMock();

        $mockValidator = m::mock()
            ->shouldReceive('setAppData')
            ->with($appData)
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
            'application' => 1,
            'transportManager' => 1,
            'action' => 'A'
        ];

        $mockTransportManagerApplication = m::mock()
            ->shouldReceive('save')
            ->with($tmApplciation)
            ->andReturn(['id' => 1])
            ->getMock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);

        $routeParams = ['transportManager' => 1, 'action' => 'edit-tm-application', 'title' => 1, 'id' => 1];

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
            ->andReturn(1)
            ->shouldReceive('redirectToRoute')
            ->with('transport-manager/details/responsibilities', $routeParams)
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('redirect')
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
        $this->setUpAction();

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
        $this->setUpAction();

        $data = [
            'details' => [
                'id' => 1,
                'version' => 1,
                'tmType' => 'tm_t_I',
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

        $this->mockServicesForApplicationOc(false);

        $stubbedValueOptions = [
            'foo' => 'bar'
        ];

        $mockTmType = m::mock();
        $mockOcElement = m::mock();

        $this->mockApplicationOcService->shouldReceive('fetchListOptions')
            ->with([])
            ->andReturn($stubbedValueOptions);

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('details')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('tmType')
                ->andReturn($mockTmType)
                ->shouldReceive('get')
                ->with('operatingCentres')
                ->andReturn($mockOcElement)
                ->getMock()
            )
            ->shouldReceive('setData')
            ->with($data)
            ->getMock();

        $mockOcElement->shouldReceive('setValueOptions')
            ->with($stubbedValueOptions);

        $mockView = $this->getMockEditView();

        $mockFormHelper = m::mock()
            ->shouldReceive('removeOption')
            ->with($mockTmType, 'tm_t_B')
            ->getMock();

        $this->sm->setService('Helper\Form', $mockFormHelper);

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
        $this->setUpAction();

        $post = [
            'details' => [
                'id' => 1,
                'version' => 1,
                'operatingCentres' => [1],
                'tmType' => 'tm_t_I',
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
        $this->mockServicesForApplicationOc(true);
        $stubbedValueOptions = ['foo' => 'bar'];

        $mockTmType = m::mock();
        $mockOcElement = m::mock();

        $this->mockApplicationOcService->shouldReceive('fetchListOptions')
            ->with([])
            ->andReturn($stubbedValueOptions);

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('details')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('tmType')
                ->andReturn($mockTmType)
                ->shouldReceive('get')
                ->with('operatingCentres')
                ->andReturn($mockOcElement)
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

        $mockOcElement->shouldReceive('setValueOptions')
            ->with($stubbedValueOptions);

        $mockFormHelper = m::mock()
            ->shouldReceive('removeOption')
            ->with($mockTmType, 'tm_t_B')
            ->getMock();

        $this->sm->setService('Helper\Form', $mockFormHelper);

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
        $this->setUpAction();

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
        $this->setUpAction();

        $mockView = m::mock('Zend\View\Model\ViewModel');

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('confirm')
            ->with('Are you sure you want to permanently delete the selected record(s)?')
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
        $this->setUpAction();

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
            ->with('Are you sure you want to permanently delete the selected record(s)?')
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
        $this->setUpAction();

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('confirm')
            ->with('Are you sure you want to permanently delete the selected record(s)?')
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
     * @group tmResponsibility1
     */
    public function testEditTmApplicationActionWithPostFileUpload()
    {
        $this->setUpAction();

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
                'tmType' => 'tm_t_I',
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
        $mockLicenceOperatingService = m::mock();
        $this->sm->setService('Entity\LicenceOperatingCentre', $mockLicenceOperatingService);

        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);

        $mockApplicationOperatingService = m::mock()
            ->shouldReceive('setApplicationId')
            ->with(1)
            ->shouldReceive('setLicenceId')
            ->with(1)
            ->shouldReceive('setLicenceOperatingCentreService')
            ->with($mockLicenceOperatingService)
            ->getMock();

        $this->sm->setService('Olcs\Service\Data\ApplicationOperatingCentre', $mockApplicationOperatingService);

        $stubbedValueOptions = [
            'foo' => 'bar'
        ];

        $mockTmType = m::mock();
        $mockOcElement = m::mock();

        $mockApplicationOperatingService->shouldReceive('fetchListOptions')
            ->with([])
            ->andReturn($stubbedValueOptions);

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('details')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('tmType')
                ->andReturn($mockTmType)
                ->shouldReceive('get')
                ->with('operatingCentres')
                ->andReturn($mockOcElement)
                ->getMock()
            )
            ->shouldReceive('setData')
            ->with($post)
            ->getMock();

        $mockOcElement->shouldReceive('setValueOptions')
            ->with($stubbedValueOptions);

        $mockView = $this->getMockEditView();

        $mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($post)
            ->getMock();

        $mockFormHelper = m::mock()
            ->shouldReceive('removeOption')
            ->with($mockTmType, 'tm_t_B')
            ->getMock();

        $this->sm->setService('Helper\Form', $mockFormHelper);

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
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
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
     * Test delete TM licence action with POST
     *
     * @group tmResponsibility
     */
    public function testDeleteTmLicenceActionWitPost()
    {
        $this->setUpAction();

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('confirm')
            ->with('Are you sure you want to permanently delete the selected record(s)?')
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
        $this->setUpAction();

        $data = [
            'details' => [
                'id' => 1,
                'version' => 1,
                'tmType' => 'tm_t_I',
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

        $this->mockServicesForLicenceOc();
        $mockView = $this->getMockEditView();

        $stubbedValueOptions = [
            'foo' => 'bar'
        ];

        $mockTmType = m::mock();
        $mockOcElement = m::mock();

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
                ->andReturn($mockTmType)
                ->shouldReceive('get')
                ->with('operatingCentres')
                ->andReturn($mockOcElement)
                ->getMock()
            )
            ->shouldReceive('setData')
            ->with($data)
            ->getMock();

        $mockOcElement->shouldReceive('setValueOptions')
            ->with($stubbedValueOptions);

        $mockFormHelper = m::mock()
            ->shouldReceive('removeOption')
            ->with($mockTmType, 'tm_t_B')
            ->getMock();

        $this->sm->setService('Helper\Form', $mockFormHelper);

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
        $this->setUpAction();

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
        $this->setUpAction();

        $mockTransportManagerLicence = m::mock()
            ->shouldReceive('getTransportManagerLicence')
            ->with(1)
            ->andReturn($this->tmLicData)
            ->shouldReceive('save')
            ->with(
                [
                    'id' => 1,
                    'version' => 1,
                    'tmType' => 'tm_t_I',
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
                'tmType' => 'tm_t_I',
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

        $mockTmType = m::mock();
        $mockOcElement = m::mock();

        $mockLicenceOcService->shouldReceive('fetchListOptions')
            ->with([])
            ->andReturn($stubbedValueOptions);

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('details')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('tmType')
                ->andReturn($mockTmType)
                ->shouldReceive('get')
                ->with('operatingCentres')
                ->andReturn($mockOcElement)
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

        $mockOcElement->shouldReceive('setValueOptions')
            ->with($stubbedValueOptions);

        $mockFormHelper = m::mock()
            ->shouldReceive('removeOption')
            ->with($mockTmType, 'tm_t_B')
            ->getMock();

        $this->sm->setService('Helper\Form', $mockFormHelper);

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
        $this->setUpAction();

        $post = [
            'details' => [
                'id' => 1,
                'version' => 1,
                'operatingCentres' => [1],
                'tmType' => 'tm_t_I',
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

        $mockTmType = m::mock();
        $mockOcElement = m::mock();

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
                ->andReturn($mockTmType)
                ->shouldReceive('get')
                ->with('operatingCentres')
                ->andReturn($mockOcElement)
                ->getMock()
            )
            ->shouldReceive('setData')
            ->with($post)
            ->getMock();

        $mockOcElement->shouldReceive('setValueOptions')
            ->with($stubbedValueOptions);

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

        $mockFormHelper = m::mock()
            ->shouldReceive('removeOption')
            ->with($mockTmType, 'tm_t_B')
            ->getMock();

        $this->sm->setService('Helper\Form', $mockFormHelper);

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
                        ['tm_t_I' => 'I', 'tm_t_B' => 'B', 'tm_t_E' => 'E']
                    )
                    ->shouldReceive('setValueOptions')
                    ->with(['tm_t_I' => 'I', 'tm_t_E' => 'E'])
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
                ->shouldReceive('save')
                ->with(
                    [
                        'id' => 1,
                        'version' => 1,
                        'tmType' => 'tm_t_I',
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
        }

        $mockLicenceOperatingService = m::mock();
        $this->sm->setService('Entity\LicenceOperatingCentre', $mockLicenceOperatingService);

        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);

        $this->mockApplicationOcService = m::mock()
            ->shouldReceive('setApplicationId')
            ->with(1)
            ->shouldReceive('setLicenceId')
            ->with(1)
            ->shouldReceive('setLicenceOperatingCentreService')
            ->with($mockLicenceOperatingService)
            ->getMock();

        $this->sm->setService('Olcs\Service\Data\ApplicationOperatingCentre', $this->mockApplicationOcService);
    }
}
