<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service;

use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Service\Cpms\ApiServiceFactory;
use Dvsa\Olcs\Cpms\Service\ApiService;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction as FeeTransactionEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as TransactionEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\CpmsResponseException;
use Dvsa\Olcs\Api\Service\CpmsV2HelperService;
use Dvsa\Olcs\Api\Service\CpmsV2HelperServiceException;
use Dvsa\Olcs\Api\Service\FeesHelperService;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Olcs\Logging\Log\Logger;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\CpmsV2HelperService::class)]
class CpmsV2HelperServiceTest extends MockeryTestCase
{
    public const CHEQUE_NR = 100001;

    /** @var CpmsV2HelperService */
    protected $sut;

    /** @var \Mockery\MockInterface (ApiService) */
    protected $cpmsClient;

    /** @var \Dvsa\Olcs\Api\Service\FeesHelperService | m\MockInterface */
    protected $feesHelper;

    protected $identity;

    protected $options;

    public function setUp(): void
    {
        $this->options = m::mock()
            ->shouldReceive('getDomain')
            ->andReturn('fake-domain')
            ->getMock();

        // Mock the CPMS client
        $this->cpmsClient = m::mock()
            ->shouldReceive('getOptions')
            ->andReturn($this->options)
            ->getMock();

        $this->feesHelper = m::mock(FeesHelperService::class);

        $this->identity = m::mock();

        $config = [
            'cpms_credentials' => [
                'client_id_ni' => 'client_id_ni',
                'client_secret_ni' => 'client_secret_ni',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret'
            ],
            'cpms_api' => [
                'identity_provider' => 'identity'
            ]
        ];

        // Create service with mocked dependencies
        $this->sut = $this->createService($this->cpmsClient, $this->feesHelper, $config);

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        parent::setUp();
    }

    private function createService(mixed $api, mixed $feesHelper, array $config = []): mixed
    {
        /** @var ContainerInterface | m\MockInterface $sm */
        $sm = m::mock(ContainerInterface::class);
        $sm
            ->shouldReceive('get')
            ->with(ApiServiceFactory::class)
            ->andReturn($api)
            ->shouldReceive('get')
            ->with('FeesHelperService')
            ->andReturn($feesHelper)
            ->shouldReceive('get')
            ->with('config')
            ->andReturn($config)
            ->shouldReceive('get')
            ->with('identity')
            ->andReturn($this->identity);

        $sut = new CpmsV2HelperService();
        return $sut->__invoke($sm, CpmsV2HelperService::class);
    }

    public function testHandleResponse(): void
    {
        $data = ['unit_data'];
        $ref = 'unit_ref';

        $this->cpmsClient
            ->shouldReceive('put')
            ->once()
            ->with('/api/gateway/' . $ref . '/complete', ApiService::SCOPE_CARD, $data)
            ->andReturn('EXPECTED');

        static::assertEquals('EXPECTED', $this->sut->handleResponse($ref, $data));
    }

