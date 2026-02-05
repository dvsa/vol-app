<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Fee;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Fee\Fee as QueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction as FeeTransactionEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as TransactionEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\Fee\Fee as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Fee Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Fee', FeeRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 69]);

        $mockFee = m::mock(FeeEntity::class);

        $this->repoMap['Fee']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockFee);

        $mockFee
            ->shouldReceive('allowEdit')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getLatestPaymentRef')
            ->once()
            ->andReturn('TestReceiptNo')
            ->shouldReceive('getPaymentMethod')
            ->once()
            ->andReturn('TestPaymentMethod')
            ->shouldReceive('getProcessedBy')
            ->once()
            ->andReturn('TestProcessedBy')
            ->shouldReceive('getPayer')
            ->once()
            ->andReturn('TestPayer')
            ->shouldReceive('getSlipNo')
            ->once()
            ->andReturn('TestSlipNo')
            ->shouldReceive('getChequePoNumber')
            ->once()
            ->andReturn('TestChequePoNumber')
            ->shouldReceive('getWaiveReason')
            ->once()
            ->andReturn('TestWaiveReason')
            ->shouldReceive('getOutstandingAmount')
            ->once()
            ->andReturn('TestOutstanding')
            ->shouldReceive('getCustomerReference')
            ->once()
            ->andReturn(111)
            ->shouldReceive('getOutstandingWaiveTransaction')
            ->andReturn(null)
            ->shouldReceive('canRefund')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getExpiryDate')
                    ->andReturn('2017-01-01')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $created = new DateTime('2015-10-29');
        $completed = new DateTime('2015-10-30');
        $status = new RefData(TransactionEntity::STATUS_COMPLETE);
        $ft1 = $this->getMockFeeTransaction(5, $created, $completed, $status, '10.00', 'Payment', 'Cash');
        $ft2 = $this->getMockFeeTransaction(6, $created, $completed, $status, '20.00', 'Payment', 'Cash');
        $ft3 = $this->getMockFeeTransaction(7, $created, $completed, $status, '-10.00', 'Refund', 'Refund');
        $ft4 = $this->getMockFeeTransaction(
            8,
            $created,
            $completed,
            $status,
            '-20.00',
            null,
            null,
            $ft3->getTransaction()
        );
        $ft5 = m::mock(FeeTransactionEntity::class)
            ->shouldReceive('getTransaction')
            ->andReturn(
                m::mock(TransactionEntity::class)
                    ->shouldReceive('getId')
                    ->andReturn(9)
                    ->shouldReceive('isOutstanding')
                    ->andReturn(true)
                    ->shouldReceive('isWaive')
                    ->andReturn(true)
                    ->shouldReceive('isMigrated')
                    ->andReturn(true)
                    ->getMock()
            )
            ->getMock();

        $mockFee
            ->shouldReceive('getFeeTransactions')
            ->andReturn(new ArrayCollection([$ft1, $ft2, $ft3, $ft4, $ft5]));

        $mockFee
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock(FeeTypeEntity::class)
                    ->shouldReceive('getVatRate')
                    ->andReturn('20.00')
                    ->shouldReceive('getVatCode')
                    ->andReturn('S')
                    ->getMock()
            );

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $mockFee
            ->shouldReceive('serialize')
            ->once()->andReturn(['id' => 69]);

        $expected = [
            'id' => '69',
            'allowEdit' => true,
            'receiptNo' => 'TestReceiptNo',
            'paymentMethod' => 'TestPaymentMethod',
            'processedBy' => 'TestProcessedBy',
            'payer' => 'TestPayer',
            'slipNo' => 'TestSlipNo',
            'chequePoNumber' => 'TestChequePoNumber',
            'waiveReason' => 'TestWaiveReason',
            'outstanding' => 'TestOutstanding',
            'hasOutstandingWaiveTransaction' => false,
            'canRefund' => true,
            'displayTransactions' => [
                5 => [
                    'transactionId' => 5,
                    'type' => 'Payment',
                    'createdOn' => $created,
                    'completedDate' => $completed,
                    'method' => 'Cash DISPLAY_AMOUNT',
                    'processedBy' => 'someuser',
                    'amount' => '10.00',
                    'status' => $status,
                    'migratedFromOlbs' => false,
                ],
                6 => [
                    'transactionId' => 6,
                    'type' => 'Payment',
                    'createdOn' => $created,
                    'completedDate' => $completed,
                    'method' => 'Cash DISPLAY_AMOUNT',
                    'processedBy' => 'someuser',
                    'amount' => '20.00',
                    'status' => $status,
                    'migratedFromOlbs' => false,
                ],
                // multiple feeTransaction records with the same transaction id
                // should group for display, summing the amounts
                7 => [
                    'transactionId' => 7,
                    'type' => 'Refund',
                    'createdOn' => $created,
                    'completedDate' => $completed,
                    'method' => 'Refund DISPLAY_AMOUNT',
                    'processedBy' => 'someuser',
                    'amount' => '-30.00',
                    'status' => $status,
                    'migratedFromOlbs' => false,
                ],
            ],
            'vatInfo' => '20% (S)',
            'licenceExpiryDate' => '2017-01-01',
            'customerReference' => 111,
        ];

        $this->assertEquals($expected, $result->serialize());
    }

    /**
     * Helper method to get a mock FeeTransaction record
     *
     * @param int               $id transaction id
     * @param DateTime          $created
     * @param DateTime          $completed
     * @param RefData           $status
     * @param string            $amount
     * @param string            $type (description)
     * @param string            $method (description)
     * @param TransactionEntity $transaction optional transaction reference
     *
     * @return m\Mock (FeeTransactionEntity)
     */
    private function getMockFeeTransaction(
        mixed $id,
        mixed $created,
        mixed $completed,
        mixed $status,
        mixed $amount,
        mixed $type,
        mixed $method,
        mixed $transaction = null
    ): m\MockInterface {
        if (is_null($transaction)) {
            $transaction = m::mock(TransactionEntity::class);
            $transaction
                ->shouldReceive('isMigrated')
                ->andReturn(false)
                ->shouldReceive('getId')
                ->andReturn($id)
                ->shouldReceive('isOutstanding')
                ->andReturn(false)
                ->shouldReceive('isWaive')
                ->andReturn(false)
                ->shouldReceive('getCreatedOn')
                ->andReturn($created)
                ->shouldReceive('getCompletedDate')
                ->andReturn($completed)
                ->shouldReceive('getStatus')
                ->andReturn($status);
            $transaction
                ->shouldReceive('getType->getDescription')
                ->andReturn($type);
            $transaction
                ->shouldReceive('getPaymentMethod->getDescription')
                ->andReturn($method);
            $transaction
                ->shouldReceive('getProcessedByFullName')
                ->andReturn('someuser');
            $transaction
                ->shouldReceive('getDisplayAmount')
                ->andReturn('DISPLAY_AMOUNT');
            $transaction
                ->shouldReceive('isAdjustment')
                ->andReturn(false);
        }

        $feeTransaction = m::mock(FeeTransactionEntity::class)
            ->shouldReceive('getTransaction')
            ->andReturn($transaction)
            ->shouldReceive('getAmount')
            ->andReturn($amount)
            ->getMock();

        return $feeTransaction;
    }
}
