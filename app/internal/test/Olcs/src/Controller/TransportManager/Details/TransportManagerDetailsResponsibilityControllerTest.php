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
use Common\Service\Entity\TransportManageApplicationEntityService;
use Common\Service\Entity\TransportManageLicenceEntityService;
use Common\Service\Data\CategoryDataService;
use Zend\View\Model\ViewModel;

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
            'tmApplicationOcs' => [
                [
                    'operatingCentre' => [
                        'id' => 1
                    ]
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
            ->with('pages/transport-manager/tm-responsibility')
            ->getMock();

        $this->sut
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getTable')
            ->with('tm.applications', 'applications')
            ->andReturn(
                m::mock()
                ->shouldReceive('render')
                ->andReturn('applicationsTable')
                ->getMock()
            )
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getTable')
            ->with('tm.licences', 'licences')
            ->andReturn(
                m::mock()
                ->shouldReceive('render')
                ->andReturn('licencesTable')
                ->getMock()
            )
            ->shouldReceive('getViewWithTm')
            ->with(['applicationsTable' => 'applicationsTable', 'licencesTable' => 'licencesTable'])
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
     * @group tmResponsibility
     */
    public function testGetDocuments()
    {
        $this->setUpAction();
        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1);

        $mockTransportManager = m::mock()
            ->shouldReceive('getDocuments')
            ->with(
                1,
                CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
                CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL
            )
            ->andReturn('documents')
            ->getMock();

        $this->sm->setService('Entity\TransportManager', $mockTransportManager);

        $this->assertEquals('documents', $this->sut->getDocuments());
    }

    /**
     * Test process additional information file upload
     * 
     * @group tmResponsibility
     */
    public function testProcessAdditionalInformationFileUpload()
    {
        $this->setUpAction();
        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('uploadFile')
            ->with(
                'file',
                [
                    'transportManager' => 1,
                    'description' => 'Additional information',
                    'category'    => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
                    'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL
                ]
            )
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
            'licence' => [
                'licenceType' => [
                    'id' => 'ltyp_sn'
                ]
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
            ->shouldReceive('getDataForProcessing')
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
            'tmApplicationStatus' => 'status',
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
     * Test edit action
     * 
     * @group tmResponsibility
     */
    public function testEditAction()
    {
        $this->setUpAction();

        $data = [
            'details' => [
                'id' => 1,
                'version' => 1,
                'tmType' => 'tm_t_I',
                'additionalInformation' => 'ai',
                'tmApplicationOc' => [1],
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

        $mockTransportManagerApplication = m::mock()
            ->shouldReceive('getTransportManagerApplication')
            ->with(1)
            ->andReturn($this->tmAppData)
            ->getMock();

        $mockLicenceOperatingService = m::mock();
        $this->sm->setService('Entity\LicenceOperatingCentre', $mockLicenceOperatingService);

        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);

        $mockTmApplicationOcService = m::mock()
            ->shouldReceive('setTmApplicationId')
            ->with(1)
            ->shouldReceive('setLicenceId')
            ->with(1)
            ->shouldReceive('setLicenceOperatingCentreService')
            ->with($mockLicenceOperatingService)
            ->getMock();

        $this->sm->setService('Olcs\Service\Data\TmApplicationOc', $mockTmApplicationOcService);

        $mockForm = m::mock()
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

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/transport-manager/tm-responsibility-edit')
            ->getMock();

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('title', 0)
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getForm')
            ->with('transport-manager-application-full')
            ->andReturn($mockForm)
            ->shouldReceive('processFiles')
            ->with(
                $mockForm,
                'details->file',
                [$this->sut, 'processAdditionalInformationFileUpload'],
                [$this->sut, 'deleteTmFile'],
                [$this->sut, 'getDocuments']
            )
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
     * Test edit action with post
     * 
     * @group tmResponsibility
     * @dataProvider filesProvider
     */
    public function testEditActionWithPost($hasFile)
    {
        $this->setUpAction();

        $mockTransportManagerApplication = m::mock()
            ->shouldReceive('getTransportManagerApplication')
            ->with(1)
            ->andReturn($this->tmAppData)
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
               ]
            )
            ->getMock();

        $post = [
            'details' => [
                'id' => 1,
                'version' => 1,
                'tmApplicationOc' => [1],
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
        if ($hasFile) {
            $post['details']['file']['list'] = ['file'];
        }
        $mockLicenceOperatingService = m::mock();
        $this->sm->setService('Entity\LicenceOperatingCentre', $mockLicenceOperatingService);

        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);

        $mockTmApplicationOcService = m::mock()
            ->shouldReceive('setTmApplicationId')
            ->with(1)
            ->shouldReceive('setLicenceId')
            ->with(1)
            ->shouldReceive('setLicenceOperatingCentreService')
            ->with($mockLicenceOperatingService)
            ->getMock();

        $this->sm->setService('Olcs\Service\Data\TmApplicationOc', $mockTmApplicationOcService);

        $mockForm = m::mock()
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
            ->with($post)
            ->shouldReceive('remove')
            ->with('csrf')
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->getMock();

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('title', 0)
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getForm')
            ->with('transport-manager-application-full')
            ->andReturn($mockForm)
            ->shouldReceive('processFiles')
            ->with(
                $mockForm,
                'details->file',
                [$this->sut, 'processAdditionalInformationFileUpload'],
                [$this->sut, 'deleteTmFile'],
                [$this->sut, 'getDocuments']
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

        $mockTmApplicationOperatingCentre = m::mock()
            ->shouldReceive('getAllForTmApplication')
            ->with(1)
            ->andReturn(
                [
                    'Results'=> [
                        [
                            'operatingCentre' => [
                                'id' => 2
                            ]
                        ]
                    ]
                ]
            )
            ->shouldReceive('deleteByTmAppAndIds')
            ->with(1, [2])
            ->shouldReceive('save')
            ->with(
                [
                    'transportManagerApplication' => 1,
                    'operatingCentre' => 1
                ]
            )
            ->getMock();

        $this->sm->setService('Entity\TmApplicationOperatingCentre', $mockTmApplicationOperatingCentre);

        $response = $this->sut->editTmApplicationAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    public function filesProvider()
    {
        return [
            [false],
            [true]
        ];
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
            ->with('pages/transport-manager/tm-responsibility')
            ->andReturn('view')
            ->getMock();

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
            ->andReturn(
                m::mock()
                ->shouldReceive('render')
                ->andReturn('applicationsTable')
                ->getMock()
            )
            ->shouldReceive('getLicencesTable')
            ->andReturn(
                m::mock()
                ->shouldReceive('render')
                ->andReturn('licencesTable')
                ->getMock()
            )
            ->shouldReceive('getViewWithTm')
            ->with(
                ['applicationsTable' => 'applicationsTable', 'licencesTable' => 'licencesTable']
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
            ->with('Are you sure you want to permanently delete this record?')
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn('rendered view');

        $this->assertEquals('rendered view', $this->sut->deleteTmApplicationAction());
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
            ->with('Are you sure you want to permanently delete this record?')
            ->andReturn('redirect')
            ->shouldReceive('addSuccessMessage')
            ->with('Deleted successfully')
            ->shouldReceive('redirectToIndex')
            ->andReturn('redirect');

        $mockTmApp = m::mock()
            ->shouldReceive('delete')
            ->with(1)
            ->getMock();

        $mockTmAppOc = m::mock()
            ->shouldReceive('deleteByTmApplication')
            ->with(1)
            ->getMock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTmApp);
        $this->sm->setService('Entity\TmApplicationOperatingCentre', $mockTmAppOc);

        $this->assertEquals('redirect', $this->sut->deleteTmApplicationAction());
    }
}
