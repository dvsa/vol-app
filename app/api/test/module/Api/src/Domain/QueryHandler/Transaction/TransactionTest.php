<?php

declare(strict_types=1);

/**
 * Transaction Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Transaction;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\QueryHandler\Transaction\Transaction as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Transaction as PaymentRepo;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as TransactionEntity;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Transaction Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransactionTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Transaction', PaymentRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 69]);

        $mockTransaction = m::mock(TransactionEntity::class);

        $ft1 = m::mock(FeeTransaction::class)
            ->shouldReceive('getFee')
            ->andReturn($this->getMockFee('1', '12.34'))
            ->shouldReceive('getAmount')
            ->andReturn('2.34')
            ->getMock();

        $ft2 = m::mock(FeeTransaction::class)
            ->shouldReceive('getFee')
            ->andReturn($this->getMockFee('2', '23.45'))
            ->shouldReceive('getAmount')
            ->andReturn('3.45')
            ->shouldReceive('getReversingFeeTransactions')
            ->andReturn([])
            ->getMock();

        $reversal = m::mock(FeeTransaction::class);
        $reversal->shouldReceive('getTransaction->getId')->andReturn(999);
        $reversal->shouldReceive('getTransaction->getType->getId')->andReturn('trt_reversal');
        $ft1
            ->shouldReceive('getReversingFeeTransactions')
            ->andReturn(new ArrayCollection([$reversal]));

        $feeTransactions = new ArrayCollection([$ft1, $ft2]);

        $mockTransaction
            ->shouldReceive('getFeeTransactions')
            ->andReturn($feeTransactions)
            ->shouldReceive('getPreviousTransaction')
            ->andReturn(null);

        $this->repoMap['Transaction']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockTransaction);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $mockTransaction
            ->shouldReceive('serialize')
            ->andReturn(
                [
                    'id' => 99,
                    'foo' => 'bar',
                ]
            );

        $this->assertEquals(
            [
                'id' => 99,
                'foo' => 'bar',
                'fees' => [
                    '1' => [
                        'id' => '1',
                        'amount' => '12.34',
                        'allocatedAmount' => '2.34',
                        'reversingTransaction' => [
                            'id' => 999,
                            'type' => 'trt_reversal',
                        ],
                    ],
                    '2' => [
                        'id' => '2',
                        'amount' => '23.45',
                        'allocatedAmount' => '3.45',
                        'reversingTransaction' => null,
                    ]
                ],
                'previousTransactionId' => null,
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryRefundMultiplePayments(): void
    {
        $query = Qry::create(['id' => 69]);

        $mockTransaction = m::mock(TransactionEntity::class);

        $mockFee = $this->getMockFee('1', '12.34');

        $ft1 = m::mock(FeeTransaction::class)
            ->shouldReceive('getFee')
            ->andReturn($mockFee)
            ->shouldReceive('getAmount')
            ->andReturn('-2.34')
            ->shouldReceive('getReversingFeeTransactions')
            ->andReturn([])
            ->getMock();
        $ft2 = m::mock(FeeTransaction::class)
            ->shouldReceive('getFee')
            ->andReturn($mockFee)
            ->shouldReceive('getAmount')
            ->andReturn('-10.00')
            ->shouldReceive('getReversingFeeTransactions')
            ->andReturn([])
            ->getMock();

        $feeTransactions = new ArrayCollection([$ft1, $ft2]);

        $mockTransaction
            ->shouldReceive('getFeeTransactions')
            ->andReturn($feeTransactions)
            ->shouldReceive('getPreviousTransaction')
            ->andReturn(
                m::mock(TransactionEntity::class)
                    ->shouldReceive('getId')
                    ->andReturn('999')
                    ->getMock()
            );

        $this->repoMap['Transaction']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockTransaction);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $mockTransaction
            ->shouldReceive('serialize')
            ->andReturn(
                [
                    'id' => 99,
                    'foo' => 'bar',
                ]
            );

        $this->assertEquals(
            [
                'id' => 99,
                'foo' => 'bar',
                'fees' => [
                    '1' => [
                        'id' => '1',
                        'amount' => '12.34',
                        'allocatedAmount' => '-12.34',
                        'reversingTransaction' => null,
                    ],
                ],
                'previousTransactionId' => 999,
            ],
            $result->serialize()
        );
    }

    private function getMockFee(mixed $id, mixed $amount): mixed
    {

        $fee = m::mock(Fee::class)
            ->shouldReceive('getAmount')
            ->andReturn($amount)
            ->shouldReceive('serialize')
            ->andReturn(
                [
                    'id' => $id,
                    'amount' => $amount,
                ]
            )
            ->getMock();

        return $fee;
    }
}
