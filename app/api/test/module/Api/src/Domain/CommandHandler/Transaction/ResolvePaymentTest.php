<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Transaction;

use Dvsa\Olcs\Api\Domain\Command\Fee\PayFee as PayFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Transaction\ResolvePayment as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Transaction\ResolvePayment;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\AbstractRepository as Repo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction as FeePaymentEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as PaymentEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\CpmsHelperInterface as CpmsHelper;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Fee\Transaction;

/**
 * Resolve Payment Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ResolvePaymentTest extends AbstractCommandHandlerTestCase
{
    protected $mockCpmsService;

    public function setUp(): void
    {
        $this->mockCpmsService = m::mock(CpmsHelper::class);
        $this->mockedSmServices = [
            'CpmsHelperService' => $this->mockCpmsService,
            AuthorizationService::class => m::mock(AuthorizationService::class)->makePartial(),
            'config' => [],
        ];

        $this->sut = new ResolvePayment();
        $this->mockRepo('Transaction', Repo::class);
        $this->mockRepo('Fee', Repo::class);
        $this->mockRepo('Task', Repo::class);

        $this->refData = [
            PaymentEntity::STATUS_OUTSTANDING => m::mock(RefData::class)
                ->shouldReceive('getId')
                ->andReturn(PaymentEntity::STATUS_OUTSTANDING)
                ->getMock(),
            PaymentEntity::STATUS_PAID => m::mock(RefData::class)
                ->shouldReceive('getDescription')
                ->andReturn('PAYMENT PAID')
                ->shouldReceive('getId')
                ->andReturn(PaymentEntity::STATUS_PAID)
                ->getMock(),
            PaymentEntity::STATUS_FAILED => m::mock(RefData::class)
                ->shouldReceive('getDescription')
                ->andReturn('PAYMENT FAILED')
                ->getMock(),
            PaymentEntity::STATUS_CANCELLED => m::mock(RefData::class)
                ->shouldReceive('getDescription')
                ->andReturn('PAYMENT CANCELLED')
                ->getMock(),
            FeeEntity::STATUS_PAID => m::mock(RefData::class)
                ->shouldReceive('getDescription')
                ->andReturn('FEE PAID')
                ->getMock(),
            FeeEntity::METHOD_CARD_ONLINE => m::mock(RefData::class)
                ->shouldReceive('getDescription')
                ->andReturn('CARD')
                ->getMock(),
            PaymentEntity::TYPE_PAYMENT,
        ];

        $this->references = [
            FeePaymentEntity::class => [
                11 => m::mock(FeePaymentEntity::class)->makePartial(),
            ],
            FeeEntity::class => [
                22 => m::mock(FeeEntity::class)->makePartial(),
            ],
        ];

        $this->references[FeePaymentEntity::class][11]
            ->setFee($this->references[FeeEntity::class][22]);

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

    public function testHandleCommandSuccess(): void
    {
        // set up data
        $paymentId = 69;
        $guid = 'OLCS-1234-ABCDE';
        $amount = '1234.56';

        $data = [
            'id' => $paymentId,
            'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
        ];

        $feeTransactions = new ArrayCollection();
        $feeTransactions->add('feeTransaction');

        $fee = $this->references[FeeEntity::class][22];
        $fee->setGrossAmount($amount);
        $fee->shouldReceive('getOutstandingAmount')
            ->andReturn('0.00')
            ->shouldReceive('getFeeTransactions')
            ->andReturn($feeTransactions)
            ->once();

        $payment = m::mock(PaymentEntity::class)->makePartial();
        $payment->setId($paymentId);
        $payment->setReference($guid);
        $payment->setFeeTransactions($this->references[FeePaymentEntity::class]);
        $payment->setType($this->refData[PaymentEntity::TYPE_PAYMENT]);
        $payment->setStatus($this->refData[PaymentEntity::STATUS_OUTSTANDING]);

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Transaction']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($payment);

        $this->mockCpmsService
            ->shouldReceive('getPaymentStatus')
            ->once()
            ->with($guid, 'fee1')
            ->andReturn(['code' => CpmsHelper::PAYMENT_SUCCESS]);

        $payment
            ->shouldReceive('setStatus')
            ->once()
            ->with($this->refData[PaymentEntity::STATUS_PAID])
            ->passthru()
            ->andReturnSelf()
            ->globally()
            ->ordered()
            ->shouldReceive('getFees')
            ->andReturn(['fee1', 'fee2'])
            ->once();

        $this->repoMap['Fee']
            ->shouldReceive('save')
            ->once()
            ->with($fee)
            ->globally()
            ->ordered();

        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->once()
            ->with($payment);

        $updateData = ['id' => 22];
        $result2 = new Result();
        $this->expectedSideEffect(PayFeeCmd::class, $updateData, $result2);

        $now = new DateTime();

        // assertions
        $result = $this->sut->handleCommand($command);

        $this->assertEquals('FEE PAID', $fee->getFeeStatus()->getDescription());
        $this->assertEquals($guid, $payment->getReference());
        $this->assertEquals($now->format('Y-m-d'), $payment->getCompletedDate()->format('Y-m-d'));

        $expected = [
            'id' => [
                'transaction' => 69,
            ],
            'messages' => [
                'Fee ID 22 updated as paid',
                'Transaction 69 resolved as PAYMENT PAID',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandSuccessTotalAmountEqualToGrossAmount(): void
    {
        // set up data
        $paymentId = 69;
        $guid = 'OLCS-1234-ABCDE';
        $amount = '25.00';

        $data = [
            'id' => $paymentId,
            'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
        ];

        $feeTransaction1 = m::mock()
            ->shouldReceive('getTransaction')
            ->andReturn(
                m::mock()
                ->shouldReceive('getStatus')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(Transaction::STATUS_COMPLETE)
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('getAmount')
            ->andReturn('10.12')
            ->once()
            ->getMock();

        $feeTransaction2 = m::mock()
            ->shouldReceive('getTransaction')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getStatus')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn(Transaction::STATUS_COMPLETE)
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getAmount')
            ->andReturn('14.88')
            ->once()
            ->getMock();

        $feeTransactions = new ArrayCollection();
        $feeTransactions->add($feeTransaction1);
        $feeTransactions->add($feeTransaction2);

        $fee = $this->references[FeeEntity::class][22];
        $fee->setGrossAmount($amount);
        $fee->shouldReceive('getOutstandingAmount')
            ->andReturn('0.00')
            ->shouldReceive('getFeeTransactions')
            ->andReturn($feeTransactions)
            ->once();

        $payment = m::mock(PaymentEntity::class)->makePartial();
        $payment->setId($paymentId);
        $payment->setReference($guid);
        $payment->setFeeTransactions($this->references[FeePaymentEntity::class]);
        $payment->setType($this->refData[PaymentEntity::TYPE_PAYMENT]);
        $payment->setStatus($this->refData[PaymentEntity::STATUS_OUTSTANDING]);

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Transaction']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($payment);

        $this->mockCpmsService
            ->shouldReceive('getPaymentStatus')
            ->once()
            ->with($guid, 'fee1')
            ->andReturn(['code' => CpmsHelper::PAYMENT_SUCCESS]);

        $payment
            ->shouldReceive('setStatus')
            ->once()
            ->with($this->refData[PaymentEntity::STATUS_PAID])
            ->passthru()
            ->andReturnSelf()
            ->globally()
            ->ordered()
            ->shouldReceive('getFees')
            ->andReturn(['fee1', 'fee2'])
            ->once();

        $this->repoMap['Fee']
            ->shouldReceive('save')
            ->once()
            ->with($fee)
            ->globally()
            ->ordered();

        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->once()
            ->with($payment);

        $updateData = ['id' => 22];
        $result2 = new Result();
        $this->expectedSideEffect(PayFeeCmd::class, $updateData, $result2);

        $now = new DateTime();

        // assertions
        $result = $this->sut->handleCommand($command);

        $this->assertEquals('FEE PAID', $fee->getFeeStatus()->getDescription());
        $this->assertEquals($guid, $payment->getReference());
        $this->assertEquals($now->format('Y-m-d'), $payment->getCompletedDate()->format('Y-m-d'));

        $expected = [
            'id' => [
                'transaction' => 69,
            ],
            'messages' => [
                'Fee ID 22 updated as paid',
                'Transaction 69 resolved as PAYMENT PAID',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('irfoDataProfider')]
    public function testHandleCommandSuccessTotalAmountGreaterThenGrossAmount(mixed $type): void
    {
        // set up data
        $paymentId = 69;
        $guid = 'OLCS-1234-ABCDE';
        $amount = '25.00';

        $data = [
            'id' => $paymentId,
            'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
        ];

        $feeTransaction1 = m::mock()
            ->shouldReceive('getTransaction')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getStatus')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn(Transaction::STATUS_COMPLETE)
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getAmount')
            ->andReturn('10.12')
            ->once()
            ->getMock();

        $feeTransaction2 = m::mock()
            ->shouldReceive('getTransaction')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getStatus')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn(Transaction::STATUS_COMPLETE)
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getAmount')
            ->andReturn('15.88')
            ->once()
            ->getMock();

        $feeTransactions = new ArrayCollection();
        $feeTransactions->add($feeTransaction1);
        $feeTransactions->add($feeTransaction2);

        $fee = $this->references[FeeEntity::class][22];
        $fee->setGrossAmount($amount);
        $fee->shouldReceive('getOutstandingAmount')
            ->andReturn('0.00')
            ->shouldReceive('getFeeTransactions')
            ->andReturn($feeTransactions)
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(10)
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('getApplication')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(20)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getBusReg')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(30)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock()
                ->shouldReceive('getFeeType')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getDescription')
                    ->andReturn('[type]')
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        if ($type === 'PSV') {
            $fee->shouldReceive('getIrfoPsvAuth')
                ->andReturn(
                    m::mock()
                        ->shouldReceive('getOrganisation')
                        ->andReturn(
                            m::mock()
                                ->shouldReceive('getId')
                                ->andReturn(40)
                                ->once()
                                ->getMock()
                        )
                        ->once()
                        ->getMock()
                )
                ->once()
                ->shouldReceive('getIrfoGvPermit')
                ->andReturnNull()
                ->once()
                ->getMock();
        } else {
            $fee->shouldReceive('getIrfoGvPermit')
                ->andReturn(
                    m::mock()
                        ->shouldReceive('getOrganisation')
                        ->andReturn(
                            m::mock()
                                ->shouldReceive('getId')
                                ->andReturn(40)
                                ->once()
                                ->getMock()
                        )
                        ->once()
                        ->getMock()
                )
                ->once()
                ->shouldReceive('getIrfoPsvAuth')
                ->andReturnNull()
                ->once()
               ->getMock();
        }

        $createTaskData = [
            'category' => Task::CATEGORY_LICENSING,
            'subCategory' => Task::SUBCATEGORY_LICENSING_GENERAL_TASK,
            'description' => sprintf(
                Task::TASK_DESCRIPTION_DUPLICATED,
                '[type]',
                22
            ),
            'actionDate' => (new DateTime('now'))->format(\DateTime::W3C),
            'isClosed' => 0,
            'urgent' => 1,
            'licence' => 10,
            'application' => 20,
            'busReg' => 30,
            'irfoOrganisation' => 40
        ];
        $this->expectedSideEffect(CreateTask::class, $createTaskData, new Result());

        $payment = m::mock(PaymentEntity::class)->makePartial();
        $payment->setId($paymentId);
        $payment->setReference($guid);
        $payment->setFeeTransactions($this->references[FeePaymentEntity::class]);
        $payment->setType($this->refData[PaymentEntity::TYPE_PAYMENT]);
        $payment->setStatus($this->refData[PaymentEntity::STATUS_OUTSTANDING]);

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Transaction']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($payment);

        $this->mockCpmsService
            ->shouldReceive('getPaymentStatus')
            ->once()
            ->with($guid, 'fee1')
            ->andReturn(['code' => CpmsHelper::PAYMENT_SUCCESS]);

        $payment
            ->shouldReceive('setStatus')
            ->once()
            ->with($this->refData[PaymentEntity::STATUS_PAID])
            ->passthru()
            ->andReturnSelf()
            ->globally()
            ->ordered()
            ->shouldReceive('getFees')
            ->andReturn(['fee1', 'fee2'])
            ->once();

        $this->repoMap['Fee']
            ->shouldReceive('save')
            ->once()
            ->with($fee)
            ->globally()
            ->ordered();

        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->once()
            ->with($payment);

        $updateData = ['id' => 22];
        $result2 = new Result();
        $this->expectedSideEffect(PayFeeCmd::class, $updateData, $result2);

        $now = new DateTime();

        // assertions
        $result = $this->sut->handleCommand($command);

        $this->assertEquals('FEE PAID', $fee->getFeeStatus()->getDescription());
        $this->assertEquals($guid, $payment->getReference());
        $this->assertEquals($now->format('Y-m-d'), $payment->getCompletedDate()->format('Y-m-d'));

        $expected = [
            'id' => [
                'transaction' => 69,
            ],
            'messages' => [
                'Fee ID 22 updated as paid',
                'Transaction 69 resolved as PAYMENT PAID',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function irfoDataProfider(): array
    {
        return [
            ['PSV'],
            ['GV']
        ];
    }

    /**
     * @param string $cpmsStatus
     * @param string $expectedPaymentStatus
     * @param string $expectedMessage
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('failureStatusProvider')]
    public function testHandleCommandFailures(mixed $cpmsStatus, mixed $expectedPaymentStatus, mixed $expectedMessage): void
    {
        // set up data
        $paymentId = 69;
        $guid = 'OLCS-1234-ABCDE';
        $amount = '1234.56';

        $data = [
            'id' => $paymentId,
            'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
        ];

        $fee = $this->references[FeeEntity::class][22];
        $fee->setGrossAmount($amount);

        $payment = m::mock(PaymentEntity::class)->makePartial();
        $payment->setId($paymentId);
        $payment->setReference($guid);
        $payment->setFeeTransactions($this->references[FeePaymentEntity::class]);
        $payment->setStatus($this->refData[PaymentEntity::STATUS_OUTSTANDING]);
        $payment->setType($this->refData[PaymentEntity::TYPE_PAYMENT]);
        $payment->setStatus($this->refData[PaymentEntity::STATUS_OUTSTANDING]);

        $payment
            ->shouldReceive('getFees')
            ->andReturn(['fee1', 'fee2'])
            ->once();

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Transaction']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($payment);

        $this->mockCpmsService
            ->shouldReceive('getPaymentStatus')
            ->once()
            ->with($guid, 'fee1')
            ->andReturn(['code' => $cpmsStatus]);

        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->once()
            ->with($payment);

        $this->repoMap['Fee']
            ->shouldReceive('save')
            ->never();

        // assertions
        $result = $this->sut->handleCommand($command);

        $this->assertEquals($expectedPaymentStatus, $payment->getStatus()->getId());

        $expected = [
            'id' => [
                'transaction' => 69,
            ],
            'messages' => [
                $expectedMessage,
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function failureStatusProvider(): array
    {
        return [
            [
                CpmsHelper::PAYMENT_FAILURE,
                PaymentEntity::STATUS_FAILED,
                'Transaction 69 resolved as PAYMENT FAILED',
            ],
            [
                CpmsHelper::PAYMENT_CANCELLATION,
                PaymentEntity::STATUS_CANCELLED,
                'Transaction 69 resolved as PAYMENT CANCELLED',
            ],
            [
                CpmsHelper::PAYMENT_GATEWAY_ERROR,
                PaymentEntity::STATUS_FAILED,
                'Transaction 69 resolved as PAYMENT FAILED',
            ],
            [
                CpmsHelper::PAYMENT_SYSTEM_ERROR,
                PaymentEntity::STATUS_FAILED,
                'Transaction 69 resolved as PAYMENT FAILED',
            ],
        ];
    }

    /**
     * @param int    $cpmsStatus
     * @param string $expectedMessage
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('otherStatusProvider')]
    public function testHandleCommandOtherStatus(mixed $cpmsStatus, mixed $expectedMessage): void
    {
        // set up data
        $paymentId = 69;
        $guid = 'OLCS-1234-ABCDE';

        $data = [
            'id' => $paymentId,
            'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
        ];

        $payment = m::mock(PaymentEntity::class)->makePartial();
        $payment->setId($paymentId);
        $payment->setReference($guid);
        $payment->setType($this->refData[PaymentEntity::TYPE_PAYMENT]);
        $payment
            ->shouldReceive('getFees')
            ->andReturn(['fee1', 'fee2'])
            ->once();
        $payment->setStatus($this->refData[PaymentEntity::STATUS_OUTSTANDING]);

        $command = Cmd::create($data);

        // expectations
        $this->repoMap['Transaction']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($payment);

        $this->mockCpmsService
            ->shouldReceive('getPaymentStatus')
            ->once()
            ->with($guid, 'fee1')
            ->andReturn($cpmsStatus);

        // payment status should not be changed
        $payment->shouldReceive('setStatus')->never();
        // payment(transation) should not be updated
        $this->repoMap['Transaction']->shouldReceive('save')->never();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'transaction' => 69,
            ],
            'messages' => [
                $expectedMessage
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function otherStatusProvider(): array
    {
        return [
            [
                ['code' => CpmsHelper::PAYMENT_IN_PROGRESS],
                'Transaction 69 is pending, CPMS status is 800',
            ],
            [
                ['code' => CpmsHelper::PAYMENT_AWAITING_GATEWAY_URL],
                'Transaction 69 is pending, CPMS status is 824',
            ],
            [
                ['code' => CpmsHelper::PAYMENT_GATEWAY_REDIRECT_URL_RECEIVED],
                'Transaction 69 is pending, CPMS status is 825',
            ],
            [
                ['code' => CpmsHelper::PAYMENT_END_OF_FLOW_SIGNALLED],
                'Transaction 69 is pending, CPMS status is 826',
            ],
            [
                ['code' => CpmsHelper::PAYMENT_CARD_PAYMENT_CONFIRMED],
                'Transaction 69 is pending, CPMS status is 827',
            ],
            [
                ['code' => CpmsHelper::PAYMENT_ACTIVELY_BEING_TAKEN],
                'Transaction 69 is pending, CPMS status is 830',
            ],
            [
                ['code' => 'FooBar', 'message' => 'some message'],
                'Unexpected status received from CPMS, transaction 69 status FooBar, message: some message'
            ],
        ];
    }

    public function testHandleCommandSuccessPartialPayment(): void
    {
        $paymentId = 69;
        $guid = 'OLCS-1234-ABCDE';
        $amount = '1234.56';

        $data = [
            'id' => $paymentId,
            'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
        ];

        $fee = $this->references[FeeEntity::class][22];
        $fee->setGrossAmount($amount);
        $fee->shouldReceive('getOutstandingAmount')
            ->andReturn('10.00');

        $payment = m::mock(PaymentEntity::class)->makePartial();
        $payment->setId($paymentId);
        $payment->setReference($guid);
        $payment->setFeeTransactions($this->references[FeePaymentEntity::class]);
        $payment->setType($this->refData[PaymentEntity::TYPE_PAYMENT]);
        $payment
            ->shouldReceive('getFees')
            ->andReturn(['fee1', 'fee2'])
            ->once();
        $payment->setStatus($this->refData[PaymentEntity::STATUS_OUTSTANDING]);

        $command = Cmd::create($data);

        $this->repoMap['Transaction']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($payment);

        $this->mockCpmsService
            ->shouldReceive('getPaymentStatus')
            ->once()
            ->with($guid, 'fee1')
            ->andReturn(['code' => CpmsHelper::PAYMENT_SUCCESS]);

        $payment
            ->shouldReceive('setStatus')
            ->once()
            ->with($this->refData[PaymentEntity::STATUS_PAID])
            ->passthru()
            ->andReturnSelf()
            ->globally()
            ->ordered();

        $this->repoMap['Fee']
            ->shouldReceive('save')
            ->once()
            ->with($fee)
            ->globally()
            ->ordered();

        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->once()
            ->with($payment);

        $taskId = 987;
        $taskResult = new Result();
        $taskResult->addId('task', $taskId);
        $createTaskData = [
            'category' => Task::CATEGORY_LICENSING,
            'subCategory' => Task::SUBCATEGORY_LICENSING_GENERAL_TASK,
            'description' => Task::TASK_DESCRIPTION_FEE_DUE,
            'actionDate' => (new DateTime('now'))->format(\DateTime::W3C),
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

        $now = new DateTime();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($guid, $payment->getReference());
        $this->assertEquals($now->format('Y-m-d'), $payment->getCompletedDate()->format('Y-m-d'));

        $expected = [
            'id' => [
                'transaction' => 69,
                'task' => $taskId
            ],
            'messages' => [
                'Transaction 69 resolved as PAYMENT PAID',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandResolved(): void
    {
        $paymentId = 69;
        $data = [
            'id' => $paymentId,
            'paymentMethod' => FeeEntity::METHOD_CARD_ONLINE,
        ];
        $command = Cmd::create($data);
        $expected = [
            'id' => [
                'transaction' => 69,
            ],
            'messages' => [
                'Transaction is already resolved',
            ]
        ];
        $payment = m::mock(PaymentEntity::class)->makePartial();
        $payment->setId($paymentId);
        $payment->setStatus($this->refData[PaymentEntity::STATUS_PAID]);

        $this->repoMap['Transaction']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($payment);

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }
}
