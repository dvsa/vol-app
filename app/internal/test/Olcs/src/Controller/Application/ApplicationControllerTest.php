<?php

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Application;

use OlcsTest\Bootstrap;
use CommonTest\Traits\MockDateTrait;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\Lva\Traits\LvaControllerTestTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Common\Service\Entity\PaymentEntityService;

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationControllerTest extends MockeryTestCase
{
    use LvaControllerTestTrait,
        MockDateTrait;

    private $mockParams;
    private $mockRouteParams;
    private $pluginManager;

    /**
     * Required by trait
     */
    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

    protected function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = $this->getMock(
            '\Olcs\Controller\Application\ApplicationController',
            array('render', 'renderView', 'addErrorMessage', 'redirectToList')
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

        $serviceLocator = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocator->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $serviceLocator->shouldReceive('get')->with($serviceName)->andReturn($dataService);

        $tableBuilder = m::mock('Common\Service\Table\TableBuilder');
        $tableBuilder->shouldReceive('buildTable')->with('case', $results, $params, false)->andReturn('tableContent');

        $serviceLocator->shouldReceive('get')->with('Table')->andReturn($tableBuilder);

        $request = new \Zend\Http\Request();
        $request->setQuery($query);

        $sut = $this->sut;
        $sut->setRequest($request);
        $sut->setPluginManager($mockPluginManager);
        $sut->setServiceLocator($serviceLocator);

        $this->assertEquals('licence/cases', $sut->caseAction()->getTemplate());
    }

    /**
     * @group application_controller
     */
    public function testEnvironmentalAction()
    {
        $this->mockRender();

        $view = $this->sut->environmentalAction();

        $this->assertEquals('application/index', $view->getTemplate());
    }

    /**
     * @group application_controller
     */
    public function testDocumentsAction()
    {
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

        $view = $this->sut->documentsAction();

    }

    /**
     * @group applicationController
     */
    public function testDocumentsActionWithUploadRedirectsToUpload()
    {
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

        $response = $this->sut->documentsAction();
    }

    /**
     * @group application_controller
     */
    public function testGrantActionWithGet()
    {
        $id = 7;

        $this->mockRouteParam('application', $id);

        $this->mockRender('renderView');

        $request = $this->sut->getRequest();
        $request->setMethod('GET');

        $formHelper = $this->getMock('\stdClass', ['createForm', 'setFormActionFromRequest']);
        $formHelper->expects($this->once())
            ->method('createForm')
            ->with('GenericConfirmation')
            ->will($this->returnValue('FORM'));

        $formHelper->expects($this->once())
            ->method('setFormActionFromRequest');

        $this->sm->setService('Helper\Form', $formHelper);

        $view = $this->sut->grantAction();
        $this->assertEquals('application/grant', $view->getTemplate());
        $this->assertEquals('FORM', $view->getVariable('form'));
    }

    /**
     * @group application_controller
     */
    public function testGrantActionWithCancelButton()
    {
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

        $this->assertEquals('REDIRECT', $this->sut->grantAction());
    }

    /**
     * @group application_controller
     */
    public function testGrantActionWithPost()
    {
        $id = 7;
        $date = date('Y-m-d');
        $this->mockDate($date);

        $post = array();

        $this->mockRouteParam('application', $id);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $mockFlashMessenger = $this->getMock('\stdClass', ['addSuccessMessage']);
        $mockFlashMessenger->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The application was granted successfully');
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        $mockProcessingApplication = $this->getMock('\stdClass', ['processGrantApplication']);
        $mockProcessingApplication->expects($this->once())
            ->method('processGrantApplication')
            ->with($id);
        $this->sm->setService('Processing\Application', $mockProcessingApplication);

        $redirect = $this->mockRedirect();
        $redirect->expects($this->once())
            ->method('toRouteAjax')
            ->with('lva-application', array('application' => 7))
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->sut->grantAction());
    }

    /**
     * @group application_controller
     */
    public function testUndoGrantActionWithGet()
    {
        $id = 7;

        $this->mockRouteParam('application', $id);

        $this->mockRender('renderView');

        $request = $this->sut->getRequest();
        $request->setMethod('GET');

        $formHelper = $this->getMock('\stdClass', ['createForm', 'setFormActionFromRequest']);
        $formHelper->expects($this->once())
            ->method('createForm')
            ->with('GenericConfirmation')
            ->will($this->returnValue('FORM'));

        $formHelper->expects($this->once())
            ->method('setFormActionFromRequest');

        $this->sm->setService('Helper\Form', $formHelper);

        $view = $this->sut->undoGrantAction();
        $this->assertEquals('application/undo-grant', $view->getTemplate());
        $this->assertEquals('FORM', $view->getVariable('form'));
    }

    /**
     * @group application_controller
     */
    public function testUndoGrantActionWithCancelButton()
    {
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
                ->with('feeAmountForValidator')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setValue')
                    ->with('15.50')
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

        $fees = [
            [
                'amount' => 5.5,
                'feeStatus' => [
                    'id' => 'lfs_ot'
                ],
                'feePayments' => []
            ], [
                'amount' => 10,
                'feeStatus' => [
                    'id' => 'lfs_ot'
                ],
                'feePayments' => []
            ]
        ];
        $this->mockEntity('Fee', 'getOverview')
            ->with('1')
            ->andReturn($fees[0])
            ->shouldReceive('getOverview')
            ->with('2')
            ->andReturn($fees[1]);

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
            'feeStatus' => [
                'id' => 'lfs_pd'
            ]
        ];
        $this->mockEntity('Fee', 'getOverview')
            ->with('1')
            ->andReturn($fee);

        $this->assertEquals(
            'redirect',
            $this->sut->payFeesAction()
        );
    }

    public function testPayFeesActionWithOutstandingPayment()
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
            'feeStatus' => [
                'id' => 'lfs_ot'
            ],
            'feePayments' => [
                [
                    'payment' => [
                        'status' => [
                            'id' => PaymentEntityService::STATUS_OUTSTANDING
                        ]
                    ]
                ]
            ]
        ];
        $this->mockEntity('Fee', 'getOverview')
            ->with('1')
            ->andReturn($fee);

        $this->assertEquals(
            'redirect',
            $this->sut->payFeesAction()
        );
    }

    public function testPostPayFeesActionWithCard()
    {
        $this->mockController(
            '\Olcs\Controller\Application\ApplicationController'
        );

        $post = [
            'details' => [
                'paymentType' => 'fpm_card_offline'
            ]
        ];
        $this->setPost($post);

        $form = m::mock()
            ->shouldReceive('setData')
            ->shouldReceive('isValid')
            ->andReturn(true)
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
                ->shouldreceive('fromRoute')
                ->andReturn('http://return-url')
                ->getMock()
            )
            ->shouldReceive('getLicence')
            ->andReturn(
                [
                    'organisation' => [
                        'id' => 123
                    ]
                ]
            );

        $this->mockEntity('Application', 'getLicenceIdForApplication')
            ->andReturn(7);

        $fee = [
            'amount' => 5.5,
            'feeStatus' => [
                'id' => 'lfs_ot'
            ],
            'feePayments' => []
        ];
        $this->mockEntity('Fee', 'getOverview')
            ->with('1')
            ->andReturn($fee);

        $this->mockService('Cpms\FeePayment', 'initiateCardRequest')
            ->with(123, '1', 'http://return-url', [$fee])
            ->andReturn(
                [
                    'gateway_url' => 'http://gateway',
                    'redirection_data' => 'foo-bar'
                ]
            );

        $this->sut->payFeesAction();
    }

    public function testPaymentResultActionWithNoPaymentFound()
    {
        $this->mockController(
            '\Olcs\Controller\Application\ApplicationController'
        );
        $this->mockService('Cpms\FeePayment', 'handleResponse')
            ->andThrow(new \Common\Service\Cpms\PaymentNotFoundException);

        $this->sut->shouldReceive('addErrorMessage')
            ->shouldReceive('redirectToList')
            ->andReturn('redirect')
            ->shouldReceive('params')
            ->with('fee')
            ->andReturn('1')
            ->shouldReceive('getRequest->getQuery')
            ->andReturn([]);

        $fee = [
            'amount' => 5.5,
            'feeStatus' => [
                'id' => 'lfs_pd'
            ]
        ];
        $this->mockEntity('Fee', 'getOverview')
            ->with('1')
            ->andReturn($fee);

        $this->assertEquals(
            'redirect',
            $this->sut->paymentResultAction()
        );
    }

    public function testPaymentResultActionWithInvalidPayment()
    {
        $this->mockController(
            '\Olcs\Controller\Application\ApplicationController'
        );

        $this->mockService('Cpms\FeePayment', 'handleResponse')
            ->andThrow(new \Common\Service\Cpms\PaymentInvalidStatusException);

        $this->sut->shouldReceive('addErrorMessage')
            ->shouldReceive('redirectToList')
            ->andReturn('redirect')
            ->shouldReceive('params')
            ->with('fee')
            ->andReturn('1')
            ->shouldReceive('getRequest->getQuery')
            ->andReturn([]);

        $fee = [
            'amount' => 5.5,
            'feeStatus' => [
                'id' => 'lfs_pd'
            ]
        ];
        $this->mockEntity('Fee', 'getOverview')
            ->with('1')
            ->andReturn($fee);

        $this->assertEquals(
            'redirect',
            $this->sut->paymentResultAction()
        );
    }

    /**
     * @dataProvider paymentResultProvider
     */
    public function testPaymentResultActionWithValidStatus($status, $flash)
    {
        $this->mockController(
            '\Olcs\Controller\Application\ApplicationController'
        );

        $this->mockService('Cpms\FeePayment', 'handleResponse')
            ->andReturn($status);

        $this->sut
            ->shouldReceive('redirectToList')
            ->andReturn('redirect')
            ->shouldReceive('params')
            ->with('fee')
            ->andReturn('1')
            ->shouldReceive('getRequest->getQuery')
            ->andReturn([]);

        if ($flash !== null) {
            $this->sut->shouldReceive($flash);
        }

        $fee = [
            'amount' => 5.5,
            'feeStatus' => [
                'id' => 'lfs_pd'
            ]
        ];
        $this->mockEntity('Fee', 'getOverview')
            ->with('1')
            ->andReturn($fee);

        $this->assertEquals(
            'redirect',
            $this->sut->paymentResultAction()
        );
    }

    public function paymentResultProvider()
    {
        return [
            [PaymentEntityService::STATUS_PAID, 'addSuccessMessage'],
            [PaymentEntityService::STATUS_FAILED, 'addErrorMessage'],
            // no flash at all for cancelled
            [PaymentEntityService::STATUS_CANCELLED, null],
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
            ->getMock();

        // we've asserted these in details elsewhere, for now we just want
        // to pass through with as little detail as possible
        $form->shouldReceive('get->get->setValue');
        $form->shouldReceive('getInputFilter->get->get->getValidatorChain->addValidator');

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->andReturn('1')
            ->shouldReceive('params')
            ->with('application')
            ->andReturn(1);

        $this->sut->shouldReceive('getForm')->with('FeePayment')->andReturn($form);

        $this->sut->shouldReceive('url')->never(); // don't need a redirect url

        $this->sut->shouldReceive('getLicence')->andReturn(['organisation' => ['id' => 123 ] ]);

        $this->mockEntity('Application', 'getLicenceIdForApplication')->andReturn(7);

        $fee = [
            'amount' => 123.45,
            'feeStatus' => ['id' => 'lfs_ot'],
            'feePayments' => []
        ];
        $this->mockEntity('Fee', 'getOverview')
            ->with('1')
            ->andReturn($fee);

        $this->mockService('Cpms\FeePayment', 'recordCashPayment')
            ->with($fee, 123, '1', '123.45', $receiptDateArray, 'Mr. P. Ayer', '987654')
            ->andReturn($apiResult);

        $this->sut->shouldReceive($expectedFlashMessageMethod)->once();

        $this->sut->shouldReceive('redirectToList')->once()->andReturn('redirect');

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
     * @dataProvider invalidPaymentTypeProvider
     * @expectedException Common\Service\Cpms\PaymentInvalidTypeException
     */
    public function testPostPayFeesActionWithInvalidTypeThrowsException($paymentType)
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $this->setPost(['details' => ['paymentType' => $paymentType]]);

        $form = m::mock()
            ->shouldReceive('setData')
            ->shouldReceive('isValid')
            ->andReturn(true)
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

        $this->sut->shouldReceive('getLicence')->andReturn(['organisation' => ['id' => 123 ] ]);

        $this->mockEntity('Application', 'getLicenceIdForApplication')->andReturn(7);

        $fee = [
            'amount' => 123.45,
            'feeStatus' => ['id' => 'lfs_ot'],
            'feePayments' => []
        ];
        $this->mockEntity('Fee', 'getOverview')
            ->with('1')
            ->andReturn($fee);

        $this->sut->payFeesAction();
    }

    public function invalidPaymentTypeProvider()
    {
        return [
            ['fpm_card_online'],
            ['invalid'],
        ];
    }

    /**
     * @expectedException Common\Exception\BadRequestException
     * @expectedExceptionMessage Payment of multiple fees by cash/cheque/PO not supported
     */
    public function testPostPayFeesActionWithCashMultipleFeesThrowsException()
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $this->setPost(['details' => ['paymentType' => 'fpm_cash']]);

        $form = m::mock()
            ->shouldReceive('setData')
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->getMock();

        $form->shouldReceive('get->get->setValue');
        $form->shouldReceive('getInputFilter->get->get->getValidatorChain->addValidator');

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->andReturn('1,2')
            ->shouldReceive('params')
            ->with('application')
            ->andReturn(1);

        $this->sut->shouldReceive('getForm')->with('FeePayment')->andReturn($form);

        $this->sut->shouldReceive('getLicence')->andReturn(['organisation' => ['id' => 123 ] ]);

        $this->mockEntity('Application', 'getLicenceIdForApplication')->andReturn(7);

        $fee = [
            'amount' => 123.45,
            'feeStatus' => ['id' => 'lfs_ot'],
            'feePayments' => []
        ];
        $this->mockEntity('Fee', 'getOverview')->andReturn($fee);

        $this->sut->payFeesAction();
    }

    public function testPostPayFeesActionWithCheque()
    {
        $this->mockController('\Olcs\Controller\Application\ApplicationController');

        $receiptDateArray = ['day'=>'08', 'month'=>'01', 'year'=>'2015'];
        $post = [
            'details' => [
                'paymentType' => 'fpm_cheque',
                'received' => '123.45',
                'receiptDate' => $receiptDateArray,
                'payer' => 'Mr. P. Ayer',
                'slipNo' => '987654',
                'chequeNo' => '1234567',
            ]
        ];
        $this->setPost($post);

        $form = m::mock()
            ->shouldReceive('setData')
            ->shouldReceive('isValid')
            ->andReturn(true)
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

        $this->sut->shouldReceive('getLicence')->andReturn(['organisation' => ['id' => 123 ] ]);

        $this->mockEntity('Application', 'getLicenceIdForApplication')->andReturn(7);

        $fee = [
            'amount' => 123.45,
            'feeStatus' => ['id' => 'lfs_ot'],
            'feePayments' => []
        ];
        $this->mockEntity('Fee', 'getOverview')
            ->with('1')
            ->andReturn($fee);

        $this->mockService('Cpms\FeePayment', 'recordChequePayment')
            ->with($fee, 123, '1', '123.45', $receiptDateArray, 'Mr. P. Ayer', '987654', '1234567')
            ->andReturn(true);

        $this->sut->shouldReceive('addSuccessMessage')->once();

        $this->sut->shouldReceive('redirectToList')->once()->andReturn('redirect');

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
        $this->setPost($post);

        $form = m::mock()
            ->shouldReceive('setData')
            ->shouldReceive('isValid')
            ->andReturn(true)
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

        $this->sut->shouldReceive('getLicence')->andReturn(['organisation' => ['id' => 123 ] ]);

        $this->mockEntity('Application', 'getLicenceIdForApplication')->andReturn(7);

        $fee = [
            'amount' => 123.45,
            'feeStatus' => ['id' => 'lfs_ot'],
            'feePayments' => []
        ];
        $this->mockEntity('Fee', 'getOverview')
            ->with('1')
            ->andReturn($fee);

        $this->mockService('Cpms\FeePayment', 'recordPostalOrderPayment')
            ->with($fee, 123, '1', '123.45', $receiptDateArray, 'Mr. P. Ayer', '987654', '1234567')
            ->andReturn(true);

        $this->sut->shouldReceive('addSuccessMessage')->once();

        $this->sut->shouldReceive('redirectToList')->once()->andReturn('redirect');

        $result = $this->sut->payFeesAction();
        $this->assertEquals('redirect', $result);
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
