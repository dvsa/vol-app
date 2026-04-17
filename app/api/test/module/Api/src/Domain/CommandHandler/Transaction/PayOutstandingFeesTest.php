<?php

declare(strict_types=1);

/**
 * Pay Outstanding Fees Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Transaction;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateOverpaymentFee as CreateOverpaymentFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Fee\PayFee as PayFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Transaction\ResolvePayment as ResolvePaymentCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\Transaction\PayOutstandingFees;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction as FeePaymentEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as PaymentEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Service\CpmsHelperInterface as CpmsHelper;
use Dvsa\Olcs\Api\Service\Exception as ServiceException;
use Dvsa\Olcs\Api\Service\FeesHelperService as FeesHelper;
use Dvsa\Olcs\Transfer\Command\Fee\RejectWaive as RejectWaiveCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Pay Outstanding Fees Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PayOutstandingFeesTest extends AbstractCommandHandlerTestCase
{
    protected $mockCpmsService;

    protected $mockFeesHelperService;

    private $cardResponse;

    public function setUp(): void
    {
        $this->cardResponse = [
            'schema_id' => 'id',
            'receipt_reference' => 'reference',
            'gateway_url' => 'url',
        ];
        $this->mockCpmsService = m::mock(CpmsHelper::class);

        $this->mockFeesHelperService = m::mock(FeesHelper::class);
        $this->mockedSmServices = [
            'CpmsHelperService' => $this->mockCpmsService,
            'FeesHelperService' => $this->mockFeesHelperService,
            AuthorizationService::class => m::mock(AuthorizationService::class)->makePartial(),
            'config' => [],
        ];

        $this->sut = new PayOutstandingFees();
        $this->mockRepo('Fee', Repository\Fee::class);
        $this->mockRepo('FeeType', Repository\FeeType::class);
        $this->mockRepo('Transaction', Repository\Transaction::class);
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('SystemParameter', Repository\SystemParameter::class);
        $this->mockRepo('Task', Repository\Task::class);

        /** @var UserEntity $mockUser */
        $mockUser = m::mock(UserEntity::class)
            ->shouldReceive('getLoginId')
            ->andReturn('bob')
            ->shouldReceive('getId')
            ->andReturn(1)
            ->shouldReceive('getTeam')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(2)
                ->getMock()
            )
            ->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            PaymentEntity::STATUS_OUTSTANDING,
            PaymentEntity::STATUS_PAID,
            PaymentEntity::STATUS_FAILED,
            PaymentEntity::TYPE_PAYMENT,
            FeeEntity::METHOD_CARD_ONLINE,
            FeeEntity::METHOD_CASH => m::mock(RefData::class)->makePartial()->setDescription('Cash'),
            FeeEntity::METHOD_CHEQUE => m::mock(RefData::class)->makePartial()->setDescription('Cheque'),
            FeeEntity::METHOD_POSTAL_ORDER => m::mock(RefData::class)->makePartial()->setDescription('Postal Order'),
            FeeEntity::METHOD_WAIVE,
            FeeEntity::STATUS_PAID,
            LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
            LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
        ];

        $this->references = [
            OrganisationEntity::class => [
                77 => m::mock(OrganisationEntity::class),
            ],
            LicenceEntity::class => [
                7 => m::mock(LicenceEntity::class),
            ],
            ApplicationEntity::class => [
                69 => m::mock(ApplicationEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithOrgId(): void
    {
        // set up data
        $organisationId = 69;
        $feeIds = [99, 100, 101];
        $cpmsRedirectUrl = 'https://olcs-selfserve/foo';

        $paymentId = 999; // payment to be created

        $fees = [
            $this->getStubFee(99, 99.99),
            $this->getStubFee(101, 99.99),
        ];

        $data = [
            'feeIds' => $feeIds,
            'organisationId' => $organisationId,
            'cpmsRedirectUrl' => $cpmsRedirectUrl,
            'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
            'customerName' => 'foo',
            'customerReference' => 'bar',
            'address' => 'cake',
        ];
        $extraParams = [
            'customer_name' => 'foo',
            'customer_reference' => 'bar',
            'customer_address' => 'cake'
        ];

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['SystemParameter']->shouldReceive('getDisableSelfServeCardPayments')->with()->once()
            ->andReturn(false);

        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByOrganisationId')
            ->once()
            ->with($organisationId)
            ->andReturn($fees)
            ->shouldReceive('fetchFeesByIds')
            ->with([99, 101])
            ->andReturn($fees)
            ->once()
            ->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($fees[0])
            ->once()
            ->shouldReceive('fetchById')
            ->with(101)
            ->andReturn($fees[1])
            ->once();

        $this->mockCpmsService
            ->shouldReceive('initiateCardRequest')
            ->once()
            ->with($cpmsRedirectUrl, $fees, $extraParams)
            ->andReturn($this->cardResponse);

        /** @var PaymentEntity $savedPayment */
        $savedPayment = null;
        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(PaymentEntity::class))
            ->andReturnUsing(
                function (PaymentEntity $payment) use (&$savedPayment, $paymentId) {
                    $payment->setId($paymentId);
                    $savedPayment = $payment;
                }
            );

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'transaction' => $paymentId,
            ],
            'messages' => [
                'Transaction record created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(PaymentEntity::STATUS_OUTSTANDING, $savedPayment->getStatus()->getId());
    }

    public function testHandleCommandNoOp(): void
    {
        // set up data
        $organisationId = 69;
        $feeIds = [99, 100, 101];
        $cpmsRedirectUrl = 'https://olcs-selfserve/foo';

        $fees = [];

        $data = [
            'feeIds' => $feeIds,
            'organisationId' => $organisationId,
            'cpmsRedirectUrl' => $cpmsRedirectUrl,
            'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
        ];

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['SystemParameter']->shouldReceive('getDisableSelfServeCardPayments')->with()->once()
            ->andReturn(false);

        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByOrganisationId')
            ->once()
            ->with($organisationId)
            ->andReturn($fees);

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                ['ERR_NO_FEES' => 'The fee(s) has already been paid'],
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandDisableCardPayments(): void
    {
        // set up data
        $organisationId = 69;
        $feeIds = [99, 100, 101];
        $cpmsRedirectUrl = 'https://olcs-selfserve/foo';

        $data = [
            'feeIds' => $feeIds,
            'organisationId' => $organisationId,
            'cpmsRedirectUrl' => $cpmsRedirectUrl,
            'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
        ];

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['SystemParameter']->shouldReceive('getDisableSelfServeCardPayments')->with()->once()
            ->andReturn(true);

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Card payments are disabled',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testResolvePaidFees(): void
    {
        $result = new Result();

        // set up fee with outstanding payment that was paid
        $paymentId = 222;
        $payment = new PaymentEntity();
        $payment
            ->setStatus($this->mapRefData(PaymentEntity::STATUS_OUTSTANDING))
            ->setId($paymentId)
            ->setPaymentMethod($this->mapRefData(FeeEntity::METHOD_CARD_ONLINE));
        $fp = new FeePaymentEntity();
        $fp->setTransaction($payment);
        $fee1 = $this->getStubFee(99, 150.00);
        $fee1->getFeeTransactions()->add($fp);

        $fees = [$fee1];

        $resolveResult = new Result();
        $resolveResult->addId('transaction', $paymentId);
        $this->expectedSideEffect(
            ResolvePaymentCommand::class,
            [
                'id' => $paymentId,
                'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
            ],
            $resolveResult
        );

        $updatedPayment = new PaymentEntity();
        $updatedPayment
            ->setId($paymentId)
            ->setStatus($this->mapRefData(PaymentEntity::STATUS_PAID));
        $this->repoMap['Transaction']
            ->shouldReceive('fetchById')
            ->once()
            ->with($paymentId)
            ->andReturn($updatedPayment);

        $this->sut->resolvePaidFees($fees, $result);
    }

    public function testResolvePaidFeesOutstandingPaymentUnpaid(): void
    {
        $result = new Result();

        // set up fee with outstanding payment that was not paid
        $paymentId = 222;
        $payment = new PaymentEntity();
        $payment
            ->setStatus($this->mapRefData(PaymentEntity::STATUS_OUTSTANDING))
            ->setId($paymentId)
            ->setPaymentMethod($this->mapRefData(FeeEntity::METHOD_CARD_ONLINE));
        $fp = new FeePaymentEntity();
        $fp->setTransaction($payment);
        $fee1 = $this->getStubFee(99, 150.00);
        $fee1->getFeeTransactions()->add($fp);

        $fees = [$fee1];

        $resolveResult = new Result();
        $resolveResult->addId('transaction', $paymentId);
        $this->expectedSideEffect(
            ResolvePaymentCommand::class,
            [
                'id' => $paymentId,
                'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
            ],
            $resolveResult
        );

        $updatedPayment = new PaymentEntity();
        $updatedPayment
            ->setId($paymentId)
            ->setStatus($this->mapRefData(PaymentEntity::STATUS_FAILED));
        $this->repoMap['Transaction']
            ->shouldReceive('fetchById')
            ->once()
            ->with($paymentId)
            ->andReturn($updatedPayment);

        $this->sut->resolvePaidFees($fees, $result);
    }

    public function testHandleCommandWithFeeIds(): void
    {
        // set up data
        $feeIds = [99, 100, 101];
        $cpmsRedirectUrl = 'https://olcs-selfserve/foo';

        $paymentId = 999; // payment to be created

        $fee1 = $this->getStubFee(99, 99.99);
        $fee2 = $this->getStubFee(101, 99.99);
        $fees = [$fee1, $fee2];

        $data = [
            'feeIds' => $feeIds,
            'cpmsRedirectUrl' => $cpmsRedirectUrl,
            'paymentMethod' => FeeEntity::METHOD_CARD_OFFLINE,
            'customerName' => 'foo',
            'customerReference' => 'bar',
            'address' => 'cake',
        ];
        $extraParams = [
            'customer_name' => 'foo',
            'customer_reference' => 'bar',
            'customer_address' => 'cake',
        ];

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchFeesByIds')
            ->once()
            ->with([99, 101])
            ->andReturn($fees)
            ->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($fees[0])
            ->once()
            ->shouldReceive('fetchById')
            ->with(101)
            ->andReturn($fees[1])
            ->once();

        $this->mockCpmsService
            ->shouldReceive('initiateCnpRequest')
            ->once()
            ->with($cpmsRedirectUrl, $fees, $extraParams)
            ->andReturn($this->cardResponse);

        /** @var PaymentEntity $savedPayment */
        $savedPayment = null;
        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(PaymentEntity::class))
            ->andReturnUsing(
                function (PaymentEntity $payment) use (&$savedPayment, $paymentId) {
                    $payment->setId($paymentId);
                    $savedPayment = $payment;
                }
            );

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'transaction' => $paymentId,
            ],
            'messages' => [
                'Transaction record created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(PaymentEntity::STATUS_OUTSTANDING, $savedPayment->getStatus()->getId());
    }

    /**
     * Test handle command for various application types (licence app, irhp)
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleForApplication')]
    public function testHandleCommandWithApplicationId(mixed $applicationType, mixed $feeServiceMethod): void
    {
        // set up data
        $cpmsRedirectUrl = 'https://olcs-selfserve/foo';
        $applicationId = 69;

        $paymentId = 999; // payment to be created

        $fee1 = $this->getStubFee(99, 99.99);
        $fee2 = $this->getStubFee(101, 99.99);
        $fees = [$fee1, $fee2];

        $customerName = 'customer name';
        $customerReference = 'customer reference';
        $customerAddress = 'customer address';

        $data = [
            'applicationId' => null,
            'irhpApplication' => null,
            'cpmsRedirectUrl' => $cpmsRedirectUrl,
            'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
            'customerName' => $customerName,
            'customerReference' => $customerReference,
            'address' => $customerAddress,
            'organisationId' => null,
        ];

        $data[$applicationType] = $applicationId;

        $command = Cmd::create($data);

        $extraParams = [
            'customer_name' => $customerName,
            'customer_reference' => $customerReference,
            'customer_address' => $customerAddress
        ];

        // expectations
        $this->repoMap['SystemParameter']->shouldReceive('getDisableSelfServeCardPayments')->with()->once()
            ->andReturn(false);

        $this->mockFeesHelperService
            ->shouldReceive($feeServiceMethod)
            ->once()
            ->with($applicationId)
            ->andReturn($fees);

        $this->repoMap['Fee']
            ->shouldReceive('fetchFeesByIds')
            ->once()
            ->with([99, 101])
            ->andReturn($fees)
            ->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($fees[0])
            ->once()
            ->shouldReceive('fetchById')
            ->with(101)
            ->andReturn($fees[1])
            ->once();

        $this->mockCpmsService
            ->shouldReceive('initiateCardRequest')
            ->once()
            ->with($cpmsRedirectUrl, $fees, $extraParams)
            ->andReturn($this->cardResponse);

        /** @var PaymentEntity $savedPayment */
        $savedPayment = null;
        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(PaymentEntity::class))
            ->andReturnUsing(
                function (PaymentEntity $payment) use (&$savedPayment, $paymentId) {
                    $payment->setId($paymentId);
                    $savedPayment = $payment;
                }
            );

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'transaction' => $paymentId,
            ],
            'messages' => [
                'Transaction record created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(PaymentEntity::STATUS_OUTSTANDING, $savedPayment->getStatus()->getId());
    }

    public static function dpHandleForApplication(): array
    {
        return [
            'licence application' => [
                'applicationId',
                'getOutstandingFeesForApplication',
            ],
            'irhp application' => [
                'irhpApplication',
                'getOutstandingFeesForIrhpApplication',
            ],
        ];
    }

    public function testHandleCommandCashPaymentWithOverpayment(): void
    {
        // set up data
        $feeIds = [99];
        $fee1 = $this->getStubFee(99, 99.99);
        $transactionId = 69;
        $applicationId = 100;
        $licenceId = 101;

        $fee1->setApplication(
            m::mock(ApplicationEntity::class)
                ->shouldReceive('getId')
                ->andReturn($applicationId)
                ->getMock()
        );

        $fee1->setLicence(
            m::mock(LicenceEntity::class)
                ->shouldReceive('getId')
                ->andReturn($licenceId)
                ->getMock()
        );

        $data = [
            'feeIds' => $feeIds,
            'paymentMethod' => FeeEntity::METHOD_CASH,
            'receiptDate' => '2015-06-17',
            'payer' => 'Dan',
            'slipNo' => '12345',
            'received' => '100.00', // overpayment of 0.01
            'customerName' => 'foo',
            'customerReference' => 'bar',
            'address' => 'cake',
        ];
        $extraParams = [
            'customer_name' => 'foo',
            'customer_reference' => 'bar',
            'customer_address' => 'cake'
        ];

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn([$fee1])
            ->shouldReceive('fetchFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn([$fee1])
            ->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($fee1)
            ->once();

        // overpayment balancing fee should be created
        //////////////////////////////////////////////////////////////////////
        $newFeeData = [
            'receivedAmount' => '100.00',
            'fees' => [$fee1],
        ];
        $newFeeResult = new Result();
        $newFeeResult->addId('fee', 100);
        $newFeeResult->addMessage('Overpayment balancing fee created');
        $this->expectedSideEffect(CreateOverpaymentFeeCmd::class, $newFeeData, $newFeeResult);

        $fee2 = $this->getStubFee(100, 0.01, FeeTypeEntity::FEE_TYPE_ADJUSTMENT); // overpayment
        $this->repoMap['Fee']
            ->shouldReceive('fetchById')
            ->with(100)
            ->once()
            ->andReturn($fee2);
        //////////////////////////////////////////////////////////////////////

        $this->mockCpmsService
            ->shouldReceive('recordCashPayment')
            ->once()
            ->with([$fee1, $fee2], '100.00', '2015-06-17', '12345', $extraParams)
            ->andReturn(
                [
                    'code' => CpmsHelper::RESPONSE_SUCCESS,
                    'receipt_reference' => 'OLCS-1234-CASH',
                    'schema_id' => 'client_id',
                ]
            );

        $this->mockFeesHelperService
            ->shouldReceive('getMinPaymentForFees')
            ->with([$fee1])
            ->andReturn(0.01);

        $this->mockFeesHelperService
            ->shouldReceive('allocatePayments')
            ->with('100.00', [$fee1, $fee2])
            ->andReturn(
                [
                    99 => '99.99',
                    100 => '0.01',
                ]
            );

        $this->mockFeesHelperService
            ->shouldReceive('getOverpaymentAmount')
            ->with('100.00', [$fee1])
            ->andReturn('0.01');

        $this->mockFeesHelperService
            ->shouldReceive('sortFeesByInvoiceDate')
            ->with([$fee1])
            ->andReturn([$fee1]);

        $this->repoMap['Fee']
            ->shouldReceive('save')
            ->once()
            ->with($fee1)
            ->shouldReceive('save')
            ->once()
            ->with($fee2);

        $savedTransaction = null;
        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->andReturnUsing(
                function ($transaction) use (&$savedTransaction, $transactionId) {
                    $savedTransaction = $transaction;
                    $savedTransaction->setId($transactionId);
                    $feeTransactionId = 200;
                    $savedTransaction->getFeeTransactions()->forAll(
                        function ($key, $ft) use (&$feeTransactionId) {
                            $ft->setId($feeTransactionId + $key);
                            return true; // closure *must* return true to continue
                        }
                    );
                }
            );

        $this->expectedSideEffect(PayFeeCmd::class, ['id' => 99], new Result());
        $this->expectedSideEffect(PayFeeCmd::class, ['id' => 100], new Result());

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'transaction' => $transactionId,
                'feeTransaction' => [
                    200,
                    201
                ],
                'fee' => 100,
            ],
            'messages' => [
                'Overpayment balancing fee created',
                'Fee ID 99 updated as paid by Cash',
                'Fee ID 100 updated as paid by Cash',
                'Transaction record created: OLCS-1234-CASH',
                'FeeTransaction record(s) created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(FeeEntity::STATUS_PAID, $fee1->getFeeStatus()->getId());
        $this->assertEquals('2015-06-17', $savedTransaction->getCompletedDate()->format('Y-m-d'));
        $this->assertEquals('OLCS-1234-CASH', $savedTransaction->getReference());
        $this->assertEquals(FeeEntity::METHOD_CASH, $savedTransaction->getPaymentMethod()->getId());
        $this->assertEquals('Dan', $savedTransaction->getPayerName());
        $this->assertEquals('12345', $savedTransaction->getPayingInSlipNumber());
        $this->assertEquals('bob', $savedTransaction->getProcessedByUser()->getLoginId());
        $this->assertEquals('99.99', $savedTransaction->getFeeTransactions()->get(0)->getAmount());
        $this->assertEquals('0.01', $savedTransaction->getFeeTransactions()->get(1)->getAmount());
        $this->assertEquals(PaymentEntity::STATUS_PAID, $savedTransaction->getStatus()->getId());
        $this->assertEquals(PaymentEntity::TYPE_PAYMENT, $savedTransaction->getType()->getId());
    }

    public function testHandleCommandChequePayment(): void
    {
        // set up data
        $feeIds = [99];
        $fee1 = $this->getStubFee(99, 99.99);
        $fees = [$fee1];
        $transactionId = 69;
        $feeTransactionId = 123;

        $data = [
            'feeIds' => $feeIds,
            'paymentMethod' => FeeEntity::METHOD_CHEQUE,
            'receiptDate' => '2015-06-17',
            'payer' => 'Dan',
            'slipNo' => '12345',
            'received' => '99.99',
            'chequeNo' => '23456',
            'chequeDate' => '2015-06-10',
            'customerName' => 'foo',
            'customerReference' => 'bar',
            'address' => 'cake',
        ];
        $extraParams = [
            'customer_name' => 'foo',
            'customer_reference' => 'bar',
            'customer_address' => 'cake'
        ];

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($fee1)
            ->once();

        $this->mockCpmsService
            ->shouldReceive('recordChequePayment')
            ->once()
            ->with($fees, '99.99', '2015-06-17', 'Dan', '12345', '23456', '2015-06-10', $extraParams)
            ->andReturn(
                [
                    'code' => CpmsHelper::RESPONSE_SUCCESS,
                    'receipt_reference' => 'OLCS-1234-CHEQUE',
                    'schema_id' => 'client_id',
                ]
            );

        $this->mockFeesHelperService
            ->shouldReceive('getMinPaymentForFees')
            ->with($fees)
            ->andReturn(0.01);

        $this->mockFeesHelperService
            ->shouldReceive('allocatePayments')
            ->with('99.99', $fees)
            ->andReturn(
                [
                    99 => '99.99',
                ]
            );

        $this->expectedSideEffect(
            CreateOverpaymentFeeCmd::class,
            ['receivedAmount' => '99.99', 'fees' => $fees],
            new Result()
        );

        $this->repoMap['Fee']
            ->shouldReceive('save')
            ->once()
            ->with($fee1);

        $savedTransaction = null;
        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->andReturnUsing(
                function ($transaction) use (&$savedTransaction, $transactionId, $feeTransactionId) {
                    $savedTransaction = $transaction;
                    $savedTransaction->setId($transactionId);
                    $savedTransaction->getFeeTransactions()->forAll(
                        function ($key, $ft) use ($feeTransactionId) {
                            $ft->setId($feeTransactionId);
                        }
                    );
                }
            );

        $updateData = ['id' => 99];
        $result2 = new Result();
        $this->expectedSideEffect(PayFeeCmd::class, $updateData, $result2);

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'transaction' => $transactionId,
                'feeTransaction' => [$feeTransactionId],
            ],
            'messages' => [
                'Fee ID 99 updated as paid by Cheque',
                'Transaction record created: OLCS-1234-CHEQUE',
                'FeeTransaction record(s) created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(FeeEntity::STATUS_PAID, $fee1->getFeeStatus()->getId());
        $this->assertEquals('2015-06-17', $savedTransaction->getCompletedDate()->format('Y-m-d'));
        $this->assertEquals('OLCS-1234-CHEQUE', $savedTransaction->getReference());
        $this->assertEquals(FeeEntity::METHOD_CHEQUE, $savedTransaction->getPaymentMethod()->getId());
        $this->assertEquals('Dan', $savedTransaction->getPayerName());
        $this->assertEquals('12345', $savedTransaction->getPayingInSlipNumber());
        $this->assertEquals('bob', $savedTransaction->getProcessedByUser()->getLoginId());
        $this->assertEquals('99.99', $savedTransaction->getFeeTransactions()->first()->getAmount());
        $this->assertEquals(PaymentEntity::STATUS_PAID, $savedTransaction->getStatus()->getId());
        $this->assertEquals(PaymentEntity::TYPE_PAYMENT, $savedTransaction->getType()->getId());
        $this->assertEquals('23456', $savedTransaction->getChequePoNumber());
        $this->assertEquals('2015-06-10', $savedTransaction->getChequePoDate()->format('Y-m-d'));
    }

    public function testHandleCommandPoPayment(): void
    {
        // set up data
        $feeIds = [99];
        $fee1 = $this->getStubFee(99, 99.99);
        $fees = [$fee1];
        $transactionId = 69;
        $feeTransactionId = 123;

        $data = [
            'feeIds' => $feeIds,
            'paymentMethod' => FeeEntity::METHOD_POSTAL_ORDER,
            'receiptDate' => '2015-06-17',
            'payer' => 'Dan',
            'slipNo' => '12345',
            'received' => '99.99',
            'poNo' => '23456',
            'customerName' => 'foo',
            'customerReference' => 'bar',
            'address' => 'cake',
        ];
        $extraParams = [
            'customer_name' => 'foo',
            'customer_reference' => 'bar',
            'customer_address' => 'cake'
        ];

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($fee1)
            ->once();

        $this->mockCpmsService
            ->shouldReceive('recordPostalOrderPayment')
            ->once()
            ->with($fees, '99.99', '2015-06-17', '12345', '23456', $extraParams)
            ->andReturn(
                [
                    'code' => CpmsHelper::RESPONSE_SUCCESS,
                    'receipt_reference' => 'OLCS-1234-PO',
                    'schema_id' => 'client_id',
                ]
            );

        $this->mockFeesHelperService
            ->shouldReceive('getMinPaymentForFees')
            ->with($fees)
            ->andReturn(0.01);

        $this->mockFeesHelperService
            ->shouldReceive('allocatePayments')
            ->with('99.99', $fees)
            ->andReturn(
                [
                    99 => '99.99',
                ]
            );

        $this->expectedSideEffect(
            CreateOverpaymentFeeCmd::class,
            ['receivedAmount' => '99.99', 'fees' => $fees],
            new Result()
        );

        $this->repoMap['Fee']
            ->shouldReceive('save')
            ->once()
            ->with($fee1);

        $savedTransaction = null;
        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->andReturnUsing(
                function ($transaction) use (&$savedTransaction, $transactionId, $feeTransactionId) {
                    $savedTransaction = $transaction;
                    $savedTransaction->setId($transactionId);
                    $savedTransaction->getFeeTransactions()->forAll(
                        function ($key, $ft) use ($feeTransactionId) {
                            $ft->setId($feeTransactionId);
                        }
                    );
                }
            );

        $updateData = ['id' => 99];
        $result2 = new Result();
        $this->expectedSideEffect(PayFeeCmd::class, $updateData, $result2);

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'transaction' => $transactionId,
                'feeTransaction' => [$feeTransactionId],
            ],
            'messages' => [
                'Fee ID 99 updated as paid by Postal Order',
                'Transaction record created: OLCS-1234-PO',
                'FeeTransaction record(s) created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(FeeEntity::STATUS_PAID, $fee1->getFeeStatus()->getId());
        $this->assertEquals('2015-06-17', $savedTransaction->getCompletedDate()->format('Y-m-d'));
        $this->assertEquals('OLCS-1234-PO', $savedTransaction->getReference());
        $this->assertEquals(FeeEntity::METHOD_POSTAL_ORDER, $savedTransaction->getPaymentMethod()->getId());
        $this->assertEquals('Dan', $savedTransaction->getPayerName());
        $this->assertEquals('12345', $savedTransaction->getPayingInSlipNumber());
        $this->assertEquals('bob', $savedTransaction->getProcessedByUser()->getLoginId());
        $this->assertEquals('99.99', $savedTransaction->getFeeTransactions()->first()->getAmount());
        $this->assertEquals(PaymentEntity::STATUS_PAID, $savedTransaction->getStatus()->getId());
        $this->assertEquals(PaymentEntity::TYPE_PAYMENT, $savedTransaction->getType()->getId());
        $this->assertEquals('23456', $savedTransaction->getChequePoNumber());
    }

    public function testHandleCommandAmountMismatch(): void
    {
        // set up data
        $feeIds = [99];
        $fee1 = $this->getStubFee(99, 99.99);
        $fees = [$fee1];

        $data = [
            'feeIds' => $feeIds,
            'paymentMethod' => FeeEntity::METHOD_CASH,
            'receiptDate' => '2015-06-17',
            'payer' => 'Dan',
            'slipNo' => '12345',
            'received' => '98.99', // 1 quid short :(
        ];

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($fee1)
            ->once();

        $this->mockCpmsService
            ->shouldReceive('recordCashPayment')
            ->never();

        $this->mockFeesHelperService
            ->shouldReceive('getMinPaymentForFees')
            ->with($fees)
            ->andReturn(99);

        // try/catch as we can't assert the message otherwise
        try {
            $this->sut->handleCommand($command);
        } catch (ValidationException $e) {
            $messages = $e->getMessages();
            $this->assertTrue(str_contains((string) reset($messages), 'Amount must be at least 99.00'));
        }
    }

    public function testHandleCommandAllocationError(): void
    {
        // set up data
        $feeIds = [99];
        $fee1 = $this->getStubFee(99, 99.99);
        $fees = [$fee1];

        $data = [
            'feeIds' => $feeIds,
            'paymentMethod' => FeeEntity::METHOD_CASH,
            'receiptDate' => '2015-06-17',
            'payer' => 'Dan',
            'slipNo' => '12345',
            'received' => '1000',
        ];

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($fee1)
            ->once();

        $this->mockCpmsService
            ->shouldReceive('recordCashPayment')
            ->never();

        $this->mockFeesHelperService
            ->shouldReceive('getMinPaymentForFees')
            ->with($fees)
            ->andReturn('0.01');

        $this->expectedSideEffect(
            CreateOverpaymentFeeCmd::class,
            ['receivedAmount' => '1000', 'fees' => $fees],
            new Result()
        );

        $this->mockFeesHelperService
            ->shouldReceive('allocatePayments')
            ->once()
            ->andThrow(new ServiceException('ohnoes'));

        try {
            $this->sut->handleCommand($command);
        } catch (RuntimeException $e) {
            $messages = $e->getMessages();
            $this->assertTrue(str_contains((string) reset($messages), 'ohnoes'));
        }
    }

    public function testHandleCommandCpmsResponseException(): void
    {
        // set up data
        $feeIds = [99];
        $fee1 = $this->getStubFee(99, 99.99);
        $fees = [$fee1];

        $data = [
            'feeIds' => $feeIds,
            'paymentMethod' => FeeEntity::METHOD_CASH,
            'receiptDate' => '2015-06-17',
            'payer' => 'Dan',
            'slipNo' => '12345',
            'received' => '99.99',
        ];

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($fee1)
            ->once();

        $this->mockFeesHelperService
            ->shouldReceive('allocatePayments')
            ->with('99.99', $fees)
            ->andReturn(
                [
                    99 => '99.99',
                ]
            );

        $this->expectedSideEffect(
            CreateOverpaymentFeeCmd::class,
            ['receivedAmount' => '99.99', 'fees' => $fees],
            new Result()
        );

        $this->mockCpmsService
            ->shouldReceive('recordCashPayment')
            ->once()
            ->andThrow(new \Dvsa\Olcs\Api\Service\CpmsResponseException('ohnoes', 400));

        $this->mockFeesHelperService
            ->shouldReceive('getMinPaymentForFees')
            ->with($fees)
            ->andReturn(0.01);

        $this->expectException(
            \Dvsa\Olcs\Api\Domain\Exception\RestResponseException::class
        );

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('feeDataProvider')]
    public function testHandleCommandChequePaymentInsufficientFee(mixed $type, mixed $orgId): void
    {
        // set up data
        $feeIds = [99];
        $fee1 = $this->getStubFee(99, 99.99);
        $fee1->setLicence(
            m::mock(Licence::class)->shouldReceive('getId')->andReturn(22)->getMock()
        );
        $fee1->setApplication(
            m::mock(ApplicationEntity::class)->shouldReceive('getId')->andReturn(33)->getMock()
        );
        $fee1->setBusReg(
            m::mock(BusRegEntity::class)->shouldReceive('getId')->andReturn(44)->getMock()
        );
        if ($type === 'psv') {
            $fee1->setIrfoPsvAuth(
                m::mock(PsvAuthEntity::class)
                    ->shouldReceive('getOrganisation')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('getId')
                        ->andReturn($orgId)
                        ->getMock()
                    )
                    ->getMock()
            );
        } else {
            $fee1->setIrfoGvPermit(
                m::mock(GvPermitEntity::class)
                    ->shouldReceive('getOrganisation')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('getId')
                        ->andReturn($orgId)
                        ->getMock()
                    )
                    ->getMock()
            );
        }
        $fees = [$fee1];
        $transactionId = 69;
        $feeTransactionId = 123;

        $data = [
            'feeIds' => $feeIds,
            'paymentMethod' => FeeEntity::METHOD_CHEQUE,
            'receiptDate' => '2015-06-17',
            'payer' => 'Dan',
            'slipNo' => '12345',
            'received' => '10.00',
            'chequeNo' => '23456',
            'chequeDate' => '2015-06-10',
            'customerName' => 'foo',
            'customerReference' => 'bar',
            'address' => 'cake',
        ];
        $extraParams = [
            'customer_name' => 'foo',
            'customer_reference' => 'bar',
            'customer_address' => 'cake'
        ];

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($fee1)
            ->once();

        $this->mockCpmsService
            ->shouldReceive('recordChequePayment')
            ->once()
            ->with($fees, '10.00', '2015-06-17', 'Dan', '12345', '23456', '2015-06-10', $extraParams)
            ->andReturn(
                [
                    'code' => CpmsHelper::RESPONSE_SUCCESS,
                    'receipt_reference' => 'OLCS-1234-CHEQUE',
                    'schema_id' => 'client_id',
                ]
            );

        $this->mockFeesHelperService
            ->shouldReceive('getMinPaymentForFees')
            ->with($fees)
            ->andReturn(0.01);

        $this->mockFeesHelperService
            ->shouldReceive('allocatePayments')
            ->with('10.00', $fees)
            ->andReturn(
                [
                    99 => '10.00',
                ]
            );

        $this->expectedSideEffect(
            CreateOverpaymentFeeCmd::class,
            ['receivedAmount' => '10.00', 'fees' => $fees],
            new Result()
        );

        $docData = [
            'template' => 'FEE_REQ_INSUFFICIENT',
            'query' => [
                'fee' => 99,
                'licence' => 22
            ],
            'knownValues' => [
                'INSUFFICIENT_FEE_TABLE' => [
                    'receivedAmount' => '10',
                    'outstandingAmount' => '89.99'
                ]
            ],
            'description' => 'Insufficient Fee Request',
            'licence'     => 22,
            'application' => 33,
            'category'    => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_FEE_REQUEST,
            'isExternal'  => false,
            'dispatch'    => true
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $docData, new Result());

        $savedTransaction = null;
        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->andReturnUsing(
                function ($transaction) use (&$savedTransaction, $transactionId, $feeTransactionId) {
                    $savedTransaction = $transaction;
                    $savedTransaction->setId($transactionId);
                    $savedTransaction->getFeeTransactions()->forAll(
                        function ($key, $ft) use ($feeTransactionId) {
                            $ft->setId($feeTransactionId);
                        }
                    );
                }
            );

        $taskId = 987;
        $taskResult = new Result();
        $taskResult->addId('task', $taskId);
        $createTaskData = [
            'category' => Task::CATEGORY_LICENSING,
            'subCategory' => Task::SUBCATEGORY_LICENSING_GENERAL_TASK,
            'description' => Task::TASK_DESCRIPTION_FEE_DUE,
            'actionDate' => (new DateTime('now'))->format(\DateTime::W3C),
            'licence' => 22,
            'application' => 33,
            'busReg' => 44,
            'irfoOrganisation' => $orgId,
            'assignedToUser' => 1,
            'assignedToTeam' => 2
        ];
        $this->expectedSideEffect(CreateTask::class, $createTaskData, $taskResult);

        $mockTask = m::mock(Task::class);

        $this->repoMap['Task']
            ->shouldReceive('fetchById')
            ->with($taskId)
            ->andReturn($mockTask)
            ->once()
            ->getMock();

        $this->repoMap['Fee']
            ->shouldReceive('save')
            ->with($fee1)
            ->once()
            ->getMock();

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'transaction' => $transactionId,
                'feeTransaction' => [$feeTransactionId],
                'task' => $taskId,
            ],
            'messages' => [
                'Transaction record created: OLCS-1234-CHEQUE',
                'FeeTransaction record(s) created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals('2015-06-17', $savedTransaction->getCompletedDate()->format('Y-m-d'));
        $this->assertEquals('OLCS-1234-CHEQUE', $savedTransaction->getReference());
        $this->assertEquals(FeeEntity::METHOD_CHEQUE, $savedTransaction->getPaymentMethod()->getId());
        $this->assertEquals('Dan', $savedTransaction->getPayerName());
        $this->assertEquals('12345', $savedTransaction->getPayingInSlipNumber());
        $this->assertEquals('bob', $savedTransaction->getProcessedByUser()->getLoginId());
        $this->assertEquals('10.00', $savedTransaction->getFeeTransactions()->first()->getAmount());
        $this->assertEquals(PaymentEntity::STATUS_PAID, $savedTransaction->getStatus()->getId());
        $this->assertEquals(PaymentEntity::TYPE_PAYMENT, $savedTransaction->getType()->getId());
        $this->assertEquals('23456', $savedTransaction->getChequePoNumber());
        $this->assertEquals('2015-06-10', $savedTransaction->getChequePoDate()->format('Y-m-d'));
    }

    public static function feeDataProvider(): array
    {
        return [
            [
                'psv',
                55,
            ],
            [
                'gv',
                66,
            ]
        ];
    }

    public function testHandleCommandCancelPendingWaive(): void
    {
        // set up data
        $feeIds = [99];
        $cpmsRedirectUrl = 'https://olcs-selfserve/foo';

        $paymentId = 999; // payment to be created

        $fee1 = $this->getStubFee(99, 99.99);
        $fees = [$fee1];

        // add an outstanding waive transaction
        $waive = m::mock(PaymentEntity::class);
        $waive->shouldReceive('getType->getId')->andReturn(PaymentEntity::TYPE_WAIVE);
        $waive->shouldReceive('isOutstanding')->andReturn(true);
        $waive->shouldReceive('getId')->andReturn(123);
        $waive->shouldReceive('getPaymentMethod')->andReturn($this->mapRefData(FeeEntity::METHOD_WAIVE));
        $waive->shouldReceive('isPaid')->andReturn(false);
        $waive->shouldReceive('getCreatedOn')->andReturn(new DateTime('2017-01-01'));
        $waive->shouldReceive('isComplete')->andReturn(false);
        $waiveFeeTransaction = m::mock(FeePaymentEntity::class)
            ->shouldReceive('getTransaction')
            ->andReturn($waive)
            ->getMock();
        $fee1->setFeeTransactions(new ArrayCollection([$waiveFeeTransaction]));

        $data = [
            'feeIds' => $feeIds,
            'cpmsRedirectUrl' => $cpmsRedirectUrl,
            'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
            'customerName' => 'foo',
            'customerReference' => 'bar',
            'address' => 'cake',
        ];
        $extraParams = [
            'customer_name' => 'foo',
            'customer_reference' => 'bar',
            'customer_address' => 'cake'
        ];

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['SystemParameter']->shouldReceive('getDisableSelfServeCardPayments')->with()->once()
            ->andReturn(false);

        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees);

        $this->mockCpmsService
            ->shouldReceive('initiateCardRequest')
            ->once()
            ->with($cpmsRedirectUrl, $fees, $extraParams)
            ->andReturn($this->cardResponse);

        /** @var PaymentEntity $savedPayment */
        $savedPayment = null;
        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(PaymentEntity::class))
            ->andReturnUsing(
                function (PaymentEntity $payment) use (&$savedPayment, $paymentId) {
                    $payment->setId($paymentId);
                    $savedPayment = $payment;
                }
            );

        $resolveResult = new Result();
        $resolveResult->addMessage('Waive transaction 123 not resolved');
        $this->expectedSideEffect(
            ResolvePaymentCommand::class,
            [
                'id' => 123,
                'paymentMethod' => FeeEntity::METHOD_WAIVE,
            ],
            $resolveResult
        );
        $this->repoMap['Transaction']
            ->shouldReceive('fetchById')
            ->once()
            ->with(123)
            ->andReturn($waive);

        $rejectResult = new Result();
        $rejectResult->addMessage('Waive transaction cancelled');
        $this->expectedSideEffect(
            RejectWaiveCmd::class,
            ['id' => 99],
            $rejectResult
        );

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'transaction' => $paymentId,
            ],
            'messages' => [
                'Waive transaction 123 not resolved',
                'Waive transaction cancelled',
                'Transaction record created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(PaymentEntity::STATUS_OUTSTANDING, $savedPayment->getStatus()->getId());
    }

    public function testHandleCommandInvalidPaymentMethod(): void
    {
        $feeIds = [1, 2];

        $data = [
            'feeIds' => $feeIds,
            'paymentMethod' => 'foo',
            'received' => '35.79',
        ];

        $command = Cmd::create($data);

        $fee1 = $this->getStubFee(99, 12.34);
        $fee2 = $this->getStubFee(100, 23.45);
        $fees = [$fee1, $fee2];

        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchFeesByIds')
            ->once()
            ->with([99, 100])
            ->andReturn($fees)
            ->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($fee1)
            ->once()
            ->shouldReceive('fetchById')
            ->with(100)
            ->andReturn($fee2)
            ->once();

        $this->mockFeesHelperService
            ->shouldReceive('getMinPaymentForFees')
            ->with($fees)
            ->andReturn(12.35);

        $allocations = [
            99 => '12.34',
            100 => '23.45',
        ];
        $this->mockFeesHelperService
            ->shouldReceive('allocatePayments')
            ->with('35.79', $fees)
            ->andReturn($allocations);

        $opData = [
            'receivedAmount' => '35.79',
            'fees' => $fees,
        ];
        $this->expectedSideEffect(CreateOverpaymentFeeCmd::class, $opData, new Result());

        $this->expectException(BadRequestException::class);

        $this->sut->handleCommand($command);
    }

    /**
     * Helper function to generate a stub fee entity
     *
     * @param int $id
     * @param string $amount
     * @return FeeEntity
     */
    private function getStubFee(mixed $id, mixed $amount, mixed $typeId = null): mixed
    {
        $status = new RefData();
        $feeType = new FeeTypeEntity();
        $feeType->setFeeType(new RefData($typeId));

        $fee = new FeeEntity($feeType, $amount, $status);
        $fee->setId($id);

        return $fee;
    }

    public function testHandleCommandWithPendingFees(): void
    {
        // set up data
        $feeIds = [99];
        $transactionId = 10;

        $transaction = m::mock()
            ->shouldReceive('isOutstanding')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getId')
            ->andReturn($transactionId)
            ->once()
            ->shouldReceive('getPaymentMethod')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(1)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('isPaid')
            ->andReturn(false)
            ->once()
            ->getMock();

        $feeTransaction = m::mock()
            ->shouldReceive('getTransaction')
            ->andReturn($transaction)
            ->getMock();

        $this->expectedSideEffect(
            ResolvePaymentCommand::class,
            ['id' => $transactionId, 'paymentMethod' => 1],
            new Result()
        );

        $feeTransactions = new ArrayCollection();
        $feeTransactions->add($feeTransaction);

        $fee = m::mock(FeeEntity::class)
            ->shouldReceive('hasOutstandingPayment')
            ->andReturn(true)
            ->once()
            ->shouldReceive('hasOutstandingPaymentExcludeWaive')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getFeeTransactions')
            ->andReturn($feeTransactions)
            ->once()
            ->shouldReceive('getId')
            ->andReturn(99)
            ->once()
            ->getMock();

        $fees = [$fee];

        $this->repoMap['Transaction']
            ->shouldReceive('fetchById')
            ->with($transactionId)
            ->andReturn($transaction)
            ->getMock();

        $data = [
            'feeIds' => $feeIds,
            'paymentMethod' => FeeEntity::METHOD_CARD_OFFLINE,
            'customerName' => 'foo',
            'customerReference' => 'bar',
            'address' => 'cake',
        ];
        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees);

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                [
                    'ERR_WAIT' => 'Error attempting to resolve a previous payment. Please wait 15 minutes and try again'
                ]
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithCompletedFees(): void
    {
        // set up data
        $feeIds = [99];

        $fee = m::mock(FeeEntity::class)
            ->shouldReceive('hasOutstandingPayment')
            ->andReturn(false)
            ->once()
            ->shouldReceive('getId')
            ->andReturn(99)
            ->once()
            ->shouldReceive('getFeeStatus')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(FeeEntity::STATUS_PAID)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $fees = [$fee];

        $data = [
            'feeIds' => $feeIds,
            'paymentMethod' => FeeEntity::METHOD_CARD_OFFLINE,
            'customerName' => 'foo',
            'customerReference' => 'bar',
            'address' => 'cake',
        ];
        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($fee)
            ->once();

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                [
                    'ERR_NO_FEES' => 'The fee(s) has already been paid'
                ]
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithFeeIdsResolveOnly(): void
    {
        // set up data
        $feeIds = [99, 100, 101];

        $fee1 = $this->getStubFee(99, 99.99);
        $fee2 = $this->getStubFee(101, 99.99);
        $fees = [$fee1, $fee2];

        $data = [
            'feeIds' => $feeIds,
            'shouldResolveOnly' => true
        ];

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByIds')
            ->once()
            ->with($feeIds)
            ->andReturn($fees)
            ->shouldReceive('fetchFeesByIds')
            ->once()
            ->with([99, 101])
            ->andReturn($fees)
            ->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($fees[0])
            ->once()
            ->shouldReceive('fetchById')
            ->with(101)
            ->andReturn($fees[1])
            ->once();

        // assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