    public function testHanldeResponseWithNi(): void
    {
        $data = ['unit_data'];
        $ref = 'unit_ref';

        $mockFee = m::mock()
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getIsNi')
                    ->andReturn('Y')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->mockSchemaIdChange(true);

        $this->cpmsClient
            ->shouldReceive('put')
            ->once()
            ->with('/api/gateway/' . $ref . '/complete', ApiService::SCOPE_CARD, $data)
            ->andReturn('EXPECTED')
            ->shouldReceive('getIdenity')->andReturn(4);

        static::assertEquals('EXPECTED', $this->sut->handleResponse($ref, $data, $mockFee));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetPaymentStatus')]
    public function testGetPaymentStatus(mixed $response, mixed $expect): void
    {
        $ref = 'unit_Ref';

        /** @var CpmsV2HelperService | m\MockInterface $sut */
        $sut = m::mock(CpmsV2HelperService::class . '[send]')
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('send')
            ->with('get', '/api/payment/' . $ref, ApiService::SCOPE_QUERY_TXN, m::type('array'), 'fee')
            ->once()
            ->andReturn($response)
            ->getMock();

        static::assertEquals($expect, $sut->getPaymentStatus($ref, 'fee'));
    }

    public static function dpTestGetPaymentStatus(): array
    {
        return [
            [
                'response' => [
                    'payment_status' => [
                        'code' => 'EXPECT',
                    ],
                ],
                'expect' => ['code' => 'EXPECT', 'message' => 'No error message from CPMS, no error code from CPMS'],
            ],
            [
                'response' => [],
                'expect' => ['code' => null, 'message' => 'No error message from CPMS, no error code from CPMS'],
            ],
        ];
    }

    public function testGetPaymentStatusWithMessageFromCpms(): void
    {
        $ref = 'unit_Ref';
        $response = [
            'code' => 701,
            'message' => 'Payment not found'
        ];

        /** @var CpmsV2HelperService | m\MockInterface $sut */
        $sut = m::mock(CpmsV2HelperService::class . '[send]')
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('send')
            ->with('get', '/api/payment/' . $ref, ApiService::SCOPE_QUERY_TXN, m::type('array'), 'fee')
            ->once()
            ->andReturn($response)
            ->getMock();

        $expected = [
            'code' => null,
            'message' => 'Payment not found, code: 701'
        ];

        static::assertEquals($expected, $sut->getPaymentStatus($ref, 'fee'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('miscParamsProvider')]
    public function testInitiateCardRequest(mixed $miscParams, mixed $expectedCustomer, mixed $expectedReceiver): void
    {
        $orgId = 99;
        $redirectUrl = 'http://olcs-selfserve/foo';

        $fees = [
            $this->getStubFee(1, 525.25, FeeEntity::ACCRUAL_RULE_IMMEDIATE, null, $orgId, '2015-08-29'),
            $this->getStubFee(2, 125.25, FeeEntity::ACCRUAL_RULE_LICENCE_START, '2014-12-25', $orgId, '2015-08-30'),
        ];

        $this->mockSchemaIdChange();

        $now = (new DateTime())->format('Y-m-d');

        $expectedParams = array_merge(
            $expectedCustomer,
            [
                'payment_data' => [
                    array_merge(
                        $expectedReceiver,
                        [
                            'line_identifier' => '1',
                            'amount' => '525.25',
                            'allocated_amount' => '525.25',
                            'net_amount' => '525.25',
                            'tax_amount' => '0.00',
                            'tax_code' => 'Z',
                            'tax_rate' => '0',
                            'invoice_date' => '2015-08-29',
                            'sales_reference' => '1',
                            'product_reference' => 'fee type description',
                            'product_description' => 'fee type description',
                            'rule_start_date' => $now,
                            'deferment_period' => '1',
                            'country_code' => 'GB',
                            'sales_person_reference' => 'Traffic Area Ref',
                        ]
                    ),
                    array_merge(
                        $expectedReceiver,
                        [
                            'line_identifier' => '2',
                            'amount' => '125.25',
                            'allocated_amount' => '125.25',
                            'net_amount' => '125.25',
                            'tax_amount' => '0.00',
                            'tax_code' => 'Z',
                            'tax_rate' => '0',
                            'invoice_date' => '2015-08-30',
                            'sales_reference' => '2',
                            'product_reference' => 'fee type description',
                            'product_description' => 'fee type description',
                            'rule_start_date' => '2014-12-25',
                            'deferment_period' => '60',
                            'country_code' => 'GB',
                            'sales_person_reference' => 'Traffic Area Ref',
                        ]
                    )
                ],
                'total_amount' => '650.50',
                'redirect_uri' => $redirectUrl,
                'disable_redirection' => true,
                'scope' => 'CARD',
                'refund_overpayment' => false,
                'country_code' => 'GB',
            ]
        );

        $response = [
            'receipt_reference' => 'guid_123',
            'schema_id' => 'client_id'
        ];

        $this->cpmsClient
            ->shouldReceive('post')
            ->with('/api/payment/card', 'CARD', $expectedParams)
            ->once()
            ->andReturn($response);

        $result = $this->sut->initiateCardRequest($redirectUrl, $fees, $miscParams);

        $this->assertSame($response, $result);
    }

    public static function miscParamsProvider(): array
    {
        return [
            'mics payment' => [
                [
                    'customer_reference' => 'foo',
                    'customer_name' => 'bar',
                    'customer_address' => [
                        'addressLine1' => 'line1',
                        'addressLine2' => 'line2',
                        'addressLine3' => 'line3',
                        'addressLine4' => 'line4',
                        'town' => 'town',
                        'postcode' => 'pc'
                    ]
                ],
                [
                    'customer_reference' => 'foo',
                    'customer_name' => 'bar',
                    'customer_manager_name' => 'bar',
                    'customer_address' => [
                        'line_1' => 'line1',
                        'line_2' => 'line2',
                        'line_3' => 'line3',
                        'line_4' => 'line4',
                        'city' => 'town',
                        'postcode' => 'pc'
                    ]
                ],
                [
                    'receiver_reference' => 'foo',
                    'receiver_name' => 'bar',
                    'receiver_address' => [
                        'line_1' => 'line1',
                        'line_2' => 'line2',
                        'line_3' => 'line3',
                        'line_4' => 'line4',
                        'city' => 'town',
                        'postcode' => 'pc'
                    ]
                ]
            ],
            'usual payment' => [
                [],
                [
                    'customer_reference' => 99,
                    'customer_name' => 'some organisation',
                    'customer_manager_name' => 'some organisation',
                    'customer_address' => [
                        'line_1' => 'Foo',
                        'line_2' => null,
                        'line_3' => null,
                        'line_4' => null,
                        'city' => 'Bar',
                        'postcode' => 'LS9 6NF',
                    ],
                ],
                [
                    'receiver_reference' => 'OB1234567',
                    'receiver_name' => 'some organisation',
                    'receiver_address' => [
                        'line_1' => 'Foo',
                        'line_2' => null,
                        'line_3' => null,
                        'line_4' => null,
                        'city' => 'Bar',
                        'postcode' => 'LS9 6NF',
                    ],
                ]
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('miscParamsProvider')]
    public function testInitiateCnpRequest(mixed $miscParams, mixed $expectedCustomer, mixed $expectedReceiver): void
    {
        $orgId = 99;
        $redirectUrl = 'http://olcs-selfserve/foo';

        $fees = [
            $this->getStubFee(1, 525.25, FeeEntity::ACCRUAL_RULE_IMMEDIATE, null, $orgId, '2015-08-29'),
            $this->getStubFee(2, 125.25, FeeEntity::ACCRUAL_RULE_LICENCE_START, '2014-12-25', $orgId, '2015-08-30'),
        ];

        $this->mockSchemaIdChange();

        $now = (new DateTime())->format('Y-m-d');

        $expectedParams = array_merge(
            $expectedCustomer,
            [
                'payment_data' => [
                    array_merge(
                        $expectedReceiver,
                        [
                            'line_identifier' => '1',
                            'amount' => '525.25',
                            'allocated_amount' => '525.25',
                            'net_amount' => '525.25',
                            'tax_amount' => '0.00',
                            'tax_code' => 'Z',
                            'tax_rate' => '0',
                            'invoice_date' => '2015-08-29',
                            'sales_reference' => '1',
                            'product_reference' => 'fee type description',
                            'product_description' => 'fee type description',
                            'rule_start_date' => $now,
                            'deferment_period' => '1',
                            'country_code' => 'GB',
                            'sales_person_reference' => 'Traffic Area Ref',
                        ]
                    ),
                    array_merge(
                        $expectedReceiver,
                        [
                            'line_identifier' => '2',
                            'amount' => '125.25',
                            'allocated_amount' => '125.25',
                            'net_amount' => '125.25',
                            'tax_amount' => '0.00',
                            'tax_code' => 'Z',
                            'tax_rate' => '0',
                            'invoice_date' => '2015-08-30',
                            'sales_reference' => '2',
                            'product_reference' => 'fee type description',
                            'product_description' => 'fee type description',
                            'rule_start_date' => '2014-12-25',
                            'deferment_period' => '60',
                            'country_code' => 'GB',
                            'sales_person_reference' => 'Traffic Area Ref',
                        ]
                    )
                ],
                'total_amount' => '650.50',
                'redirect_uri' => $redirectUrl,
                'disable_redirection' => true,
                'scope' => 'CNP',
                'refund_overpayment' => false,
                'country_code' => 'GB',
            ]
        );

        $response = [
            'receipt_reference' => 'guid_123',
            'schema_id' => 'client_id'
        ];

        $this->cpmsClient
            ->shouldReceive('post')
            ->with('/api/payment/cardholder-not-present', 'CNP', $expectedParams)
            ->once()
            ->andReturn($response);

        $result = $this->sut->initiateCnpRequest($redirectUrl, $fees, $miscParams);

        $this->assertSame($response, $result);
    }

    public function testInitiateCardRequestInvalidApiResponse(): void
    {
        $this->expectException(CpmsResponseException::class);

        $this->mockSchemaIdChange();

        $this->cpmsClient
            ->shouldReceive('post')
            ->with('/api/payment/card', 'CARD', m::any())
            ->andReturn([]);

        $this->cpmsClient
            ->shouldReceive('getClient->getHttpClient->getResponse->getStatusCode')
            ->andReturn(400);

        $this->sut->initiateCardRequest('http://olcs-selfserve/foo', []);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('miscParamsProvider')]
    public function testRecordCashPaymentWithOverpayment(mixed $miscParams, mixed $expectedCustomer, mixed $expectedReceiver): void
    {
        $orgId = 99;

        // user input data
        $receiptDate = '2015-09-10';
        $slipNo = '12345';
        $amount = '1000';  // doesn't need to match fee total

        $fees = [
            $this->getStubFee(1, 500.00, FeeEntity::ACCRUAL_RULE_IMMEDIATE, null, $orgId, '2015-08-29'),
            $this->getStubFee(2, 100.00, FeeEntity::ACCRUAL_RULE_LICENCE_START, '2014-12-25', $orgId, '2015-08-30'),
            $this->getStubFee(
                3,
                400.00,
                FeeEntity::ACCRUAL_RULE_IMMEDIATE,
                null,
                $orgId,
                '2015-08-30',
                FeeTypeEntity::FEE_TYPE_ADJUSTMENT
            ),
        ];

        $this->mockSchemaIdChange();

        $now = (new DateTime())->format('Y-m-d');

        $expectedParams = array_merge(
            $expectedCustomer,
            [
                'payment_data' => [
                    array_merge(
                        $expectedReceiver,
                        [
                            'line_identifier' => '1',
                            'amount' => '500.00',
                            'allocated_amount' => '500.00',
                            'net_amount' => '500.00',
                            'tax_amount' => '0.00',
                            'tax_code' => 'Z',
                            'tax_rate' => '0',
                            'invoice_date' => '2015-08-29',
                            'sales_reference' => '1',
                            'product_reference' => 'fee type description',
                            'product_description' => 'fee type description',
                            'rule_start_date' => $now,
                            'deferment_period' => '1',
                            'country_code' => 'GB',
                            'sales_person_reference' => 'Traffic Area Ref',
                        ]
                    ),
                    array_merge(
                        $expectedReceiver,
                        [
                            'line_identifier' => '2',
                            'amount' => '100.00',
                            'allocated_amount' => '100.00',
                            'net_amount' => '100.00',
                            'tax_amount' => '0.00',
                            'tax_code' => 'Z',
                            'tax_rate' => '0',
                            'invoice_date' => '2015-08-30',
                            'sales_reference' => '2',
                            'product_reference' => 'fee type description',
                            'product_description' => 'fee type description',
                            'rule_start_date' => '2014-12-25',
                            'deferment_period' => '60',
                            'country_code' => 'GB',
                            'sales_person_reference' => 'Traffic Area Ref',
                        ]
                    )
                ],
                'total_amount' => '1000.00',
                'slip_number' => '12345',
                'batch_number' => '12345',
                'receipt_date' => '2015-09-10',
                'scope' => 'CASH',
                'refund_overpayment' => true,
                'country_code' => 'GB',
            ]
        );

        $response = [
            'code' => CpmsV2HelperService::RESPONSE_SUCCESS,
            'receipt_reference' => 'OLCS-1234-CASH',
            'schema_id' => 'client_id',
        ];

        $this->cpmsClient
            ->shouldReceive('post')
            ->with('/api/payment/cash', 'CASH', $expectedParams)
            ->once()
            ->andReturn($response);

        $this->feesHelper
            ->shouldReceive('allocatePayments')
            ->with('1000.00', $fees)
            ->andReturn(
                [
                    1 => '500.00',
                    2 => '100.00',
                    3 => '400.00',
                ]
            );

        $result = $this->sut->recordCashPayment($fees, $amount, $receiptDate, $slipNo, $miscParams);

        $this->assertSame($response, $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('miscParamsProvider')]
    public function testRecordChequePayment(mixed $miscParams, mixed $expectedCustomer, mixed $expectedReceiver): void
    {
        $orgId = 99;

        // user input data
        $receiptDate = '2015-09-10';
        $payer = 'Owen Money';
        $slipNo = '12345';
        $amount = '1000';
        $chequeNo = '0098765';
        $chequeDate = '2015-09-01';

        $fees = [
            $this->getStubFee(1, 525.25, FeeEntity::ACCRUAL_RULE_IMMEDIATE, null, $orgId, '2015-08-29'),
            $this->getStubFee(2, 125.25, FeeEntity::ACCRUAL_RULE_LICENCE_START, '2014-12-25', $orgId, '2015-08-30'),
        ];

        $this->mockSchemaIdChange();

        $now = (new DateTime())->format('Y-m-d');

        $expectedParams = array_merge(
            $expectedCustomer,
            [
                'payment_data' => [
                    array_merge(
                        $expectedReceiver,
                        [
                            'line_identifier' => '1',
                            'amount' => '525.25',
                            'allocated_amount' => '525.25',
                            'net_amount' => '525.25',
                            'tax_amount' => '0.00',
                            'tax_code' => 'Z',
                            'tax_rate' => '0',
                            'invoice_date' => '2015-08-29',
                            'sales_reference' => '1',
                            'product_reference' => 'fee type description',
                            'product_description' => 'fee type description',
                            'rule_start_date' => $now,
                            'deferment_period' => '1',
                            'country_code' => 'GB',
                            'sales_person_reference' => 'Traffic Area Ref',
                        ]
                    ),
                    array_merge(
                        $expectedReceiver,
                        [
                            'line_identifier' => '2',
                            'amount' => '125.25',
                            'allocated_amount' => '125.25',
                            'net_amount' => '125.25',
                            'tax_amount' => '0.00',
                            'tax_code' => 'Z',
                            'tax_rate' => '0',
                            'invoice_date' => '2015-08-30',
                            'sales_reference' => '2',
                            'product_reference' => 'fee type description',
                            'product_description' => 'fee type description',
                            'rule_start_date' => '2014-12-25',
                            'deferment_period' => '60',
                            'country_code' => 'GB',
                            'sales_person_reference' => 'Traffic Area Ref',
                        ]
                    )
                ],
                'total_amount' => '1000.00',
                'slip_number' => '12345',
                'batch_number' => '12345',
                'receipt_date' => '2015-09-10',
                'scope' => 'CHEQUE',
                'cheque_number' => '0098765',
                'cheque_date' => '2015-09-01',
                'name_on_cheque' => $payer,
                'refund_overpayment' => false,
                'country_code' => 'GB',
            ]
        );

        $response = [
            'code' => CpmsV2HelperService::RESPONSE_SUCCESS,
            'receipt_reference' => 'OLCS-1234-CHEQUE',
            'schema_id' => 'client_id',
        ];

        $this->cpmsClient
            ->shouldReceive('post')
            ->with('/api/payment/cheque', 'CHEQUE', $expectedParams)
            ->once()
            ->andReturn($response);

        $this->feesHelper
            ->shouldReceive('allocatePayments')
            ->with('1000.00', $fees)
            ->andReturn(
                [
                    1 => '525.25',
                    2 => '125.25'
                ]
            );

        $result = $this->sut->recordChequePayment(
            $fees,
            $amount,
            $receiptDate,
            $payer,
            $slipNo,
            $chequeNo,
            $chequeDate,
            $miscParams
        );

        $this->assertSame($response, $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('miscParamsProvider')]
    public function testRecordPostalOrderPayment(mixed $miscParams, mixed $expectedCustomer, mixed $expectedReceiver): void
    {
        $orgId = 99;

        // user input data
        $receiptDate = '2015-09-10';
        $slipNo = '12345';
        $amount = '1000';
        $poNo = '00666666';

        $fees = [
            $this->getStubFee(1, 525.25, FeeEntity::ACCRUAL_RULE_IMMEDIATE, null, $orgId, '2015-08-29'),
            $this->getStubFee(2, 125.25, FeeEntity::ACCRUAL_RULE_LICENCE_START, '2014-12-25', $orgId, '2015-08-30'),
        ];

        $this->mockSchemaIdChange();

        $now = (new DateTime())->format('Y-m-d');

        $expectedParams = array_merge(
            $expectedCustomer,
            [
                'payment_data' => [
                    array_merge(
                        $expectedReceiver,
                        [
                            'line_identifier' => '1',
                            'amount' => '525.25',
                            'allocated_amount' => '525.25',
                            'net_amount' => '525.25',
                            'tax_amount' => '0.00',
                            'tax_code' => 'Z',
                            'tax_rate' => '0',
                            'invoice_date' => '2015-08-29',
                            'sales_reference' => '1',
                            'product_reference' => 'fee type description',
                            'product_description' => 'fee type description',
                            'rule_start_date' => $now,
                            'deferment_period' => '1',
                            'country_code' => 'GB',
                            'sales_person_reference' => 'Traffic Area Ref',
                        ]
                    ),
                    array_merge(
                        $expectedReceiver,
                        [
                            'line_identifier' => '2',
                            'amount' => '125.25',
                            'allocated_amount' => '125.25',
                            'net_amount' => '125.25',
                            'tax_amount' => '0.00',
                            'tax_code' => 'Z',
                            'tax_rate' => '0',
                            'invoice_date' => '2015-08-30',
                            'sales_reference' => '2',
                            'product_reference' => 'fee type description',
                            'product_description' => 'fee type description',
                            'rule_start_date' => '2014-12-25',
                            'deferment_period' => '60',
                            'country_code' => 'GB',
                            'sales_person_reference' => 'Traffic Area Ref',
                        ]
                    )
                ],
                'total_amount' => '1000.00',
                'slip_number' => '12345',
                'batch_number' => '12345',
                'receipt_date' => '2015-09-10',
                'scope' => 'POSTAL_ORDER',
                'postal_order_number' => '00666666',
                'refund_overpayment' => false,
                'country_code' => 'GB',
            ]
        );

        $response = [
            'code' => CpmsV2HelperService::RESPONSE_SUCCESS,
            'receipt_reference' => 'OLCS-1234-PO',
            'schema_id' => 'client_id',
        ];

        $this->cpmsClient
            ->shouldReceive('post')
            ->with('/api/payment/postal-order', 'POSTAL_ORDER', $expectedParams)
            ->once()
            ->andReturn($response);

        $this->feesHelper
            ->shouldReceive('allocatePayments')
            ->with('1000.00', $fees)
            ->andReturn(
                [
                    1 => '525.25',
                    2 => '125.25'
                ]
            );

        $result = $this->sut->recordPostalOrderPayment(
            $fees,
            $amount,
            $receiptDate,
            $slipNo,
            $poNo,
            $miscParams
        );

        $this->assertSame($response, $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('authCodeProvider')]
    public function testGetPaymentAuthCode(mixed $authCode): void
    {
        $response = ['auth_code' => $authCode];

        $this->cpmsClient
            ->shouldReceive('get')
            ->with('/api/payment/MY-REFERENCE/auth-code', 'QUERY_TXN', [])
            ->once()
            ->andReturn($response);

        $this->mockSchemaIdChange();

        $result = $this->sut->getPaymentAuthCode('MY-REFERENCE', null);

        $this->assertSame($authCode, $result);
    }

    public static function authCodeProvider(): array
    {
        return [
            ['AUTH123'],
            [null]
        ];
    }

    public function testGetPaymentAuthCodeWithNi(): void
    {
        $response = ['auth_code' => 'AUTH123'];

        $this->cpmsClient
            ->shouldReceive('get')
            ->with('/api/payment/MY-REFERENCE/auth-code', 'QUERY_TXN', [])
            ->once()
            ->andReturn($response);

        $mockFee = m::mock(FeeEntity::class)
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getIsNi')
                    ->andReturn('Y')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->mockSchemaIdChange(true);

        $result = $this->sut->getPaymentAuthCode('MY-REFERENCE', $mockFee);

        $this->assertSame('AUTH123', $result);
    }

    /**
     * @param string $paymentMethod
     * @param string $expectedScope
     * @param string $expectedEndpointSuffix
     * @param array  $miscParams
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('reversalProvider')]
    public function testReversePayment(mixed $paymentMethod, mixed $expectedScope, mixed $expectedEndpointSuffix, mixed $customer, mixed $miscParams): void
    {
        $orgId = 99;

        $response = [
            'code' => CpmsV2HelperService::PAYMENT_PAYMENT_CHARGED_BACK,
            'message' => 'ok',
            'receipt_reference' => 'REVERSAL_REFERENCE',
            'schema_id' => 'client_id',
        ];

        $expectedParams = array_merge(
            $customer,
            ['scope' => $expectedScope]
        );

        $this->mockSchemaIdChange();

        $this->cpmsClient
            ->shouldReceive('post')
            ->with('/api/payment/MY-REFERENCE/' . $expectedEndpointSuffix, $expectedScope, $expectedParams)
            ->once()
            ->andReturn($response);

        $fees = [
            $this->getStubFee(1, 100.00, FeeEntity::ACCRUAL_RULE_IMMEDIATE, null, $orgId, '2015-11-04'),
        ];

        $result = $this->sut->reversePayment('MY-REFERENCE', $paymentMethod, $fees, $miscParams);

        $this->assertSame($response, $result);
    }

    public static function reversalProvider(): array
    {
        return [
            'cheque' => [
                FeeEntity::METHOD_CHEQUE,
                'CHEQUE_RD',
                'reversal',
                [
                    'customer_reference' => 'foo',
                    'customer_name' => 'bar',
                    'customer_manager_name' => 'bar',
                    'customer_address' => [
                        'line_1' => 'line1',
                        'line_2' => 'line2',
                        'line_3' => 'line3',
                        'line_4' => 'line4',
                        'city' => 'town',
                        'postcode' => 'postcode',
                    ]
                ],
                [
                    'customer_reference' => 'foo',
                    'customer_name' => 'bar',
                    'customer_address' => [
                        'addressLine1' => 'line1',
                        'addressLine2' => 'line2',
                        'addressLine3' => 'line3',
                        'addressLine4' => 'line4',
                        'town' => 'town',
                        'postcode' => 'postcode',
                    ]
                ]
            ],
            'cash' => [
                FeeEntity::METHOD_CASH,
                'CASH',
                'reversal',
                [
                    'customer_reference' => 99,
                    'customer_name' => 'some organisation',
                    'customer_manager_name' => 'some organisation',
                    'customer_address' => [
                        'line_1' => 'Foo',
                        'line_2' => null,
                        'line_3' => null,
                        'line_4' => null,
                        'city' => 'Bar',
                        'postcode' => 'LS9 6NF',
                    ],
                ],
                []
            ],
            'po' => [
                FeeEntity::METHOD_POSTAL_ORDER,
                'POSTAL_ORDER',
                'reversal',
                [
                    'customer_reference' => 99,
                    'customer_name' => 'some organisation',
                    'customer_manager_name' => 'some organisation',
                    'customer_address' => [
                        'line_1' => 'Foo',
                        'line_2' => null,
                        'line_3' => null,
                        'line_4' => null,
                        'city' => 'Bar',
                        'postcode' => 'LS9 6NF',
                    ],
                ],
                []
            ],
            'card digital' => [
                FeeEntity::METHOD_CARD_ONLINE,
                'CHARGE_BACK',
                'chargeback',
                [
                    'customer_reference' => 99,
                    'customer_name' => 'some organisation',
                    'customer_manager_name' => 'some organisation',
                    'customer_address' => [
                        'line_1' => 'Foo',
                        'line_2' => null,
                        'line_3' => null,
                        'line_4' => null,
                        'city' => 'Bar',
                        'postcode' => 'LS9 6NF',
                    ],
                ],
                []
            ],
            'card assisted digital' => [
                FeeEntity::METHOD_CARD_OFFLINE,
                'CHARGE_BACK',
                'chargeback',
                [
                    'customer_reference' => 99,
                    'customer_name' => 'some organisation',
                    'customer_manager_name' => 'some organisation',
                    'customer_address' => [
                        'line_1' => 'Foo',
                        'line_2' => null,
                        'line_3' => null,
                        'line_4' => null,
                        'city' => 'Bar',
                        'postcode' => 'LS9 6NF',
                    ],
                ],
                []
            ],
        ];
    }

    /**
     * Helper function to generate a stub fee entity
     *
     * @param int    $id
     * @param string $amount
     * @param string $accrualRule
     * @param string $licenceStartDate
     *
     * @return FeeEntity
     */
    private function getStubFee(
        mixed $id,
        mixed $amount,
        mixed $accrualRule = null,
        mixed $licenceStartDate = null,
        mixed $organisationId = null,
        mixed $invoicedDate = null,
        mixed $feeTypeId = null,
        string $licNo = 'OB1234567'
    ): FeeEntity {
        $status = new RefData();
        $rule = new RefData();
        if ($accrualRule) {
            $rule->setId($accrualRule);
        }
        $feeType = new FeeTypeEntity();
        $feeType
            ->setAccrualRule($rule)
            ->setDescription('fee type description')
            ->setFeeType((new RefData($feeTypeId)))
            ->setCostCentreRef('TA')
            ->setVatCode('Z')
            ->setProductReference('TEST_PRODUCT_REF')
            ->setIsNi('N');

        $organisation = new OrganisationEntity();
        $organisation
            ->setId($organisationId)
            ->setName('some organisation');

        $address = new AddressEntity();
        $address->updateAddress('Foo', null, null, null, 'Bar', 'LS9 6NF', null);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence
            ->setOrganisation($organisation)
            ->setLicNo($licNo)
            ->setCorrespondenceCd(
                m::mock()
                    ->shouldReceive('getAddress')
                    ->andReturn($address)
                    ->getMock()
            );
        if (!is_null($licenceStartDate)) {
            $licence->setInForceDate($licenceStartDate);
        }
        $licence->shouldReceive('getTrafficArea->getSalesPersonReference')->andReturn('Traffic Area Ref');

        $fee = new FeeEntity($feeType, $amount, $status);
        $fee
            ->setId($id)
            ->setInvoiceLineNo(1)
            ->setInvoicedDate($invoicedDate)
            ->setLicence($licence);

        return $fee;
    }

    public function testGetReportList(): void
    {
        $response = ['stuff'];

        $params = [
            'filters' => [
                'scheme' => [
                    'client_id',
                    'client_id_ni'
                ]
            ]
        ];
        $this->cpmsClient
            ->shouldReceive('get')
            ->with('/api/report', 'REPORT', $params)
            ->once()
            ->andReturn($response);

        $this->mockSchemaIdChange();

        $result = $this->sut->getReportList();

        $this->assertSame($response, $result);
    }

    public function testRequestReport(): void
    {
        $code = 'ABC123';
        $start = new \DateTime('2015-10-07 08:57:00');
        $end = new \DateTime('2015-10-08 08:56:59');

        $expectedParams = [
            'report_code' => $code,
            'filters' => [
                'from' => '2015-10-07 08:57:00',
                'to' => '2015-10-08 08:56:59',
                'scheme' => [
                    'client_id',
                    'client_id_ni'
                ]
            ],
        ];

        $this->mockSchemaIdChange();

        $response = ['stuff'];

        $this->cpmsClient
            ->shouldReceive('post')
            ->with('/api/report', 'REPORT', $expectedParams)
            ->once()
            ->andReturn($response);

        $result = $this->sut->requestReport($code, $start, $end);

        $this->assertSame($response, $result);
    }

    public function testGetReportStatus(): void
    {
        $reference = 'OLCS-1234-FOO';

        $response = ['stuff'];

        $params = [
            'filters' => [
                'scheme' => [
                    'client_id',
                    'client_id_ni'
                ]
            ]
        ];
        $this->cpmsClient
            ->shouldReceive('get')
            ->with('/api/report/OLCS-1234-FOO/status', 'REPORT', $params)
            ->once()
            ->andReturn($response);

        $this->mockSchemaIdChange();

        $result = $this->sut->getReportStatus($reference);

        $this->assertSame($response, $result);
    }

    public function testDownloadReport(): void
    {
        $reference = 'OLCS-1234-FOO';
        $token = 'secretsquirrel';

        $response = "foo,bar,csv,data";

        $params = [
            'filters' => [
                'scheme' => [
                    'client_id',
                    'client_id_ni'
                ]
            ]
        ];
        $this->cpmsClient
            ->shouldReceive('get')
            ->with('/api/report/OLCS-1234-FOO/download?token=secretsquirrel', 'REPORT', $params)
            ->once()
            ->andReturn($response);

        $this->mockSchemaIdChange();

        $result = $this->sut->downloadReport($reference, $token);

        $this->assertSame($response, $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('miscParamsProvider')]
    public function testRefundFeeSinglePayment(mixed $miscParams, mixed $expectedCustomer, mixed $expectedReceiver): void
    {
        $isMiscellaneous = count($miscParams) > 0 ? true : false;
        $fee = m::mock(FeeEntity::class);

        $this->mockSchemaIdChange(false, 2);

        $ft = m::mock(FeeTransactionEntity::class);
        $ft->shouldReceive('getAmount')->andReturn('100.00');
        $ft->shouldReceive('getTransaction')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getPaymentMethod')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn(FeeEntity::METHOD_CARD_ONLINE)
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('getReference')
                    ->andReturn('payment_ref')
                    ->times(3)
                    ->shouldReceive('getCpmsSchema')
                    ->andReturn('client_id')
                    ->once()
                    ->getMock()
            )
            ->getMock();

        $address = new Address();
        $address->updateAddress('Foo', null, null, null, 'Bar', 'LS9 6NF');
        $customerReference = 99;
        if (array_key_exists('customer_reference', $miscParams)) {
            $customerReference = $miscParams['customer_reference'];
        }
        $fee
            ->shouldReceive('getFeeTransactionsForRefund')
            ->andReturn([$ft])
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(99)
                    ->getMock()
            )
            ->shouldReceive('getId')
            ->andReturn(101)
            ->shouldReceive('isBalancingFee')
            ->andReturn(false)
            ->shouldReceive('getInvoiceLineNo')
            ->andReturn('LINE_NO')
            ->shouldReceive('getAmount')
            ->andReturn('200.00')
            ->shouldReceive('getOutstandingAmount')
            ->andReturn('0.00')
            ->shouldReceive('getInvoicedDate')
            ->andReturn(new \DateTime('2015-10-09'))
            ->shouldReceive('getCustomerNameForInvoice')
            ->andReturn('some organisation')
            ->shouldReceive('getCustomerAddressForInvoice')
            ->andReturn($address)
            ->shouldReceive('getRuleStartDate')
            ->andReturn(new \DateTime('2015-10-12'))
            ->shouldReceive('getDefermentPeriod')
            ->andReturn('1')
            ->shouldReceive('getSalesPersonReference')
            ->andReturn('TEST_SALES_PERSON_REF')
            ->shouldReceive('getNetAmount')
            ->andReturn('12.00')
            ->shouldReceive('getVatAmount')
            ->andReturn('15.00')
            ->shouldReceive('getGrossAmount')
            ->andReturn('9.99')
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getLicNo')
                    ->andReturn('OB1234567')
                    ->getMock()
            )
            ->shouldReceive('getCustomerReference')
            ->andReturn($customerReference);

        $fee->shouldReceive('getFeeType->getDescription')->andReturn('TEST_FEE_TYPE');
        $fee->shouldReceive('getFeeType->getVatCode')->andReturn('VAT_CODE');
        $fee->shouldReceive('getFeeType->getVatRate')->andReturn('VAT_RATE');
        $fee->shouldReceive('getFeeType->getCountryCode')->andReturn('NI');
        $fee->shouldReceive('getFeeType->isMiscellaneous')->andReturn($isMiscellaneous);
        $fee->shouldReceive('getFeeType->getIrfoFeeType')->andReturnNull();
        $fee->shouldReceive('getFeeType->getFeeType->getId')->andReturn('APP');
        $fee->shouldReceive('getFeeType->getIsNi')->andReturn('N');

        $ft->shouldReceive('getFee')->andReturn($fee);

        $params = array_merge(
            $expectedCustomer,
            [
                'receipt_reference' => 'payment_ref',
                'refund_reason' => 'Refund',
                'payment_data' => [
                    array_merge(
                        $expectedReceiver,
                        [
                            'line_identifier' => '101',
                            'amount' => '100.00',
                            'allocated_amount' => '0.00',
                            'net_amount' => '12.00',
                            'tax_amount' => '15.00',
                            'tax_code' => 'VAT_CODE',
                            'tax_rate' => 'VAT_RATE',
                            'invoice_date' => '2015-10-09',
                            'sales_reference' => '101',
                            'product_reference' => 'TEST_FEE_TYPE',
                            'product_description' => 'TEST_FEE_TYPE',
                            'rule_start_date' => '2015-10-12',
                            'deferment_period' => '1',
                            'country_code' => 'NI',
                            'sales_person_reference' => 'TEST_SALES_PERSON_REF',
                        ]
                    )
                ],
                'scope' => 'REFUND',
                'total_amount' => '100.00',
                'country_code' => 'NI',
                'auth_code' => '123'
            ]
        );

        $response = [
            'code' => CpmsV2HelperService::PAYMENT_REFUNDED,
            'receipt_reference' => 'RECEIPT_REF',
        ];

        $this->cpmsClient
            ->shouldReceive('post')
            ->with('/api/payment/payment_ref/refund', 'REFUND', $params)
            ->once()
            ->andReturn($response)
            ->shouldReceive('get')
            ->with('/api/payment/payment_ref/auth-code', 'QUERY_TXN', [])
            ->andReturn(
                ['auth_code' => '123']
            )
            ->once()
            ->getMock();

        $result = $this->sut->refundFee($fee, $miscParams);

        $this->assertSame(['payment_ref' => 'RECEIPT_REF'], $result);
    }

    public function testRefundFeeSinglePaymentErrorResponse(): void
    {
        $this->expectException(CpmsResponseException::class);

        $fee = m::mock(FeeEntity::class);

        $this->mockSchemaIdChange(false, 2);

        $ft = m::mock(FeeTransactionEntity::class);
        $ft->shouldReceive('getAmount')->andReturn('100.00');
        $ft->shouldReceive('getTransaction')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getPaymentMethod')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn(FeeEntity::METHOD_CARD_ONLINE)
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('getReference')
                    ->andReturn('payment_ref')
                    ->twice(2)
                    ->shouldReceive('getCpmsSchema')
                    ->once()
                    ->andReturn(null)
                    ->getMock()
            )
            ->getMock();

        $address = new Address();
        $address->updateAddress('ADDR1', 'ADDR2', 'ADDR3', 'ADDR4', 'TOWN', 'POSTCODE');

        $mockFeeType = m::mock()
            ->shouldReceive('getDescription')
            ->andReturn('TEST_FEE_TYPE')
            ->twice()
            ->shouldReceive('getVatCode')
            ->andReturn('VAT_CODE')
            ->once()
            ->shouldReceive('getVatRate')
            ->andReturn('VAT_RATE')
            ->once()
            ->shouldReceive('getCountryCode')
            ->andReturn('NI')
            ->times(3)
            ->shouldReceive('isMiscellaneous')
            ->andReturn(false)
            ->once()
            ->shouldReceive('getIrfoFeeType')
            ->andReturnNull()
            ->once()
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn('APP')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getIsNi')
            ->andReturn('N')
            ->twice()
            ->getMock();

        $fee
            ->shouldReceive('getFeeTransactionsForRefund')
            ->andReturn([$ft])
            ->shouldReceive('getOrganisation')
            ->shouldReceive('getId')
            ->andReturn(101)
            ->shouldReceive('isBalancingFee')
            ->andReturn(false)
            ->shouldReceive('getInvoiceLineNo')
            ->andReturn('LINE_NO')
            ->shouldReceive('getAmount')
            ->andReturn('200.00')
            ->shouldReceive('getOutstandingAmount')
            ->andReturn('0.00')
            ->shouldReceive('getInvoicedDate')
            ->andReturn(new \DateTime('2015-10-09'))
            ->shouldReceive('getCustomerNameForInvoice')
            ->andReturn('Test Customer')
            ->shouldReceive('getCustomerAddressForInvoice')
            ->andReturn($address)
            ->shouldReceive('getRuleStartDate')
            ->andReturn(new \DateTime('2015-10-12'))
            ->shouldReceive('getDefermentPeriod')
            ->andReturn('1')
            ->shouldReceive('getSalesPersonReference')
            ->andReturn('TEST_SALES_PERSON_REF')
            ->shouldReceive('getNetAmount')
            ->andReturn('12.00')
            ->shouldReceive('getVatAmount')
            ->andReturn('15.00')
            ->shouldReceive('getGrossAmount')
            ->andReturn('9.99')
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getLicNo')
                    ->andReturn('OB1234567')
                    ->getMock()
            )
            ->shouldReceive('getFeeType')
            ->andReturn($mockFeeType)
            ->getMock()
            ->shouldReceive('getCustomerReference')
            ->andReturn(99);

        $ft->shouldReceive('getFee')->andReturn($fee);

        $this->cpmsClient
            ->shouldReceive('post')
            ->with('/api/payment/payment_ref/refund', 'REFUND', m::any())
            ->andReturn([])
            ->shouldReceive('get')
            ->with('/api/payment/payment_ref/auth-code', 'QUERY_TXN', [])
            ->andReturn(
                ['auth_code' => '123']
            )
            ->once()
            ->getMock();

        $this->cpmsClient
            ->shouldReceive('getClient->getHttpClient->getResponse->getStatusCode')
            ->andReturn(401);

        $this->sut->refundFee($fee);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('miscParamsProvider')]
    public function testRefundFeeMultiplePayments(mixed $miscParams, mixed $expectedCustomer, mixed $expectedReceiver): void
    {
        $isMiscellaneous = count($miscParams) > 0 ? true : false;
        $fee = m::mock(FeeEntity::class);

        $this->mockSchemaIdChange();

        $txn = m::mock(TransactionEntity::class);
        $txn->shouldReceive('getReference')
            ->andReturn('payment_ref');
        $txn->shouldReceive('getCpmsSchema')
            ->andReturn('client_id');

        $ft = m::mock(FeeTransactionEntity::class);
        $ft
            ->shouldReceive('getTransaction')
            ->andReturn($txn);

        $ft
            ->shouldReceive('getAmount')
            ->andReturn('100.00');

        $ft2 = m::mock(FeeTransactionEntity::class);
        $ft2
            ->shouldReceive('getTransaction')
            ->andReturn($txn);
        $ft2
            ->shouldReceive('getAmount')
            ->andReturn('201.00');

        $address = new Address();
        $address->updateAddress('Foo', null, null, null, 'Bar', 'LS9 6NF');

        $mockFeeType = m::mock(FeeType::class);

        $mockFeeType->shouldReceive('getDescription')
            ->andReturn('TEST_FEE_TYPE')
            ->times(4);

        $mockFeeType->shouldReceive('getVatCode')
            ->andReturn('VAT_CODE')
            ->twice();

        $mockFeeType
            ->shouldReceive('getVatRate')
            ->andReturn('VAT_RATE')
            ->twice();

        $mockFeeType
            ->shouldReceive('getCountryCode')
            ->andReturn('NI')
            ->times(5);

        if (!$isMiscellaneous) {
            $mockFeeType
                ->shouldReceive('isMiscellaneous')
                ->andReturn($isMiscellaneous)
                ->twice()
                ->shouldReceive('getIrfoFeeType')
                ->andReturnNull()
                ->twice()
                ->shouldReceive('getFeeType')
                ->andReturn(
                    m::mock()
                        ->shouldReceive('getId')
                        ->andReturn('APP')
                        ->twice()
                        ->getMock()
                )
                ->twice()
                ->getMock();
        }
        $customerReference = 99;
        if (array_key_exists('customer_reference', $miscParams)) {
            $customerReference = $miscParams['customer_reference'];
        }
        $fee
            ->shouldReceive('getFeeTransactionsForRefund')
            ->andReturn([$ft, $ft2])
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(99)
                    ->getMock()
            )
            ->shouldReceive('getId')
            ->andReturn(101)
            ->shouldReceive('isBalancingFee')
            ->andReturn(false)
            ->shouldReceive('getInvoiceLineNo')
            ->andReturn('LINE_NO')
            ->shouldReceive('getAmount')
            ->andReturn('200.00')
            ->shouldReceive('getOutstandingAmount')
            ->andReturn('0.00')
            ->shouldReceive('getInvoicedDate')
            ->andReturn(new \DateTime('2015-10-09'))
            ->shouldReceive('getCustomerNameForInvoice')
            ->andReturn('some organisation')
            ->shouldReceive('getCustomerAddressForInvoice')
            ->andReturn($address)
            ->shouldReceive('getRuleStartDate')
            ->andReturn(new \DateTime('2015-10-12'))
            ->shouldReceive('getDefermentPeriod')
            ->andReturn('1')
            ->shouldReceive('getSalesPersonReference')
            ->andReturn('TEST_SALES_PERSON_REF')
            ->shouldReceive('getNetAmount')
            ->andReturn('12.00')
            ->shouldReceive('getVatAmount')
            ->andReturn('15.00')
            ->shouldReceive('getGrossAmount')
            ->andReturn('9.99')
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getLicNo')
                    ->andReturn('OB1234567')
                    ->getMock()
            )
            ->shouldReceive('getFeeType')
            ->andReturn($mockFeeType)
            ->shouldReceive('getCustomerReference')
            ->andReturn($customerReference)
            ->getMock();

        $ft->shouldReceive('getFee')->andReturn($fee);
        $ft2->shouldReceive('getFee')->andReturn($fee);

        $params = array_merge(
            $expectedCustomer,
            [
                'scope' => 'REFUND',
                'country_code' => 'NI',
                'payments' => [
                    [
                        'receipt_reference' => 'payment_ref',
                        'refund_reason' => 'Refund',
                        'country_code' => 'NI',
                        'total_amount' => '100.00',
                        'payment_data' => [
                            array_merge(
                                $expectedReceiver,
                                [
                                    'line_identifier' => '101',
                                    'amount' => '100.00',
                                    'allocated_amount' => '0.00',
                                    'net_amount' => '12.00',
                                    'tax_amount' => '15.00',
                                    'tax_code' => 'VAT_CODE',
                                    'tax_rate' => 'VAT_RATE',
                                    'invoice_date' => '2015-10-09',
                                    'sales_reference' => '101',
                                    'product_reference' => 'TEST_FEE_TYPE',
                                    'product_description' => 'TEST_FEE_TYPE',
                                    'rule_start_date' => '2015-10-12',
                                    'deferment_period' => '1',
                                    'country_code' => 'NI',
                                    'sales_person_reference' => 'TEST_SALES_PERSON_REF',
                                ]
                            ),
                        ],
                    ],
                    [
                        'receipt_reference' => 'payment_ref',
                        'refund_reason' => 'Refund',
                        'country_code' => 'NI',
                        'total_amount' => '201.00',
                        'payment_data' => [
                            array_merge(
                                $expectedReceiver,
                                [
                                    'line_identifier' => '101',
                                    'amount' => '201.00',
                                    'allocated_amount' => '0.00',
                                    'net_amount' => '12.00',
                                    'tax_amount' => '15.00',
                                    'tax_code' => 'VAT_CODE',
                                    'tax_rate' => 'VAT_RATE',
                                    'invoice_date' => '2015-10-09',
                                    'sales_reference' => '101',
                                    'product_reference' => 'TEST_FEE_TYPE',
                                    'product_description' => 'TEST_FEE_TYPE',
                                    'rule_start_date' => '2015-10-12',
                                    'deferment_period' => '1',
                                    'country_code' => 'NI',
                                    'sales_person_reference' => 'TEST_SALES_PERSON_REF',
                                ]
                            )
                        ],
                    ],
                ],
            ]
        );

        $response = [
            'code' => CpmsV2HelperService::RESPONSE_SUCCESS,
            'receipt_references' => [
                'foo' => 'bar',
                'baz' => 'bat',
            ],
        ];

        $this->cpmsClient
            ->shouldReceive('post')
            ->with('/api/refund', 'REFUND', $params)
            ->once()
            ->andReturn($response);

        $result = $this->sut->refundFee($fee, $miscParams);

        $this->assertSame($response['receipt_references'], $result);
    }

    public function testBatchRefundWithInvalidResponse(): void
    {
        $this->expectException(CpmsResponseException::class);
        $this->mockSchemaIdChange();

        $fee = m::mock(FeeEntity::class);
        $fee
            ->shouldReceive('getFeeTransactionsForRefund')
            ->andReturn([])
            ->shouldReceive('getOrganisation')
            ->shouldReceive('getCustomerNameForInvoice')
            ->andReturn('foo')
            ->twice()
            ->shouldReceive('getCustomerAddressForInvoice')
            ->andReturn(
                [
                    'addressLine1' => 'line1',
                    'addressLine2' => 'line2',
                    'addressLine3' => 'line3',
                    'addressLine4' => 'line4',
                    'town' => 'town',
                    'postcode' => 'postcode'
                ]
            )
            ->once()
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getCountryCode')
                    ->andReturn('GB')
                    ->once()
                    ->shouldReceive('getIsNi')
                    ->andReturn('N')
                    ->once()
                    ->getMock()
            )
            ->twice()
            ->getMock()
            ->shouldReceive('getCustomerReference')
            ->andReturn(99);

        $this->cpmsClient
            ->shouldReceive('post')
            ->with('/api/refund', 'REFUND', m::any())
            ->andReturn([]);

        $this->cpmsClient
            ->shouldReceive('getClient->getHttpClient->getResponse->getStatusCode')
            ->andReturn(401);

        $this->sut->batchRefund($fee);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('miscParamsProvider')]
    public function testBatchRefundWithServiceException(mixed $miscParams, mixed $expectedCustomer, mixed $expectedReceiver): void
    {

        $isMiscellaneous = count($miscParams) > 0 ? true : false;
        $fee = m::mock(FeeEntity::class);

        $txn = m::mock(TransactionEntity::class);
        $txn->shouldReceive('getReference')
            ->andReturn('payment_ref');
        $txn->shouldReceive('getCpmsSchema')
            ->andReturn('client_id');

        $txn2 = m::mock(TransactionEntity::class);
        $txn2->shouldReceive('getReference')
            ->andReturn('payment_ref');
        $txn2->shouldReceive('getCpmsSchema')
            ->andReturn('client_id_2');

        $ft = m::mock(FeeTransactionEntity::class);
        $ft
            ->shouldReceive('getTransaction')
            ->andReturn($txn);
        $ft
            ->shouldReceive('getAmount')
            ->andReturn('100.00');

        $ft2 = m::mock(FeeTransactionEntity::class);
        $ft2
            ->shouldReceive('getTransaction')
            ->andReturn($txn2);
        $ft2
            ->shouldReceive('getAmount')
            ->andReturn('201.00');

        $address = new Address();
        $address->updateAddress('Foo', null, null, null, 'Bar', 'LS9 6NF');

        $mockFeeType = m::mock()
            ->shouldReceive('getDescription')
            ->andReturn('TEST_FEE_TYPE')
            ->times(4)
            ->shouldReceive('getVatCode')
            ->andReturn('VAT_CODE')
            ->twice()
            ->shouldReceive('getVatRate')
            ->andReturn('VAT_RATE')
            ->twice()
            ->shouldReceive('getCountryCode')
            ->andReturn('NI')
            ->times(4)
            ->getMock();

        if (!$isMiscellaneous) {
            $mockFeeType
                ->shouldReceive('isMiscellaneous')
                ->andReturn($isMiscellaneous)
                ->twice()
                ->shouldReceive('getIrfoFeeType')
                ->andReturnNull()
                ->twice()
                ->shouldReceive('getFeeType')
                ->andReturn(
                    m::mock()
                        ->shouldReceive('getId')
                        ->andReturn('APP')
                        ->twice()
                        ->getMock()
                )
                ->twice()
                ->getMock();
        }

        $fee
            ->shouldReceive('getFeeTransactionsForRefund')
            ->andReturn([$ft, $ft2])
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(99)
                    ->getMock()
            )
            ->shouldReceive('getId')
            ->andReturn(101)
            ->shouldReceive('isBalancingFee')
            ->andReturn(false)
            ->shouldReceive('getInvoiceLineNo')
            ->andReturn('LINE_NO')
            ->shouldReceive('getAmount')
            ->andReturn('200.00')
            ->shouldReceive('getOutstandingAmount')
            ->andReturn('0.00')
            ->shouldReceive('getInvoicedDate')
            ->andReturn(new \DateTime('2015-10-09'))
            ->shouldReceive('getCustomerNameForInvoice')
            ->andReturn('some organisation')
            ->shouldReceive('getCustomerAddressForInvoice')
            ->andReturn($address)
            ->shouldReceive('getRuleStartDate')
            ->andReturn(new \DateTime('2015-10-12'))
            ->shouldReceive('getDefermentPeriod')
            ->andReturn('1')
            ->shouldReceive('getSalesPersonReference')
            ->andReturn('TEST_SALES_PERSON_REF')
            ->shouldReceive('getNetAmount')
            ->andReturn('12.00')
            ->shouldReceive('getVatAmount')
            ->andReturn('15.00')
            ->shouldReceive('getGrossAmount')
            ->andReturn('9.99')
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getLicNo')
                    ->andReturn('OB1234567')
                    ->getMock()
            )
            ->shouldReceive('getFeeType')
            ->andReturn($mockFeeType)
            ->getMock();

