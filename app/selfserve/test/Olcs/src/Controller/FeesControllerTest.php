<?php

/**
 * Fees Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Entity\PaymentEntityService;
use Dvsa\Olcs\Transfer\Query\Organisation\OutstandingFees as OutstandingFeesQry;
use Dvsa\Olcs\Transfer\Query\Payment\Payment as PaymentByIdQry;
use Dvsa\Olcs\Transfer\Query\Payment\PaymentByReference as PaymentByReferenceQry;
use Dvsa\Olcs\Transfer\Command\Payment\CompletePayment as CompletePaymentCmd;
use Dvsa\Olcs\Transfer\Command\Payment\PayOutstandingFees as PayOutstandingFeesCmd;
use Olcs\Controller\FeesController;

/**
 * Fees Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeesControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sut = m::mock(FeesController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testIndexAction()
    {
        $fees = [
            [
                'id' => 1,
                'description' => 'fee 1',
                'licence' => [
                    'id' => 7,
                    'licNo' => 'LIC7',
                ],
            ],
            [
                'id' => 2,
                'description' => 'fee 2',
                'licence' => [
                    'id' => 8,
                    'licNo' => 'LIC8',
                ],
            ],
            [
                'id' => 3,
                'description' => 'fee 3',
                'licence' => [
                    'id' => 9,
                    'licNo' => 'LIC9',
                ],
            ],
        ];

        $correspondence = [
            'Count' => '3',
            'Results' => [
                ['id' => 1, 'accessed' => 'N'],
                ['id' => 2, 'accessed' => 'Y'],
                ['id' => 3, 'accessed' => 'Y'],
            ],
        ];

        $organisationId = 99;

        // mocks
        $mockNavigation = m::mock();
        $this->sm->setService('Olcs\Navigation\DashboardNavigation', $mockNavigation);

        $mockFeesResponse = m::mock();

        $mockCorrespondenceService = m::mock();
        $this->sm->setService('Entity\CorrespondenceInbox', $mockCorrespondenceService);

        $mockTableService = m::mock();
        $this->sm->setService('Table', $mockTableService);

        $mockTable = m::mock();

        $mockScriptHelper = m::mock();
        $this->sm->setService('Script', $mockScriptHelper);

        // expectations
        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        $this->sut
            ->shouldReceive('handleQuery')
            ->with(m::type(OutstandingFeesQry::class))
            ->andReturn($mockFeesResponse);

        $mockFeesResponse
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn(['outstandingFees' => $fees]);

        $mockCorrespondenceService
            ->shouldReceive('getCorrespondenceByOrganisation')
            ->with($organisationId)
            ->once()
            ->andReturn($correspondence);

        $mockNavigation
            ->shouldReceive('findOneById')
            ->with('dashboard-fees')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->with('count', 3)
                    ->getMock()
            )
            ->shouldReceive('findOneById')
            ->with('dashboard-correspondence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->with('count', 1)
                    ->getMock()
            );

        $mockTableService
            ->shouldReceive('buildTable')
            ->once()
            ->with('fees', $fees, [], false)
            ->andReturn($mockTable);

        $mockScriptHelper
            ->shouldReceive('loadFile')
            ->once()
            ->with('dashboard-fees');

        $view = $this->sut->indexAction();

        // assertions
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertEquals('pages/fees/home', $view->getTemplate());
    }

    public function testIndexActionPostRedirectSuccess()
    {
        $postData = [
            'id' => [
                '77',
                '99',
            ],
        ];

        $mockRequest = m::mock();

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('fees/pay', ['fee' => '77,99'], null, true)
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionPostRedirectError()
    {
        $postData = [];

        $mockRequest = m::mock();

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $this->sut->shouldReceive('addErrorMessage')
            ->once()
            ->with('fees.pay.error.please-select');

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('fees')
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testPayFeesActionShowOneFee()
    {
        // data
        $organisationId = 99;
        $outstandingFees = [
            [
                'id' => 77,
                'description' => 'fee 77',
                'licence' => [
                    'id' => 7,
                    'licNo' => 'LIC7',
                ],
            ],
        ];

        // mocks ...

        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockForm = m::mock();
        $mockFeesResponse = m::mock();

        // expectations ...

        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(false);

        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        $this->sut->shouldReceive('params')->with('fee')->once()->andReturn('77');

        $mockFormHelper
            ->shouldReceive('createForm')
            ->with('FeePayment')
            ->once()
            ->andReturn($mockForm);

        $this->sut
            ->shouldReceive('handleQuery')
            ->with(m::type(OutstandingFeesQry::class))
            ->andReturn($mockFeesResponse);

        $mockFeesResponse
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn(['outstandingFees' => $outstandingFees]);

        $view = $this->sut->payFeesAction();

        // assertions...

        $this->assertEquals($outstandingFees[0], $view->getVariable('fee'));

        $this->assertSame(
            $mockForm,
            $view->getVariable('form')
        );

        $this->assertEquals('pages/fees/pay-one', $view->getTemplate());
    }

    public function testPayFeesActionShowMultipleFees()
    {
        // data
        $organisationId = 99;
        $outstandingFees = [
            [
                'id' => 77,
                'description' => 'fee 77',
                'licence' => [
                    'id' => 7,
                    'licNo' => 'LIC7',
                ],
            ],
            [
                'id' => 88,
                'description' => 'fee 88',
                'licence' => [
                    'id' => 8,
                    'licNo' => 'LIC8',
                ],
            ],
        ];

        // mocks ...

        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockForm = m::mock();
        $mockTableService = m::mock();
        $this->sm->setService('Table', $mockTableService);
        $mockTable = m::mock();
        $mockFeesResponse = m::mock();

        // expectations ...

        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(false);

        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->once()
            ->andReturn('77,88,99'); // should filter to only outstanding fees

        $mockFormHelper
            ->shouldReceive('createForm')
            ->with('FeePayment')
            ->once()
            ->andReturn($mockForm);

        $mockTableService
            ->shouldReceive('buildTable')
            ->with('pay-fees', $outstandingFees, [], false)
            ->andReturn($mockTable);

        $this->sut
            ->shouldReceive('handleQuery')
            ->with(m::type(OutstandingFeesQry::class))
            ->andReturn($mockFeesResponse);

        $mockFeesResponse
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn(['outstandingFees' => $outstandingFees]);

        $view = $this->sut->payFeesAction();

        // assertions...

        $this->assertSame($mockTable, $view->getVariable('table'));

        $this->assertSame($mockForm, $view->getVariable('form'));

        $this->assertEquals('pages/fees/pay-multi', $view->getTemplate());
    }

    public function testPayFeesActionCancel()
    {
        $mockRequest = m::mock();

        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn(
                [
                    'form-actions' => [
                        'cancel' => '',
                    ]
                ]
            );

        $this->sut
            ->shouldReceive('redirect->toRoute')
            ->with('fees')
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->payFeesAction());
    }

    public function testPayFeesActionFeeNotFound()
    {
        // data
        $organisationId = 99;
        $outstandingFees = [
            [
                'id' => 77,
                'description' => 'fee 77',
                'licence' => [
                    'id' => 7,
                    'licNo' => 'LIC7',
                ],
            ],
        ];

        // mocks ...

        $mockRequest = m::mock();
        $mockFeesResponse = m::mock();

        // expectations ...

        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(false);

        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        $this->sut
            ->shouldReceive('handleQuery')
            ->with(m::type(OutstandingFeesQry::class))
            ->andReturn($mockFeesResponse);

        $mockFeesResponse
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn(['outstandingFees' => $outstandingFees]);

        $this->sut->shouldReceive('params')->with('fee')->once()->andReturn('99');

        $this->setExpectedException('\Common\Exception\ResourceNotFoundException');

        $this->sut->payFeesAction();
    }

    public function testPayFeesActionPostAndPay()
    {
        // data
        $organisationId = 99;

        // mocks ...

        $mockRequest = m::mock();
        $mockUrlHelper = m::mock();
        $this->sm->setService('Helper\Url', $mockUrlHelper);
        $mockPayCmdResponse = m::mock();
        $mockPaymentQryResponse = m::mock();

        // expectations ...

        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn(
                [
                    'form-actions' => [
                        'pay' => '',
                    ]
                ]
            );

        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->once()
            ->andReturn('77,88');

        $mockUrlHelper
            ->shouldReceive('fromRoute')
            ->with('fees/result', [], ['force_canonical' => true], true)
            ->andReturn('RESULT_URL');

        $this->sut
            ->shouldReceive('handleCommand')
            ->with(m::type(PayOutstandingFeesCmd::class))
            ->andReturn($mockPayCmdResponse);

        $paymentId = 69;
        $mockPayCmdResponse
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn(
                [
                    'id' => [
                        'payment' => $paymentId
                    ],
                ]
            );

        $this->sut
            ->shouldReceive('handleQuery')
            ->once()
            ->with(m::type(PaymentByIdQry::class))
            ->andReturn($mockPaymentQryResponse);
        $mockPaymentQryResponse
            ->shouldReceive('getResult')
            ->andReturn(
                [
                    'id' => $paymentId,
                    'guid' => 'OLCS-foo-123',
                    'gatewayUrl' => 'GATEWAY_URL',
                    'status' => [
                        'id' => FeesController::STATUS_PAID
                    ],
                ]
            );

        $view = $this->sut->payFeesAction();

        $this->assertEquals('cpms/payment', $view->getTemplate());

        $this->assertEquals(
            [
                'gateway' => 'GATEWAY_URL',
                'data' => [
                    'receipt_reference' => 'OLCS-foo-123'
                ]
            ],
            $view->getVariables()
        );
    }

    public function testPayFeesActionPostAndPayError()
    {
        // data
        $organisationId = 99;

        // mocks ...

        $mockRequest = m::mock();
        $mockUrlHelper = m::mock();
        $this->sm->setService('Helper\Url', $mockUrlHelper);
        $mockPayCmdResponse = m::mock();

        // expectations ...

        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn(
                [
                    'form-actions' => [
                        'pay' => '',
                    ]
                ]
            );

        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->once()
            ->andReturn('77,88');

        $mockUrlHelper
            ->shouldReceive('fromRoute')
            ->with('fees/result', [], ['force_canonical' => true], true)
            ->andReturn('RESULT_URL');

        $this->sut
            ->shouldReceive('handleCommand')
            ->with(m::type(PayOutstandingFeesCmd::class))
            ->andReturn($mockPayCmdResponse);

        $mockPayCmdResponse
            ->shouldReceive('isOk')
            ->andReturn(false);

        $this->sut->shouldReceive('addErrorMessage')->once()->with('payment-failed');

        $this->sut
            ->shouldReceive('redirect->toRoute')
            ->with('fees')
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->payFeesAction());
    }

    public function testHandleResultActionSuccess()
    {
        parse_str(
            'receipt_reference=OLCS-01-20150506-095652-1F516AA9&code=800
            &message=Payment+reference+issued%2C+request+sent+to+gateway%2C+awaiting+response+from+gateway',
            $query
        );

        $mockRequest = m::mock();
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockRequest
            ->shouldReceive('getQuery')
            ->andReturn($query);

        $mockCompleteResponse = m::mock();
        $this->sut
            ->shouldReceive('handleCommand')
            ->once()
            ->with(m::type(CompletePaymentCmd::class))
            ->andReturn($mockCompleteResponse);
        $paymentId = 69;
        $mockCompleteResponse
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn(
                [
                    'id' => [
                        'payment' => $paymentId
                    ],
                ]
            );

        $mockPaymentResponse = m::mock();
        $this->sut
            ->shouldReceive('handleQuery')
            ->once()
            ->with(m::type(PaymentByIdQry::class))
            ->andReturn($mockPaymentResponse);
        $mockPaymentResponse
            ->shouldReceive('getResult')
            ->andReturn(
                [
                    'id' => $paymentId,
                    'status' => [
                        'id' => FeesController::STATUS_PAID
                    ],
                ]
            );

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('fees/receipt', ['reference' => 'OLCS-01-20150506-095652-1F516AA9'])
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->handleResultAction());
    }

    /**
     * @param string $status
     * @param boolean $shouldShowError
     *
     * @dataProvider handleResultFailedProvider
     */
    public function testHandleResultActionFailed($status, $shouldShowError)
    {
        parse_str(
            'receipt_reference=OLCS-01-20150506-095652-1F516AA9&code=800
            &message=Payment+reference+issued%2C+request+sent+to+gateway%2C+awaiting+response+from+gateway',
            $query
        );

        $mockRequest = m::mock();
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockRequest
            ->shouldReceive('getQuery')
            ->andReturn($query);

        $mockCompleteResponse = m::mock();
        $this->sut
            ->shouldReceive('handleCommand')
            ->once()
            ->with(m::type(CompletePaymentCmd::class))
            ->andReturn($mockCompleteResponse);
        $paymentId = 69;
        $mockCompleteResponse
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn(
                [
                    'id' => [
                        'payment' => $paymentId
                    ],
                ]
            );

        $mockPaymentResponse = m::mock();
        $this->sut
            ->shouldReceive('handleQuery')
            ->once()
            ->with(m::type(PaymentByIdQry::class))
            ->andReturn($mockPaymentResponse);
        $mockPaymentResponse
            ->shouldReceive('getResult')
            ->andReturn(
                [
                    'id' => $paymentId,
                    'status' => [
                        'id' => $status
                    ],
                ]
            );

        if ($shouldShowError) {
            $this->sut->shouldReceive('addErrorMessage')->once();
        } else {
            $this->sut->shouldReceive('addErrorMessage')->never();
        }

        $this->sut
            ->shouldReceive('redirect->toRoute')
            ->with('fees')
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->handleResultAction());
    }

    /**
     * @return array
     */
    public function handleResultFailedProvider()
    {
        return [
            [FeesController::STATUS_FAILED, true],
            [FeesController::STATUS_CANCELLED, false],
            ['invalid', true],
        ];
    }

    public function testHandleResultActionError()
    {
        parse_str(
            'receipt_reference=OLCS-01-20150506-095652-1F516AA9&code=800
            &message=Payment+reference+issued%2C+request+sent+to+gateway%2C+awaiting+response+from+gateway',
            $query
        );

        $mockRequest = m::mock();
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockRequest
            ->shouldReceive('getQuery')
            ->andReturn($query);

        $mockCompleteResponse = m::mock();
        $this->sut
            ->shouldReceive('handleCommand')
            ->once()
            ->with(m::type(CompletePaymentCmd::class))
            ->andReturn($mockCompleteResponse);
        $mockCompleteResponse
            ->shouldReceive('isOk')
            ->andReturn(false);

        $this->sut->shouldReceive('addErrorMessage')
            ->once()
            ->shouldReceive('redirect->toRoute')
            ->with('fees')
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->handleResultAction());
    }

    public function testReceiptAction()
    {
        $paymentRef = 'OLCS-1234-WXYZ';
        $paymentId = 99;
        $fee1 = [
            'id' => 77,
            'description' => 'fee 77',
            'licence' => [
                'id' => 7,
                'licNo' => 'LIC7',
                'organisation' => [
                    'name' => 'Big Trucks Ltd.',
                ],
            ],
        ];
        $fee2 = [
            'id' => 88,
            'description' => 'fee 88',
            'licence' => [
                'id' => 8,
                'licNo' => 'LIC8',
                'organisation' => [
                    'name' => 'Big Trucks Ltd.',
                ],
            ],
        ];
        $payment = [
            'id' => $paymentId,
            'completedDate' => '2015-05-07',
            'feePayments' => [
                [
                    'fee' => $fee1,
                ],
                [
                    'fee' => $fee2,
                ],
            ],
        ];
        $fees = [$fee1, $fee2];

        $mockTableService = m::mock();
        $this->sm->setService('Table', $mockTableService);
        $mockTable = m::mock();
        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

        $this->sut->shouldReceive('params->fromRoute')
            ->with('reference')
            ->once()
            ->andReturn($paymentRef);

        $mockPaymentResponse = m::mock();
        $this->sut
            ->shouldReceive('handleQuery')
            ->with(m::type(PaymentByReferenceQry::class))
            ->andReturn($mockPaymentResponse);

        $mockPaymentResponse
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($payment);

        $mockTableService
            ->shouldReceive('buildTable')
            ->with('pay-fees', $fees, [], false)
            ->andReturn($mockTable);

        $mockTranslator
            ->shouldReceive('translate')
            ->with('pay-fees.success.table.title')
            ->andReturn('TABLE TITLE');

        $mockTable
            ->shouldReceive('setVariable')
            ->with('title', 'TABLE TITLE');

        $view = $this->sut->receiptAction();

        $this->assertEquals('pages/fees/payment-success', $view->getTemplate());

        $this->assertSame($mockTable, $view->getVariable('table'));
        $this->assertEquals($fees, $view->getVariable('fees'));
        $this->assertEquals($payment, $view->getVariable('payment'));
    }

    public function testReceiptActionInvalidPaymentRef()
    {
        $paymentRef = 'OLCS-1234-WXYZ';

        $this->sut->shouldReceive('params->fromRoute')
            ->with('reference')
            ->once()
            ->andReturn($paymentRef);

        $mockPaymentResponse = m::mock();
        $this->sut
            ->shouldReceive('handleQuery')
            ->with(m::type(PaymentByReferenceQry::class))
            ->andReturn($mockPaymentResponse);

        $mockPaymentResponse
            ->shouldReceive('isOk')
            ->andReturn(false);

        $this->setExpectedException('\Common\Exception\ResourceNotFoundException');

        $this->sut->receiptAction();
    }

    public function testPrintAction()
    {
        $paymentRef = 'OLCS-1234-WXYZ';
        $paymentId = 99;
        $fee1 = [
            'id' => 77,
            'description' => 'fee 77',
            'licence' => [
                'id' => 7,
                'licNo' => 'LIC7',
                'organisation' => [
                    'name' => 'Big Trucks Ltd.',
                ],
            ],
        ];
        $fee2 = [
            'id' => 88,
            'description' => 'fee 88',
            'licence' => [
                'id' => 8,
                'licNo' => 'LIC8',
                'organisation' => [
                    'name' => 'Big Trucks Ltd.',
                ],
            ],
        ];
        $payment = [
            'id' => $paymentId,
            'completedDate' => '2015-05-07',
            'feePayments' => [
                [
                    'fee' => $fee1,
                ],
                [
                    'fee' => $fee2,
                ],
            ],
        ];
        $fees = [$fee1, $fee2];

        $mockTableService = m::mock();
        $this->sm->setService('Table', $mockTableService);
        $mockTable = m::mock();
        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

        $this->sut->shouldReceive('params->fromRoute')
            ->with('reference')
            ->once()
            ->andReturn($paymentRef);

        $mockPaymentResponse = m::mock();
        $this->sut
            ->shouldReceive('handleQuery')
            ->with(m::type(PaymentByReferenceQry::class))
            ->andReturn($mockPaymentResponse);

        $mockPaymentResponse
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($payment);

        $mockTableService
            ->shouldReceive('buildTable')
            ->with('pay-fees', $fees, [], false)
            ->andReturn($mockTable);

        $mockTranslator
            ->shouldReceive('translate')
            ->with('pay-fees.success.table.title')
            ->andReturn('TABLE TITLE');

        $mockTable
            ->shouldReceive('setVariable')
            ->with('title', 'TABLE TITLE');

        $view = $this->sut->printAction();

        $this->assertEquals('pages/fees/payment-success-print', $view->getTemplate());

        $this->assertSame($mockTable, $view->getVariable('table'));
        $this->assertEquals($fees, $view->getVariable('fees'));
        $this->assertEquals($payment, $view->getVariable('payment'));

        $this->assertInstanceOf('\Olcs\View\Model\ReceiptViewModel', $view);
    }
}
