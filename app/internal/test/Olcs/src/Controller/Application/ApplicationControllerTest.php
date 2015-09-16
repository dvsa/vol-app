<?php

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Application;

use Common\RefData;
use CommonTest\Traits\MockDateTrait;
use Dvsa\Olcs\Transfer\Command\ChangeOfEntity\CreateChangeOfEntity as CreateChangeOfEntityCmd;
use Dvsa\Olcs\Transfer\Command\ChangeOfEntity\UpdateChangeOfEntity as UpdateChangeOfEntityCmd;
use Dvsa\Olcs\Transfer\Command\ChangeOfEntity\DeleteChangeOfEntity as DeleteChangeOfEntityCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompletePaymentCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees as PayOutstandingFeesCmd;
use Dvsa\Olcs\Transfer\Query\ChangeOfEntity\ChangeOfEntity as ChangeOfEntityQry;
use Dvsa\Olcs\Transfer\Query\Fee\FeeList as FeeListQry;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentByIdQry;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\Controller\Traits\ControllerTestTrait;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use OlcsTest\Bootstrap;

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationControllerTest extends MockeryTestCase
{
    use ControllerTestTrait,
        MockDateTrait;

    private $mockParams;
    private $mockRouteParams;
    private $pluginManager;

    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

    protected function setUp()
    {
        $this->sm = $this->getServiceManager();

        $this->sut = $this->getMock(
            '\Olcs\Controller\Application\ApplicationController',
            array('render', 'renderView', 'addErrorMessage', 'redirectToList', 'loadScripts', 'getFees')
        );
        $this->sut->setServiceLocator($this->sm);
        $this->pluginManager = $this->sut->getPluginManager();

        return parent::setUp();
    }

    /**
     * @group application_controller
     */
    public function testCaseAction()
    {
        $this->mockRender();

        $serviceName = 'Olcs\Service\Data\Cases';
        $results = ['id' => '1'];

        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(['params' => 'Params', 'url' => 'Url']);

        $query = new \Zend\Stdlib\Parameters();

        $params = [
            'application' => 1,
            'page'    => 1,
            'sort'    => 'id',
            'order'   => 'desc',
            'limit'   => 10,
            'url'     => $mockPluginManager->get('url'),
            'query'   => $query,
        ];

        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('application', '')->andReturn(1);
        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('page', 1)->andReturn(1);
        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('sort', 'id')->andReturn('id');
        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('order', 'desc')->andReturn('desc');
        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('limit', 10)->andReturn(10);

        // deals with getCrudActionFromPost
        $mockPluginManager->get('params', '')->shouldReceive('fromPost')->with('action')->andReturn(null);

        $dataService = m::mock($serviceName);
        $dataService->shouldReceive('fetchList')->andReturn($results);

        $applicationDataService = m::mock($serviceName);
        $applicationDataService->shouldReceive('canHaveCases')->andReturn(true);

        $serviceLocator = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocator->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $serviceLocator->shouldReceive('get')->with($serviceName)->andReturn($dataService);
        $serviceLocator->shouldReceive('get')->with('Common\Service\Data\Application')
            ->andReturn($applicationDataService);

        $tableBuilder = m::mock('Common\Service\Table\TableBuilder');
        $tableBuilder->shouldReceive('buildTable')->with('cases', $results, $params, false)->andReturn('tableContent');

        $serviceLocator->shouldReceive('get')->with('Table')->andReturn($tableBuilder);

        $request = new \Zend\Http\Request();
        $request->setQuery($query);

        $sut = $this->sut;
        $sut->setRequest($request);
        $sut->setPluginManager($mockPluginManager);
        $sut->setServiceLocator($serviceLocator);

        $sut->expects($this->once())
            ->method('loadScripts')
            ->with(['table-actions']);

        $this->assertEquals('partials/table', $sut->caseAction()->getTemplate());
    }

    /**
     * @group application_controller
     */
    public function testOppositionAction()
    {
        $this->markTestSkipped();

        $this->mockController(
            '\Olcs\Controller\Application\ApplicationController'
        );

        $this->sut->shouldReceive('params->fromRoute')
            ->once()
            ->with('application', null)
            ->andReturn(321);

        $mockOppositionService = m::mock('\Common\Service\Entity\OppositionEntityService');
        $this->sm->setService('Entity\Opposition', $mockOppositionService);
        $mockOppositionService->shouldReceive('getForApplication')
            ->once()
            ->with(321)
            ->andReturn(['oppositions']);

        $mockOppositionHelperService = m::mock('\Common\Service\Helper\OppositionHelperService');
        $this->sm->setService('Helper\Opposition', $mockOppositionHelperService);
        $mockOppositionHelperService->shouldReceive('sortOpenClosed')
            ->once()
            ->with(['oppositions'])
            ->andReturn(['sorted-oppositions']);

        $mockCasesService = m::mock('\Common\Service\Entity\CasesEntityService');
        $this->sm->setService('Entity\Cases', $mockCasesService);
        $mockCasesService->shouldReceive('getComplaintsForApplication')
            ->once()
            ->with(321)
            ->andReturn(['complaints']);

        $mockComplaintsHelperService = m::mock('\Common\Service\Helper\ComplaintsHelperService');
        $this->sm->setService('Helper\Complaints', $mockComplaintsHelperService);
        $mockComplaintsHelperService->shouldReceive('sortCasesOpenClosed')
            ->once()
            ->with(['complaints'])
            ->andReturn(['sorted-complaints']);

        $this->sut->shouldReceive('getTable')
            ->once()
            ->with('opposition-readonly', ['sorted-oppositions'])
            ->andReturn('TABLE HTML');
        $this->sut->shouldReceive('getTable')
            ->once()
            ->with('environmental-complaints-readonly', ['sorted-complaints'])
            ->andReturn('TABLE HTML');
        $this->sut->shouldReceive('render')
            ->once()
            ->andReturn('HTML');

        $this->sut->oppositionAction();
    }

    /**
     * @group application_controller
     */
    public function testDocumentsAction()
    {
        $this->markTestSkipped();
        $this->mockController(
            '\Olcs\Controller\Application\ApplicationController'
        );

        $this->sut->shouldReceive('getFromRoute')
            ->with('application')
            ->andReturn(1);

        $this->mockEntity('Application', 'getLicenceIdForApplication')
            ->andReturn(7);

        $expectedFilters = [
            'sort' => "issuedDate",
            'order' =>"DESC",
            'page' => 1,
            'limit' => 10,
            'licenceId' => 7,
        ];

        $this->sut->shouldReceive('makeRestCall')
            ->with('DocumentSearchView', 'GET', $expectedFilters)
            ->andReturn([]);

        $this->sut->shouldReceive('getTable')
            ->andReturn(
                m::mock('\StdClass')
                ->shouldReceive('render')
                ->andReturn('tablecontent')
                ->getMock()
            );

        $this->sut->shouldReceive('makeRestCall')
            ->with(
                'Category',
                'GET',
                [
                    'limit'         => 100,
                    'sort'          => 'description',
                    'isDocCategory' => true,
                ]
            )
            ->andReturn(['cat1', 'cat2']);

        $this->sut->shouldReceive('makeRestCall')
            ->with(
                'SubCategory',
                'GET',
                [
                    'sort'      => 'subCategoryName',
                    'order'     => 'ASC',
                    'page'      => 1,
                    'limit'     => 100,
                    'licenceId' => 7,
                    'isDoc'     => true
                ]
            )
            ->andReturn(['subcat1', 'subcat2']);
        $this->sut->shouldReceive('makeRestCall')
            ->with(
                'RefData',
                'GET',
                [
                    'refDataCategoryId' => "document_type",
                    'limit'             => 100,
                    'sort'              => "description"
                ]
            )
            ->andReturn(['type1', 'type2']);

        // needed for stub the calls used for view/header generation
        $this->sut
            ->shouldReceive('getApplication')
                ->andReturn(
                    [
                        'id'=>1,
                        'licence' => [
                            'id' => 7,
                            'goodsOrPsv' => ['id' => 'lcat_psv']
                        ]
                    ]
                )
            ->shouldReceive('getHeaderParams')
                ->andReturn(
                    [
                        'licNo' => 'TEST1234',
                        'companyName' => 'myco',
                        'description' => 'foo'
                    ]
                );

        $this->sut->shouldReceive('loadScripts')
            ->with(['documents', 'table-actions'])
            ->andReturnSelf();

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValueOptions')
                    ->getMock()
            )
            ->shouldReceive('setData')
            ->shouldReceive('remove')
            ->getMock();

        $this->sut->shouldReceive('getForm')
            ->with('DocumentsHome')
            ->andReturn($mockForm);

        $this->sm->setService(
            'Script',
            m::mock()
                ->shouldReceive('loadFiles')
                ->with(['documents', 'table-actions'])
                ->getMock()
        );

        $this->sut->documentsAction();
    }

    /**
     * @group applicationController
     */
    public function testDocumentsActionWithUploadRedirectsToUpload()
    {
        $this->markTestSkipped();
        $this->sut = $this->getMock(
            '\Olcs\Controller\Application\ApplicationController',
            array(
                'getRequest',
                'params',
                'redirect',
                'url',
                'getFromRoute'
            )
        );

        $request = $this->getMock('\stdClass', ['isPost', 'getPost']);
        $request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));
        $this->sut->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $params = $this->getMock('\stdClass', ['fromPost']);
        $params->expects($this->once())
            ->method('fromPost')
            ->with('action')
            ->will($this->returnValue('upload'));
        $this->sut->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        $this->sut->expects($this->any())
            ->method('getFromRoute')
            ->with('application')
            ->will($this->returnValue(1234));

        $redirect = $this->getMock('\stdClass', ['toRoute']);
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('lva-application/documents/upload', ['application' => 1234]);
        $this->sut->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->sut->documentsAction();
    }

    /**
     * @group application_controller
     */
    public function testUndoGrantActionWithGet()
    {
        $this->markTestSkipped();

        $id = 7;

        $this->mockRouteParam('application', $id);

        $this->mockRender('renderView');

        $request = $this->sut->getRequest();
        $request->setMethod('GET');

        $mockForm = m::mock();
        $mockForm->shouldReceive('get->get->setValue')
            ->with('confirm-undo-grant-application');

        $formHelper = $this->getMock('\stdClass', ['createForm', 'setFormActionFromRequest']);
        $formHelper->expects($this->once())
            ->method('createForm')
            ->with('GenericConfirmation')
            ->will($this->returnValue($mockForm));

        $formHelper->expects($this->once())
            ->method('setFormActionFromRequest');

        $this->sm->setService('Helper\Form', $formHelper);

        $view = $this->sut->undoGrantAction();
        $this->assertEquals('partials/form', $view->getTemplate());
        $this->assertEquals($mockForm, $view->getVariable('form'));
    }

    /**
     * @group application_controller
     */
    public function testUndoGrantActionWithCancelButton()
    {
        $this->markTestSkipped();

        $id = 7;
        $post = array(
            'form-actions' => array(
                'cancel' => 'foo'
            )
        );

        $this->mockRouteParam('application', $id);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $redirect = $this->mockRedirect();
        $redirect->expects($this->once())
            ->method('toRouteAjax')
            ->with('lva-application', array('application' => 7))
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->sut->undoGrantAction());
    }

    /**
     * @group application_controller
     */
    public function testUndoGrantActionWithPost()
    {
        $this->markTestSkipped();

        $id = 7;
        $post = array();

        $this->mockRouteParam('application', $id);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $mockFlashMessenger = $this->getMock('\stdClass', ['addSuccessMessage']);
        $mockFlashMessenger->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The application grant has been undone successfully');
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        $mockProcessingApplication = $this->getMock('\stdClass', ['processUnGrantApplication']);
        $mockProcessingApplication->expects($this->once())
            ->method('processUnGrantApplication')
            ->with($id);
        $this->sm->setService('Processing\Application', $mockProcessingApplication);

        $redirect = $this->mockRedirect();
        $redirect->expects($this->once())
            ->method('toRouteAjax')
            ->with('lva-application', array('application' => 7))
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->sut->undoGrantAction());
    }

    /**
     * @group application_controller
     */
    public function testFeesListActionWithValidPostRedirectsCorrectly()
    {
        $id = 7;
        $post = [
            'id' => [1,2,3]
        ];

        $this->mockRouteParam('application', $id);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $routeParams = [
            'action' => 'pay-fees',
            'fee' => '1,2,3'
        ];
        $redirect = $this->mockRedirect();
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('lva-application/fees/fee_action', $routeParams)
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->sut->feesAction());
    }

    /**
     * @group application_controller
     */
    public function testFeesListActionWithInvalidPostRedirectsCorrectly()
    {
        $id = 7;
        $post = [];

        $this->mockRouteParam('application', $id);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $this->sut->expects($this->once())
            ->method('redirectToList')
            ->will($this->returnValue('REDIRECT'));

        $this->sut->expects($this->once())
            ->method('addErrorMessage');

        $this->assertEquals('REDIRECT', $this->sut->feesAction());
    }

    public function testPayFeesActionWithGet()
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $date = date('Y-m-d', strtotime('2015-01-06'));
        $this->mockDate($date);

        $validatorClosure = function ($input) {
            $this->assertEquals(15.5, $input->getMax());
            $this->assertEquals(true, $input->getInclusive());
        };

        $inputFilter = m::mock()
            ->shouldReceive(['get' => 'details'])
            ->andReturn(
                m::mock()
                ->shouldReceive(['get' => 'received'])
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValidatorChain')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('addValidator')
                        ->andReturnUsing($validatorClosure)
                        ->getMock()
                    )
                    ->getMock()
                )
                ->getMock()
            )
            ->getMock();

        $expectedDefaultDate = new \DateTime($date);
        $form = m::mock()
            ->shouldReceive('get')
            ->with('details')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('maxAmount')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setValue')
                    ->with('£15.50')
                    ->getMock()
                )
                ->shouldReceive('get')
                ->with('maxAmountForValidator')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setValue')
                    ->with('15.50')
                    ->getMock()
                )
                ->shouldReceive('get')
                ->with('minAmountForValidator')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setValue')
                    ->with('5.51')
                    ->getMock()
                )
                ->shouldReceive('get')
                ->with('receiptDate')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setValue')
                    ->once()
                    ->with(m::mustBe($expectedDefaultDate)) // note custom matcher
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('getInputFilter')
            ->andReturn($inputFilter)
            ->getMock();

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->andReturn('1,2')
            ->shouldReceive('params')
            ->with('application')
            ->andReturn(1)
            ->shouldReceive('getForm')
            ->with('FeePayment')
            ->andReturn($form)
            ->shouldReceive('renderView')
            ->andReturn('renderView');

        $this->mockEntity('Application', 'getLicenceIdForApplication')
            ->andReturn(7);

        $fee1 = [
            'amount' => 6,
            'outstanding' => 5.5,
            'feeStatus' => [
                'id' => 'lfs_ot'
            ],
            'feeTransactions' => []
        ];
        $fee2 = [
            'amount' => 10,
            'outstanding' => 10,
            'feeStatus' => [
                'id' => 'lfs_ot'
            ],
            'feeTransactions' => []
        ];
        $fees = [$fee1, $fee2];
        $this->sut->shouldReceive('getFees')->andReturn(
            [
                'results' => $fees,
                'count' => 2,
                'extra' => [
                    'minPayment' => '5.51',
                    'totalOutstanding' => '15.50',
                ],
            ]
        );

        $this->sm->setService(
            'Script',
            m::mock()
                ->shouldReceive('loadFiles')
                ->with(['forms/fee-payment'])
                ->getMock()
        );

        $this->assertEquals(
            'renderView',
            $this->sut->payFeesAction()
        );
    }

    public function testPayFeesActionWithInvalidFeeStatuses()
    {
        $this->mockController(
            '\Olcs\Controller\Application\ApplicationController'
        );

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->andReturn('1')
            ->shouldReceive('params')
            ->with('application')
            ->andReturn(1)
            ->shouldReceive('redirectToList')
            ->andReturn('redirect')
            ->shouldReceive('addErrorMessage');

        $this->mockEntity('Application', 'getLicenceIdForApplication')
            ->andReturn(7);

        $fee = [
            'amount' => 5.5,
            'outstanding' => 5.5,
            'feeStatus' => [
                'id' => 'lfs_pd'
            ]
        ];
        $this->sut->shouldReceive('getFees')->andReturn(
            [
                'results' => [$fee],
                'count' => 1,
            ]
        );

        $this->sm->setService('Cpms\FeePayment', m::mock());

        $this->assertEquals(
            'redirect',
            $this->sut->payFeesAction()
        );
    }

    protected function postPayFeesActionWithCardSetUp($fee)
    {
        $this->mockController(
            '\Olcs\Controller\Application\ApplicationController'
        );

        $date = '2015-02-02';
        $this->mockDate($date);

        $post = [
            'details' => [
                'paymentType' => 'fpm_card_offline'
            ]
        ];
        $this->setPost($post);

        $form = m::mock()
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->getMock();

        // we've asserted these in details elsewhere, for now we just want
        // to pass through with as little detail as possible
        $form->shouldReceive('get->get->setValue');
        $form->shouldReceive('getInputFilter->get->get->getValidatorChain->addValidator');

        $this->getMockFormHelper()
            ->shouldReceive('remove')
            ->with($form, 'details->received');

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->andReturn('1')
            ->shouldReceive('params')
            ->with('application')
            ->andReturn(1)
            ->shouldReceive('getForm')
            ->with('FeePayment')
            ->andReturn($form)
            ->shouldReceive('url')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromRoute')
                ->andReturn('http://return-url')
                ->getMock()
            );

        $response = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn(
                [
                    'results' => [$fee],
                    'count' => 1,
                    'extra' => [
                        'minPayment' => '0.01',
                        'totalOutstanding' => '5.50',
                    ],
                ]
            )
            ->getMock();
        $this->sut
            ->shouldReceive('handleQuery')
            ->once()
            ->with(m::type(FeeListQry::class))
            ->andReturn($response);

        $this->sm->setService(
            'Script',
            m::mock()
                ->shouldReceive('loadFiles')
                ->with(['forms/fee-payment'])
                ->getMock()
        );

        // skip testing the confirm plugin step
        $this->sut->shouldReceive('confirm')->andReturn(false);
    }

    public function testPostPayFeesActionWithCard()
    {
        $fee = [
            'id' => 1,
            'amount' => 5.5,
            'outstanding' => 5.5,
            'feeStatus' => [
                'id' => 'lfs_ot'
            ],
            'feeTransactions' => []
        ];

        $this->postPayFeesActionWithCardSetUp($fee);

        $paymentId = 101;
        $response = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn(
                [
                    'id' => [
                        'transaction' => $paymentId,
                    ],
                    'messages' => [
                        'payment created',
                    ]
                ]
            )
            ->getMock();

        $this->sut
            ->shouldReceive('handleCommand')
            ->once()
            ->with(m::type(PayOutstandingFeesCmd::class))
            ->andReturn($response);

        $response2 = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn(
                [
                    'id' => $paymentId,
                    'reference' => 'foo-bar',
                    'gatewayUrl' => 'http://gateway',
                ]
            )
            ->getMock();
        $this->sut
            ->shouldReceive('handleQuery')
            ->once()
            ->with(m::type(PaymentByIdQry::class))
            ->andReturn($response2);

        $this->sut->payFeesAction();
    }

    public function testPaymentResultActionWithError()
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $this->request
            ->shouldReceive('getQuery')
            ->andReturn(['receipt_reference' => 'OLCS-1234-FOO']);

        $response = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->getMock();
        $this->sut->shouldReceive('handleCommand')->andReturn($response);

        $this->sut
            ->shouldReceive('addErrorMessage')->once()
            ->shouldReceive('redirectToList')->once()->andReturn('redirect');

        $this->assertEquals('redirect', $this->sut->paymentResultAction());
    }

    /**
     * @dataProvider paymentResultValidStatusProvider
     */
    public function testPaymentResultActionOk($status, $flash)
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $this->request
            ->shouldReceive('getQuery')
            ->andReturn(['receipt_reference' => 'OLCS-1234-FOO']);

        $paymentId = 69;
        $result = [
            'id' => [
                'transaction' => $paymentId,
            ],
        ];
        $response = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($result)
            ->getMock();

        $this->sut
            ->shouldReceive('handleCommand')
            ->with(m::type(CompletePaymentCmd::class))
            ->once()
            ->andReturn($response);

        $payment = [
            'id' => $paymentId,
            'status' => [
                'id' => $status,
            ],
        ];
        $response2 = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($payment)
            ->getMock();
        $this->sut
            ->shouldReceive('handleQuery')
            ->with(m::type(PaymentByIdQry::class))
            ->once()
            ->andReturn($response2);

        $this->sut->shouldReceive('redirectToList')
            ->once()
            ->andReturn('redirect');

        if ($flash !== null) {
            $this->sut->shouldReceive($flash)->once();
        }

        $this->assertEquals('redirect', $this->sut->paymentResultAction());
    }

    public function paymentResultValidStatusProvider()
    {
        return [
            [RefData::TRANSACTION_STATUS_COMPLETE, 'addSuccessMessage'],
            [RefData::TRANSACTION_STATUS_FAILED, 'addErrorMessage'],
            // no flash at all for cancelled
            [RefData::TRANSACTION_STATUS_CANCELLED, null],
            // duff payment status
            [null, 'addErrorMessage']
        ];
    }

    /**
     * @param boolean $apiResult result of CPMS call
     * @param string $expectedFlashMessageMethod
     * @dataProvider postPayFeesActionWithCashProvider
     */
    public function testPostPayFeesActionWithCash($apiResult, $expectedFlashMessageMethod)
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $receiptDateArray = ['day'=>'07', 'month'=>'01', 'year'=>'2015'];
        $post = [
            'details' => [
                'paymentType' => 'fpm_cash',
                'received' => '123.45',
                'receiptDate' => $receiptDateArray,
                'payer' => 'Mr. P. Ayer',
                'slipNo' => '987654',
            ]
        ];
        $this->setPost($post);

        $form = m::mock()
            ->shouldReceive('setData')
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->getMock();

        // we've asserted these in details elsewhere, for now we just want
        // to pass through with as little detail as possible
        $form->shouldReceive('get->get->setValue');
        $form->shouldReceive('getInputFilter->get->get->getValidatorChain->addValidator');

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->andReturn('1,2')
            ->shouldReceive('params')
            ->with('application')
            ->andReturn(1);

        $this->sut->shouldReceive('getForm')->with('FeePayment')->andReturn($form);

        $this->sut->shouldReceive('url')->never(); // don't need a redirect url

        $fee1 = [
            'id' => 1,
            'amount' => 123.45,
            'outstanding' => 123.45,
            'feeStatus' => ['id' => 'lfs_ot'],
            'feeTransactions' => []
        ];
        $fees = array($fee1);

        $this->sut->shouldReceive('getFees')->andReturn(
            [
                'results' => $fees,
                'count' => 1,
                'extra' => [
                    'minPayment' => '0.01',
                    'totalOutstanding' => '123.45',
                ],
            ]
        );

        $response = m::mock()
            ->shouldReceive('isOk')
            ->andReturn($apiResult)
            ->getMock();
        $this->sut
            ->shouldReceive('handleCommand')
            ->once()
            ->with(m::type(PayOutstandingFeesCmd::class))
            ->andReturn($response);

        $this->sut->shouldReceive($expectedFlashMessageMethod)->once();

        $this->sut->shouldReceive('redirectToList')->once()->andReturn('redirect');

        $this->mockDate('2015-02-03'); // mock receipt date

        $this->sm->setService(
            'Script',
            m::mock()
                ->shouldReceive('loadFiles')
                ->with(['forms/fee-payment'])
                ->getMock()
        );

        $this->mockEntity('FeePayment', 'isValidPaymentType')
            ->andReturn(true);

        // skip testing the confirm plugin step
        $this->sut->shouldReceive('confirm')->andReturn(false);

        $result = $this->sut->payFeesAction();
        $this->assertEquals('redirect', $result);
    }

    public function postPayFeesActionWithCashProvider()
    {
        return [
            [true, 'addSuccessMessage'],
            [false, 'addErrorMessage'],
        ];
    }

    /**
     * @param string $paymentType
     * @param boolean $validPaymentType - whether the payment type itself is valid
     * @expectedException \UnexpectedValueException
     */
    public function testPostPayFeesActionWithUnexpectedTypeThrowsException()
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $data = ['details' => ['paymentType' => 'INVALID']];
        $this->setPost($data);

        $form = m::mock()
            ->shouldReceive('setData')
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($data)
            ->getMock();

        $form->shouldReceive('get->get->setValue');
        $form->shouldReceive('getInputFilter->get->get->getValidatorChain->addValidator');

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->andReturn('1')
            ->shouldReceive('params')
            ->with('application')
            ->andReturn(1);

        $this->sut->shouldReceive('getForm')->with('FeePayment')->andReturn($form);

        $fee1 = [
            'id' => 1,
            'amount' => 123.45,
            'outstanding' => 123.45,
            'feeStatus' => ['id' => 'lfs_ot'],
            'feeTransactions' => []
        ];
        $fees = array($fee1);

        $this->sut->shouldReceive('getFees')->andReturn(
            [
                'results' => $fees,
                'count' => 1,
                'extra' => [
                    'minPayment' => '0.01',
                    'totalOutstanding' => '123.45',
                ],
            ]
        );

        $this->mockDate('2015-02-03'); // mock receipt date

        $this->sm->setService(
            'Script',
            m::mock()
                ->shouldReceive('loadFiles')
                ->with(['forms/fee-payment'])
                ->getMock()
        );

        // skip testing the confirm plugin step
        $this->sut->shouldReceive('confirm')->andReturn(false);

        $this->sut->payFeesAction();
    }

    public function testPostPayFeesActionWithCheque()
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $receiptDateArray = ['day'=>'08', 'month'=>'01', 'year'=>'2015'];
        $chequeDateArray = ['day'=>'02', 'month'=>'01', 'year'=>'2015'];

        $post = [
            'details' => [
                'paymentType' => 'fpm_cheque',
                'received' => '123.45',
                'receiptDate' => $receiptDateArray,
                'payer' => 'Mr. P. Ayer',
                'slipNo' => '987654',
                'chequeNo' => '1234567',
                'chequeDate' => $chequeDateArray,
            ]
        ];
        $formData = [
            'details' => [
                'paymentType' => 'fpm_cheque',
                'received' => '123.45',
                'receiptDate' => '2015-01-08',
                'payer' => 'Mr. P. Ayer',
                'slipNo' => '987654',
                'chequeNo' => '1234567',
                'chequeDate' => '2015-01-02',
            ]
        ];
        $this->setPost($post);

        $form = m::mock()
            ->shouldReceive('setData')
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($formData)
            ->getMock();

        $form->shouldReceive('get->get->setValue');
        $form->shouldReceive('getInputFilter->get->get->getValidatorChain->addValidator');

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->andReturn('1')
            ->shouldReceive('params')
            ->with('application')
            ->andReturn(1);

        $this->sut->shouldReceive('getForm')->with('FeePayment')->andReturn($form);

        $fee = [
            'id' => 1,
            'amount' => 123.45,
            'outstanding' => 123.45,
            'feeStatus' => ['id' => 'lfs_ot'],
            'feeTransactions' => []
        ];

        $this->sut->shouldReceive('getFees')->andReturn(
            [
                'results' => [$fee],
                'count' => 1,
                'extra' => [
                    'minPayment' => '0.01',
                    'totalOutstanding' => '123.45',
                ],
            ]
        );

        $response = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->getMock();
        $this->sut
            ->shouldReceive('handleCommand')
            ->once()
            ->with(
                m::on(
                    function ($cmd) {
                        $expected = [
                            'feeIds' => ['1'],
                            'organisationId' => null,
                            'applicationId' => null,
                            'cpmsRedirectUrl' => null,
                            'paymentMethod' => 'fpm_cheque',
                            'received' => '123.45',
                            'receiptDate' => '2015-01-08',
                            'payer' => 'Mr. P. Ayer',
                            'slipNo' => '987654',
                            'chequeNo' => '1234567',
                            'chequeDate' => '2015-01-02',
                            'poNo' => null,
                        ];
                        $matched = (
                            $cmd instanceof PayOutstandingFeesCmd
                            &&
                            $cmd->getArrayCopy() == $expected
                        );

                        return $matched;
                    }
                )
            )
            ->andReturn($response);

        $this->sut->shouldReceive('addSuccessMessage')->once();

        $this->sut->shouldReceive('redirectToList')->once()->andReturn('redirect');

        $this->mockDate('2015-02-03'); // mock receipt date

        $this->sm->setService(
            'Script',
            m::mock()
                ->shouldReceive('loadFiles')
                ->with(['forms/fee-payment'])
                ->getMock()
        );

        // skip testing the confirm plugin step
        $this->sut->shouldReceive('confirm')->andReturn(false);

        $result = $this->sut->payFeesAction();
        $this->assertEquals('redirect', $result);
    }

    public function testPostPayFeesActionWithPostalOrder()
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $receiptDateArray = ['day'=>'08', 'month'=>'01', 'year'=>'2015'];
        $post = [
            'details' => [
                'paymentType' => 'fpm_po',
                'received' => '123.45',
                'receiptDate' => $receiptDateArray,
                'payer' => 'Mr. P. Ayer',
                'slipNo' => '987654',
                'poNo' => '1234567',
            ]
        ];
        $formData = [
            'details' => [
                'paymentType' => 'fpm_po',
                'received' => '123.45',
                'receiptDate' => '2015-01-08',
                'payer' => 'Mr. P. Ayer',
                'slipNo' => '987654',
                'poNo' => '1234567',
            ]
        ];
        $this->setPost($post);

        $form = m::mock()
            ->shouldReceive('setData')
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($formData)
            ->getMock();

        $form->shouldReceive('get->get->setValue');
        $form->shouldReceive('getInputFilter->get->get->getValidatorChain->addValidator');

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->andReturn('1')
            ->shouldReceive('params')
            ->with('application')
            ->andReturn(1);

        $this->sut->shouldReceive('getForm')->with('FeePayment')->andReturn($form);

        $fee = [
            'id' => 1,
            'amount' => 123.45,
            'outstanding' => 123.45,
            'feeStatus' => ['id' => 'lfs_ot'],
            'feeTransactions' => []
        ];

        $this->sut->shouldReceive('getFees')->andReturn(
            [
                'results' => [$fee],
                'count' => 1,
                'extra' => [
                    'minPayment' => '0.01',
                    'totalOutstanding' => '123.45',
                ],
            ]
        );

        $response = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->getMock();
        $this->sut
            ->shouldReceive('handleCommand')
            ->once()
            ->with(
                m::on(
                    function ($cmd) {
                        $expected = [
                            'feeIds' => ['1'],
                            'organisationId' => null,
                            'applicationId' => null,
                            'cpmsRedirectUrl' => null,
                            'paymentMethod' => 'fpm_po',
                            'received' => '123.45',
                            'receiptDate' => '2015-01-08',
                            'payer' => 'Mr. P. Ayer',
                            'slipNo' => '987654',
                            'chequeNo' => null,
                            'chequeDate' => null,
                            'poNo' => '1234567',
                        ];

                        $matched = (
                            $cmd instanceof PayOutstandingFeesCmd
                            &&
                            $cmd->getArrayCopy() == $expected
                        );
                        return $matched;
                    }
                )
            )
            ->andReturn($response);

        $this->sut->shouldReceive('addSuccessMessage')->once();

        $this->sut->shouldReceive('redirectToList')->once()->andReturn('redirect');

        $this->mockDate('2015-02-03'); // mock receipt date

        $this->sm->setService(
            'Script',
            m::mock()
                ->shouldReceive('loadFiles')
                ->with(['forms/fee-payment'])
                ->getMock()
        );

        $this->sut->shouldReceive('confirm')->andReturn(false);

        $this->sut->payFeesAction();
    }

    public function testGetChangeOfEntityAction()
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $this->sut->shouldReceive('params->fromRoute')->with('application', null)->andReturn(1);
        $this->sut->shouldReceive('params->fromRoute')->with('changeId', null)->andReturn(null);

        $this->createMockForm('ApplicationChangeOfEntity')
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                    ->shouldReceive('remove')
                    ->with('remove')
                    ->getMock()
            );

        $this->sut->changeOfEntityAction();
    }

    public function testPostUpdateChangeOfEntityAction()
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $this->setPost([]);

        $this->sut->shouldReceive('params->fromRoute')->with('application', null)->andReturn(1);
        $this->sut->shouldReceive('params->fromRoute')->with('changeId', null)->andReturn(1);

        $this->expectQuery(ChangeOfEntityQry::class, ['id' => 1], []);

        $this->createMockForm('ApplicationChangeOfEntity')
            ->shouldReceive('setData')
            ->twice()
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn(
                [
                    'change-details' => [
                        'oldLicenceNo' => 'oldNo',
                        'oldOrganisationName' => 'oldName',
                    ],
                ]
            );

        $this->expectCommand(
            UpdateChangeOfEntityCmd::class,
            [
                'id' => 1,
                'version' => null,
                'oldLicenceNo' => 'oldNo',
                'oldOrganisationName' => 'oldName',
            ],
            [
                'id' => [
                    'changeOfEntity' => 1
                ],
                'messages' => [
                    'ChangeOfEntity Updated',
                ]
            ]
        );

        $this->sut
            ->shouldReceive('flashMessenger->addSuccessMessage')
            ->with('application.change-of-entity.create.success');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with(
                'lva-application/overview',
                array(
                    'application' => 1
                ),
                array(),
                false
            );

        $this->sut->changeOfEntityAction();
    }

    public function testPostCreateChangeOfEntityAction()
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $postData =  [
            'change-details' => [
                'oldLicenceNo' => 'newOldNo',
                'oldOrganisationName' => 'newOldName',
            ]
        ];
        $this->setPost($postData);

        $this->sut->shouldReceive('params->fromRoute')->with('application', null)->andReturn(7);
        $this->sut->shouldReceive('params->fromRoute')->with('changeId', null)->andReturn(null);

        $this->createMockForm('ApplicationChangeOfEntity')
            ->shouldReceive('setData')
            ->with($postData)
            ->once()
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData)
            ->shouldReceive('get->remove');

        $this->expectCommand(
            CreateChangeOfEntityCmd::class,
            [
                'applicationId' => 7,
                'oldLicenceNo' => 'newOldNo',
                'oldOrganisationName' => 'newOldName',
            ],
            [
                'id' => [
                    'changeOfEntity' => 69
                ],
                'messages' => [
                    'ChangeOfEntity Created',
                ]
            ]
        );

        $this->sut
            ->shouldReceive('flashMessenger->addSuccessMessage')
            ->with('application.change-of-entity.create.success');

        $this->sut
            ->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/overview', ['application' => 7], [], false);

        $this->sut->changeOfEntityAction();
    }

    public function testPostInvalidChangeOfEntityAction()
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $this->setPost([]);

        $this->sut->shouldReceive('params->fromRoute')->with('application', null)->andReturn(1);
        $this->sut->shouldReceive('params->fromRoute')->with('changeId', null)->andReturn(1);

        $this->expectQuery(ChangeOfEntityQry::class, ['id' => 1], []);

        $this->createMockForm('ApplicationChangeOfEntity')
            ->shouldReceive('setData')
            ->twice()
            ->shouldReceive('isValid')
            ->andReturn(false);

        $this->sut->changeOfEntityAction();
    }

    public function testRemoveChangeOfEntityAction()
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $this->sut->shouldReceive('params->fromRoute')->with('application', null)->andReturn(1);
        $this->sut->shouldReceive('params->fromRoute')->with('changeId', null)->andReturn(69);

        $this->sut->shouldReceive('isButtonPressed')->with('remove')->andReturn(true);

        $this->expectCommand(
            DeleteChangeOfEntityCmd::class,
            [
                'id' => 69,
                'version' => null,
            ],
            [
                'id' => [
                    'changeOfEntity' => 69
                ],
                'messages' => [
                    'ChangeOfEntity ID 69 deleted',
                ]
            ]
        );

        $this->sut
            ->shouldReceive('flashMessenger->addSuccessMessage')
            ->with('application.change-of-entity.delete.success');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with(
                'lva-application/overview',
                array(
                    'application' => 1
                ),
                array(),
                false
            );

        $this->sut->changeOfEntityAction();
    }

    /**
     * Helper method
     * @to-do when these helper methods are required in more than 1 place, we need to abstract them away
     */
    protected function mockRouteParam($name, $value)
    {
        $this->mockRouteParams[$name] = $value;

        if ($this->mockParams === null) {
            $this->mockParams = $this->getMock('\Zend\Mvc\Controller\Plugin\Params', array('__invoke'));

            $this->mockParams->expects($this->any())
                ->method('__invoke')
                ->will($this->returnCallback(array($this, 'getRouteParam')));

            $this->pluginManager->setService('params', $this->mockParams);
        }
    }

    /**
     * Helper method
     */
    public function getRouteParam($name)
    {
        return isset($this->mockRouteParams[$name]) ? $this->mockRouteParams[$name] : null;
    }

    /**
     * Helper method
     */
    protected function mockRender($renderMethod = 'render')
    {
        $this->sut->expects($this->once())
            ->method($renderMethod)
            ->will(
                $this->returnCallback(
                    function ($view) {
                        return $view;
                    }
                )
            );
    }

    /**
     * Helper method
     */
    protected function mockRedirect()
    {
        $mockRedirect = $this->getMock('\Zend\Mvc\Controller\Plugin\Redirect', array('toRoute', 'toRouteAjax'));
        $this->pluginManager->setService('Redirect', $mockRedirect);
        return $mockRedirect;
    }
}
