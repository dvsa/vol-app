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
        $this->sut = m::mock('\Olcs\Controller\FeesController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testIndexAction()
    {
        $fees = [
            'Count' => 3,
            'Results' => [
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
            ],
        ];

        $organisationId = 99;

        // mocks
        $mockNavigation = m::mock();
        $this->sm->setService('Olcs\Navigation\DashboardNavigation', $mockNavigation);

        $mockFeeService = m::mock();
        $this->sm->setService('Entity\Fee', $mockFeeService);

        $mockTableService = m::mock();
        $this->sm->setService('Table', $mockTableService);

        $mockTable = m::mock();

        // expectations
        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        $mockFeeService
            ->shouldReceive('getOutstandingFeesForOrganisation')
            ->with($organisationId)
            ->once()
            ->andReturn($fees);

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
                    ->with('count', 0)
                    ->getMock()
            );

        $mockTableService
            ->shouldReceive('buildTable')
            ->once()
            ->with(
                'fees',
                [
                    [
                        'id' => 1,
                        'description' => 'fee 1',
                        'licNo' => 'LIC7',
                    ],
                    [
                        'id' => 2,
                        'description' => 'fee 2',
                        'licNo' => 'LIC8',
                    ],
                    [
                        'id' => 3,
                        'description' => 'fee 3',
                        'licNo' => 'LIC9',
                    ],
                ]
            )
            ->andReturn($mockTable);

        $view = $this->sut->indexAction();

        // assertions
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertEquals('fees', $view->getTemplate());
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
            'Count' => 1,
            'Results' => [
                [
                    'id' => 77,
                    'description' => 'fee 77',
                    'licence' => [
                        'id' => 7,
                        'licNo' => 'LIC7',
                    ],
                ],
            ],
        ];

        // mocks ...

        $mockRequest = m::mock();
        $mockFeeService = m::mock();
        $this->sm->setService('Entity\Fee', $mockFeeService);
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockForm = m::mock();

        // expectations ...

        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(false);

        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        $mockFeeService
            ->shouldReceive('getOutstandingFeesForOrganisation')
            ->with($organisationId)
            ->once()
            ->andReturn($outstandingFees);

        $this->sut->shouldReceive('params')->with('fee')->once()->andReturn('77');

        $mockFormHelper
            ->shouldReceive('createForm')
            ->with('FeePayment')
            ->once()
            ->andReturn($mockForm);

        $view = $this->sut->payFeesAction();

        // assertions...

        $this->assertEquals(
            [
                'id' => 77,
                'description' => 'fee 77',
                'licence' => [
                    'id' => 7,
                    'licNo' => 'LIC7',
                ],
            ],
            $view->getVariable('fee')
        );

        $this->assertSame(
            $mockForm,
            $view->getVariable('form')
        );

        $this->assertEquals('pay-fee', $view->getTemplate());
    }

    public function testPayFeesActionShowMultipleFees()
    {
        // data
        $organisationId = 99;
        $outstandingFees = [
            'Count' => 2,
            'Results' => [
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
            ],
        ];

        // mocks ...

        $mockRequest = m::mock();
        $mockFeeService = m::mock();
        $this->sm->setService('Entity\Fee', $mockFeeService);
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockForm = m::mock();
        $mockTableService = m::mock();
        $this->sm->setService('Table', $mockTableService);
        $mockTable = m::mock();

        // expectations ...

        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(false);

        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        $mockFeeService
            ->shouldReceive('getOutstandingFeesForOrganisation')
            ->with($organisationId)
            ->once()
            ->andReturn($outstandingFees);

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
            ->with(
                'pay-fees',
                [
                    [
                        'id' => 77,
                        'description' => 'fee 77',
                        'licNo' => 'LIC7',
                    ],
                    [
                        'id' => 88,
                        'description' => 'fee 88',
                        'licNo' => 'LIC8',
                    ],
                    // 99 should get filtered out
                ]
            )
            ->andReturn($mockTable);

        $view = $this->sut->payFeesAction();

        // assertions...

        $this->assertSame($mockTable, $view->getVariable('table'));

        $this->assertSame($mockForm, $view->getVariable('form'));

        $this->assertEquals('pay-fees', $view->getTemplate());
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
            'Count' => 1,
            'Results' => [
                [
                    'id' => 77,
                    'description' => 'fee 77',
                    'licence' => [
                        'id' => 7,
                        'licNo' => 'LIC7',
                    ],
                ],
            ],
        ];

        // mocks ...

        $mockRequest = m::mock();
        $mockFeeService = m::mock();
        $this->sm->setService('Entity\Fee', $mockFeeService);

        // expectations ...

        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(false);

        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        $mockFeeService
            ->shouldReceive('getOutstandingFeesForOrganisation')
            ->with($organisationId)
            ->once()
            ->andReturn($outstandingFees);

        $this->sut->shouldReceive('params')->with('fee')->once()->andReturn('99');

        $this->setExpectedException('\Common\Exception\ResourceNotFoundException');

        $this->sut->payFeesAction();
    }

    public function testPayFeesActionPostAndPay()
    {
        // data
        $organisationId = 99;
        $outstandingFees = [
            'Count' => 2,
            'Results' => [
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
            ],
        ];

        // mocks ...

        $mockRequest = m::mock();
        $mockFeeService = m::mock();
        $this->sm->setService('Entity\Fee', $mockFeeService);
        $mockCpmsService = m::mock();
        $this->sm->setService('Cpms\FeePayment', $mockCpmsService);
        $mockUrlHelper = m::mock();
        $this->sm->setService('Helper\Url', $mockUrlHelper);

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

        $mockFeeService
            ->shouldReceive('getOutstandingFeesForOrganisation')
            ->with($organisationId)
            ->once()
            ->andReturn($outstandingFees);

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->once()
            ->andReturn('77,88');

        $mockCpmsService
            ->shouldReceive('hasOutstandingPayment')
            ->twice()
            ->andReturn(false, true)
            ->shouldReceive('resolveOutstandingPayments')
            ->once()
            ->andReturn(false);

        $mockUrlHelper
            ->shouldReceive('fromRoute')
            ->with('fees/result', [], ['force_canonical' => true], true)
            ->andReturn('RESULT_URL');

        $mockCpmsService
            ->shouldReceive('initiateCardRequest')
            ->once()
            ->with(
                $organisationId,
                'RESULT_URL',
                [
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
                    ]
                ]
            )
            ->andReturn(
                [
                    'gateway_url' => 'GATEWAY_URL',
                    'receipt_reference' => 'OLCS-foo-123',
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

    public function testPayFeesActionPostAndPayFeesAlreadyPaid()
    {
        // data
        $organisationId = 99;
        $outstandingFees = [
            'Count' => 2,
            'Results' => [
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
            ],
        ];

        // mocks ...

        $mockRequest = m::mock();
        $mockFeeService = m::mock();
        $this->sm->setService('Entity\Fee', $mockFeeService);
        $mockCpmsService = m::mock();
        $this->sm->setService('Cpms\FeePayment', $mockCpmsService);
        $mockUrlHelper = m::mock();
        $this->sm->setService('Helper\Url', $mockUrlHelper);

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

        $mockFeeService
            ->shouldReceive('getOutstandingFeesForOrganisation')
            ->with($organisationId)
            ->once()
            ->andReturn($outstandingFees);

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->once()
            ->andReturn('77,88');

        $mockCpmsService
            ->shouldReceive('hasOutstandingPayment')
            ->andReturn(true)
            ->shouldReceive('resolveOutstandingPayments')
            ->andReturn(true);

        $this->sut
            ->shouldReceive('redirect->toRoute')
            ->with('fees')
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->payFeesAction());
    }

    public function testPayFeesActionPostAndPayError()
    {
        // data
        $organisationId = 99;
        $outstandingFees = [
            'Count' => 2,
            'Results' => [
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
            ],
        ];

        // mocks ...

        $mockRequest = m::mock();
        $mockFeeService = m::mock();
        $this->sm->setService('Entity\Fee', $mockFeeService);
        $mockCpmsService = m::mock();
        $this->sm->setService('Cpms\FeePayment', $mockCpmsService);
        $mockUrlHelper = m::mock();
        $this->sm->setService('Helper\Url', $mockUrlHelper);

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

        $mockFeeService
            ->shouldReceive('getOutstandingFeesForOrganisation')
            ->with($organisationId)
            ->once()
            ->andReturn($outstandingFees);

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->once()
            ->andReturn('77,88');

        $mockCpmsService
            ->shouldReceive('hasOutstandingPayment')
            ->andReturn(false);

        $mockUrlHelper
            ->shouldReceive('fromRoute')
            ->with('fees/result', [], ['force_canonical' => true], true)
            ->andReturn('RESULT_URL');

        $mockCpmsService
            ->shouldReceive('initiateCardRequest')
            ->once()
            ->andThrow(new \Common\Service\Cpms\Exception\PaymentInvalidResponseException());

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
        $mockCpmsService = m::mock();
        $this->sm->setService('Cpms\FeePayment', $mockCpmsService);

        $mockRequest
            ->shouldReceive('getQuery')
            ->andReturn($query);

        $mockCpmsService
            ->shouldReceive('handleResponse')
            ->once()
            ->with($query, 'fpm_card_online') // FeePaymentEntityService::METHOD_CARD_ONLINE
            ->andReturn(PaymentEntityService::STATUS_PAID);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('fees/receipt', ['reference' => 'OLCS-01-20150506-095652-1F516AA9'])
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->handleResultAction());
    }

    /**
     * @dataProvider exceptionFromPaymentServiceProvider
     */
    public function testPaymentResultActionExceptionFromPaymentService($exceptionClass)
    {
        parse_str(
            'receipt_reference=OLCS-01-20150506-095652-1F516AA9&code=800
            &message=Payment+reference+issued%2C+request+sent+to+gateway%2C+awaiting+response+from+gateway',
            $query
        );

        $mockRequest = m::mock();
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);
        $mockCpmsService = m::mock();
        $this->sm->setService('Cpms\FeePayment', $mockCpmsService);

        $mockRequest
            ->shouldReceive('getQuery')
            ->andReturn($query);

        $mockCpmsService
            ->shouldReceive('handleResponse')
            ->once()
            ->with($query, 'fpm_card_online')
            ->andThrow(new $exceptionClass());

        $this->sut->shouldReceive('addErrorMessage')
            ->once()
            ->shouldReceive('redirect->toRoute')
            ->with('fees')
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->handleResultAction());
    }

    public function exceptionFromPaymentServiceProvider()
    {
        return [
            ['Common\Service\Cpms\Exception\PaymentInvalidStatusException'],
            ['Common\Service\Cpms\Exception\PaymentNotFoundException']
        ];
    }

    /**
     * @dataProvider failureFromPaymentServiceProvider
     */
    public function testPaymentResultActionFailureFromPaymentService($responseCode, $expectedErrorMessage)
    {
        parse_str(
            'receipt_reference=OLCS-01-20150506-095652-1F516AA9&code=800
            &message=Payment+reference+issued%2C+request+sent+to+gateway%2C+awaiting+response+from+gateway',
            $query
        );

        $mockRequest = m::mock();
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);
        $mockCpmsService = m::mock();
        $this->sm->setService('Cpms\FeePayment', $mockCpmsService);

        $mockRequest
            ->shouldReceive('getQuery')
            ->andReturn($query);

        $mockCpmsService
            ->shouldReceive('handleResponse')
            ->once()
            ->with($query, 'fpm_card_online')
            ->andReturn($responseCode);

        if ($expectedErrorMessage) {
            $this->sut->shouldReceive('addErrorMessage')
                ->once()
                ->with($expectedErrorMessage);
        }

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('fees')
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->handleResultAction());
    }

    public function failureFromPaymentServiceProvider()
    {
        return [
            [PaymentEntityService::STATUS_CANCELLED, null],
            [PaymentEntityService::STATUS_FAILED, 'payment-failed'],
            ['unknown_status', 'payment-failed'],
        ];
    }

    public function testReceiptAction()
    {
        $view = $this->sut->receiptAction();

        // currently just a placeholder
        $this->assertEquals('pages/placeholder', $view->getTemplate());
    }
}
