<?php

declare(strict_types=1);

/**
 * Reverse Transaction Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Transaction;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Fee\ResetFees as ResetFeesCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Transaction\ReverseTransaction;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction as FeeTransactionEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as TransactionEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\CpmsHelperInterface as CpmsHelper;
use Dvsa\Olcs\Transfer\Command\Transaction\ReverseTransaction as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Reverse Transaction Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ReverseTransactionTest extends AbstractCommandHandlerTestCase
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

        $this->sut = new ReverseTransaction();
        $this->mockRepo('Fee', Repository\Fee::class);
        $this->mockRepo('Transaction', Repository\Transaction::class);

        /** @var UserEntity $mockUser */
        $mockUser = m::mock(UserEntity::class)
            ->shouldReceive('getLoginId')
            ->andReturn('bob')
            ->getMock();

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            TransactionEntity::STATUS_COMPLETE,
            FeeEntity::STATUS_OUTSTANDING => m::mock(RefData::class)
                ->makePartial()
                ->shouldReceive('getDescription')
                ->andReturn('Outstanding')
                ->getMock(),
            FeeEntity::METHOD_REVERSAL,
            FeeEntity::METHOD_CHEQUE,
            FeeEntity::METHOD_CASH,
            FeeEntity::METHOD_POSTAL_ORDER,
            FeeEntity::METHOD_CARD_ONLINE,
            FeeEntity::METHOD_CARD_OFFLINE,
        ];

        $this->references = [
            TransactionEntity::class => [
                123 => m::mock(TransactionEntity::class),
            ],
            FeeEntity::class => [
                69 => m::mock(FeeEntity::class),
            ],
            FeeTransactionEntity::class => [
                101 => m::mock(FeeTransactionEntity::class),
            ],
        ];

        parent::initReferences();
    }

    /**
     * @param string $paymentMethod
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandProvider')]
    public function testHandleCommand(mixed $paymentMethod): void
    {
        $now = new DateTime();
        $transactionId = 123;
        $transactionReference = 'MY-REFERENCE';

        $data = [
            'id' => $transactionId,
            'reason' => 'reversal reason',
            'customerReference' => 'foo',
            'customerName' => 'bar',
            'address' => 'cake'
        ];
        $command = Cmd::create($data);

        $transaction = $this->mapReference(TransactionEntity::class, $transactionId);
        $fee = $this->mapReference(FeeEntity::class, 69);

        $transaction
            ->shouldReceive('getFeeTransactions->first->getFee')
            ->andReturn($fee);

        $transaction
            ->shouldReceive('getPaymentMethod')
            ->andReturn($this->mapRefData($paymentMethod))
            ->shouldReceive('getReference')
            ->andReturn($transactionReference)
            ->shouldReceive('isComplete')
            ->andReturn(true)
            ->shouldReceive('canReverse')
            ->andReturn(true)
            ->shouldReceive('getChequePoNumber')
            ->andReturn('1234')
            ->shouldReceive('getChequePoDate')
            ->andReturn(new DateTime('2015-12-01'))
            ->shouldReceive('getPayingInSlipNumber')
            ->andReturn('2345')
            ->shouldReceive('getPayerName')
            ->andReturn('Bob');

        $fee
            ->shouldReceive('isBalancingFee')
            ->andReturn(false);

        $ft1 = $this->mapReference(FeeTransactionEntity::class, 101);
        $ft1
            ->shouldReceive('getFee')
            ->andReturn($fee)
            ->shouldReceive('getAmount')
            ->andReturn('10.00');
        $transaction
            ->shouldReceive('getFeeTransactionsForReversal')
            ->once()
            ->andReturn([$ft1]);

        $this->repoMap['Transaction']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($transaction);

        $this->mockCpmsService
            ->shouldReceive('reversePayment')
            ->once()
            ->with(
                $transactionReference,
                $paymentMethod,
                [$fee],
                [
                    'customer_name' => 'bar',
                    'customer_reference' => 'foo',
                    'customer_address' => 'cake'
                ]
            )
            ->andReturn(
                [
                    'receipt_reference' => 'REFUND_REF_1',
                    'code' => CpmsHelper::PAYMENT_PAYMENT_CHARGED_BACK,
                    'message' => 'ok',
                ]
            );

        $savedTransaction = null;
        $this->repoMap['Transaction']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(TransactionEntity::class))
            ->andReturnUsing(
                function ($txn) use (&$savedTransaction) {
                    $savedTransaction = $txn;
                    $savedTransaction->setId(999);
                    return true;
                }
            );

        $this->expectedSideEffect(
            ResetFeesCmd::class,
            [
                'fees' => [69 => $fee]
            ],
            (new Result())->addMessage('Fee 69 reset to Outstanding')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'transaction' => 999,
            ],
            'messages' => [
                "Transaction 123 reversed",
                'Transaction record created',
                'Fee 69 reset to Outstanding',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($this->mapRefData(TransactionEntity::TYPE_REVERSAL), $savedTransaction->getType());
        $this->assertSame($this->mapRefData(TransactionEntity::STATUS_COMPLETE), $savedTransaction->getStatus());
        $this->assertEquals('reversal reason', $savedTransaction->getComment());
        $this->assertEquals($now, $savedTransaction->getCompletedDate());
        $this->assertEquals('bob', $savedTransaction->getProcessedByUser()->getLoginId());
        $this->assertEquals('REFUND_REF_1', $savedTransaction->getReference());

        // assert data copied from original transaction
        $this->assertSame($this->mapRefData($paymentMethod), $savedTransaction->getPaymentMethod());
        $this->assertSame('1234', $savedTransaction->getChequePoNumber());
        $this->assertSame('2015-12-01', $savedTransaction->getChequePoDate()->format('Y-m-d'));
        $this->assertSame('2345', $savedTransaction->getPayingInSlipNumber());
        $this->assertSame('Bob', $savedTransaction->getPayerName());
    }

    public static function handleCommandProvider(): array
    {
        return [
            'cheque' => [FeeEntity::METHOD_CHEQUE],
            'cash' => [FeeEntity::METHOD_CASH],
            'po' => [FeeEntity::METHOD_POSTAL_ORDER],
            'digital card' => [FeeEntity::METHOD_CARD_ONLINE],
            'assisted digital card' => [FeeEntity::METHOD_CARD_OFFLINE],
        ];
    }

    public function testHandleCommandCpmsResponseException(): void
    {
        $transactionId = 123;

        $command = Cmd::create(
            [
                'id' => $transactionId,
                'reason' => 'foo',
                'customerReference' => null,
                'customerName' => null,
                'address' => null
            ]
        );

        $transaction = $this->mapReference(TransactionEntity::class, $transactionId);
        $fee = $this->mapReference(FeeEntity::class, 69);

        $transaction
            ->shouldReceive('getPaymentMethod')
            ->andReturn($this->mapRefData(FeeEntity::METHOD_CHEQUE));
        $transaction
            ->shouldReceive('getFeeTransactions->first->getFee')
            ->andReturn($fee);
        $transaction
            ->shouldReceive('getReference')
            ->andReturn('MY-REFERENCE');
        $transaction
            ->shouldReceive('isComplete')
            ->andReturn(true)
            ->shouldReceive('canReverse')
            ->andReturn(true);
        $this->repoMap['Transaction']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($transaction);

        $this->mockCpmsService
            ->shouldReceive('reversePayment')
            ->once()
            ->with('MY-REFERENCE', FeeEntity::METHOD_CHEQUE, [$fee], [])
            ->andThrow(new \Dvsa\Olcs\Api\Service\CpmsResponseException('ohnoes'));

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);

        $this->sut->handleCommand($command);
    }

    public function testValidateIncompleteTransaction(): void
    {
        $transaction = m::mock(TransactionEntity::class)
            ->shouldReceive('isComplete')
            ->andReturn(false)
            ->getMock();

        $this->expectException(ValidationException::class);

        $this->sut->validate($transaction);
    }

    public function testValidateIrreversibleTransaction(): void
    {
        $transaction = m::mock(TransactionEntity::class)
            ->shouldReceive('isComplete')
            ->andReturn(true)
            ->shouldReceive('canReverse')
            ->andReturn(false)
            ->getMock();

        $this->expectException(ValidationException::class);

        $this->sut->validate($transaction);
    }
}