        $ft->shouldReceive('getFee')->andReturn($fee);
        $ft2->shouldReceive('getFee')->andReturn($fee);

        $response = [
            'code' => CpmsV2HelperService::RESPONSE_SUCCESS,
            'receipt_references' => [
                'foo' => 'bar',
                'baz' => 'bat',
            ],
        ];

        $this->expectException(CpmsV2HelperServiceException::class);
        $result = $this->sut->refundFee($fee, $miscParams);
        $this->assertSame($response['receipt_references'], $result);
    }

    public function testCreateServiceWithInvoicePrefix(): void
    {
        $sut = $this->createService(
            null,
            null,
            [
                'cpms' => ['invoice_prefix' => 'PREFIX'],
                'cpms_api' => ['identity_provider' => 'identity'],
            ]
        );

        $this->assertSame('PREFIX', $sut->getInvoicePrefix());
    }

    public function testGetSetInvoicePrefix(): void
    {
        $this->assertSame(null, $this->sut->getInvoicePrefix());
        $this->sut->setInvoicePrefix('PREFIX');
        $this->assertSame('PREFIX', $this->sut->getInvoicePrefix());
    }

    public function testSetInvoicePrefixTooLong(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->sut->setInvoicePrefix('TOO_LONGX');
    }

    public function testFormatAddressNull(): void
    {
        $sut = m::mock(\Dvsa\Olcs\Api\Service\CpmsV2HelperService::class)->makePartial();

        $this->assertNull($sut->formatAddress(null));
    }

    public function testValidateReceiverParamsWithException(): void
    {
        $sut = m::mock(\Dvsa\Olcs\Api\Service\CpmsV2HelperService::class)->makePartial();

        $this->expectException(ValidationException::class);
        $sut->validateReceiverParams();
    }

    public function testValidateCustomerParamsWithException(): void
    {
        $sut = m::mock(\Dvsa\Olcs\Api\Service\CpmsV2HelperService::class)->makePartial();

        $this->expectException(ValidationException::class);
        $sut->validateCustomerParams();
    }

    public function testGetReceiverReferenceMiscFee(): void
    {
        $mockFee = m::mock()
            ->shouldReceive('getLicence')
            ->andReturnNull()
            ->once()
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isMiscellaneous')
                    ->andReturn(true)
                    ->once()
                    ->getMock()
            )
            ->shouldReceive('getOrganisation')
            ->andReturnNull()
            ->getMock();

        $sut = m::mock(\Dvsa\Olcs\Api\Service\CpmsV2HelperService::class)->makePartial();

        $this->assertNull($sut->getReceiverReference($mockFee));
    }

    public function testGetReceiverReferenceIrfoFee(): void
    {
        $mockFee = m::mock()
            ->shouldReceive('getLicence')
            ->andReturnNull()
            ->once()
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isMiscellaneous')
                    ->andReturn(false)
                    ->once()
                    ->shouldReceive('getIrfoFeeType')
                    ->andReturn('FOO')
                    ->once()
                    ->getMock()
            )
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(1)
                    ->once()
                    ->getMock()
            )
            ->getMock();

        $sut = m::mock(\Dvsa\Olcs\Api\Service\CpmsV2HelperService::class)->makePartial();

        $this->assertEquals('IR0000001', $sut->getReceiverReference($mockFee));
    }

    public function testGetReceiverReferenceBusFee(): void
    {
        $mockFee = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getLicNo')
                    ->andReturn('OB1234567')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isMiscellaneous')
                    ->andReturn(false)
                    ->once()
                    ->shouldReceive('getIrfoFeeType')
                    ->andReturnNull()
                    ->once()
                    ->shouldReceive('getFeeType')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn(FeeTypeEntity::FEE_TYPE_BUSAPP)
                            ->once()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('getOrganisation')
            ->andReturnNull()
            ->getMock();

        $sut = m::mock(\Dvsa\Olcs\Api\Service\CpmsV2HelperService::class)->makePartial();

        $this->assertEquals('OB1234567B', $sut->getReceiverReference($mockFee));
    }

    public function testGetReceiverReferenceLicenceFee(): void
    {
        $mockFee = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getLicNo')
                    ->andReturn('OB1234567')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isMiscellaneous')
                    ->andReturn(false)
                    ->once()
                    ->shouldReceive('getIrfoFeeType')
                    ->andReturnNull()
                    ->once()
                    ->shouldReceive('getFeeType')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn('FOO')
                            ->once()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('getOrganisation')
            ->andReturnNull()
            ->getMock();

        $sut = m::mock(\Dvsa\Olcs\Api\Service\CpmsV2HelperService::class)->makePartial();

        $this->assertEquals('OB1234567', $sut->getReceiverReference($mockFee));
    }

    public function testGetReceiverReferenceOrgFee(): void
    {
        $mockFee = m::mock()
            ->shouldReceive('getLicence')
            ->andReturnNull()
            ->once()
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isMiscellaneous')
                    ->andReturn(false)
                    ->once()
                    ->shouldReceive('getIrfoFeeType')
                    ->andReturnNull()
                    ->once()
                    ->shouldReceive('getFeeType')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn('FOO')
                            ->once()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(99)
                    ->once()
                    ->getMock()
            )
            ->getMock();

        $sut = m::mock(\Dvsa\Olcs\Api\Service\CpmsV2HelperService::class)->makePartial();

        $this->assertEquals(99, $sut->getReceiverReference($mockFee));
    }

    public function testGetReceiverReferenceNoFee(): void
    {
        $mockFee = m::mock()
            ->shouldReceive('getLicence')
            ->andReturnNull()
            ->once()
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isMiscellaneous')
                    ->andReturn(false)
                    ->once()
                    ->shouldReceive('getIrfoFeeType')
                    ->andReturnNull()
                    ->once()
                    ->shouldReceive('getFeeType')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn('FOO')
                            ->once()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('getOrganisation')
            ->andReturnNull()
            ->getMock();

        $sut = m::mock(\Dvsa\Olcs\Api\Service\CpmsV2HelperService::class)->makePartial();

        $this->assertNull($sut->getReceiverReference($mockFee));
    }

    public function testFormatAddressArray(): void
    {
        $sut = m::mock(\Dvsa\Olcs\Api\Service\CpmsV2HelperService::class)->makePartial();

        $expected = [
            'line_1' => 'line1',
            'line_2' => 'line2',
            'line_3' => 'line3',
            'line_4' => 'line4',
            'city' => 'city',
            'postcode' => ' ',
        ];
        $address = [
            'addressLine1' => 'line1',
            'addressLine2' => 'line2',
            'addressLine3' => 'line3',
            'addressLine4' => 'line4',
            'town' => 'city',
            'postcode' => null,
        ];

        $this->assertEquals($sut->formatAddress($address), $expected);
    }

    public function testFormatAddressObject(): void
    {
        $sut = m::mock(\Dvsa\Olcs\Api\Service\CpmsV2HelperService::class)->makePartial();

        $expected = [
            'line_1' => 'line1',
            'line_2' => 'line2',
            'line_3' => 'line3',
            'line_4' => 'line4',
            'city' => 'city',
            'postcode' => ' ',
        ];
        $address = new AddressEntity();
        $address->setAddressLine1('line1');
        $address->setAddressLine2('line2');
        $address->setAddressLine3('line3');
        $address->setAddressLine4('line4');
        $address->setTown('city');
        $address->setPostcode(null);

        $this->assertEquals($sut->formatAddress($address), $expected);
    }

    protected function mockSchemaIdChange(bool $isNi = false, int $times = 1): void
    {
        $this->identity
            ->shouldReceive('setClientId')
            ->with('client_id' . ($isNi ? '_ni' : ''))
            ->times($times)
            ->shouldReceive('setClientSecret')
            ->with('client_secret' . ($isNi ? '_ni' : ''))
            ->times($times)
            ->getMock();

        $this->cpmsClient
            ->shouldReceive('getIdentity')
            ->andReturn($this->identity)
            ->getMock();
    }
}
