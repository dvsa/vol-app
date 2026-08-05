<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Fee;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Fee\Transaction;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Fee\Transaction::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Fee\AbstractTransaction::class)]
final class TransactionEntityTest extends EntityTester
{
    public const int FEE_1_ID = 9001;
    public const int FEE_2_ID = 9002;
    public const int FEE_3_ID = 9003;

    public const int ORG_1_ID = 8001;
    public const int TRANSACTION_1_ID = 70001;
    public const int TRANSACTION_2_ID = 70002;

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /** @var  Entity */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new Entity();
    }

    public function testGetCollections(): void
    {
        $sut = $this->instantiate($this->entityClass);

        $feeTransactions = $sut->getFeeTransactions();

        $this->assertInstanceOf(\Doctrine\Common\Collections\ArrayCollection::class, $feeTransactions);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isOutstandingProvider')]
    public function testIsOutstanding(mixed $status, mixed $expected): void
    {
        /** @var RefData $status */
        $status = m::mock(RefData::class)
            ->shouldReceive('getId')
            ->once()
            ->andReturn($status)
            ->getMock();

        $this->sut->setStatus($status);

        $this->assertEquals($expected, $this->sut->isOutstanding());
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function isOutstandingProvider(): \Iterator
    {
        yield [Entity::STATUS_OUTSTANDING, true];
        yield [Entity::STATUS_CANCELLED, false];
        yield [Entity::STATUS_FAILED, false];
        yield [Entity::STATUS_PAID, false];
        yield ['invalid', false];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isPaidProvider')]
    public function testIsPaid(mixed $status, mixed $expected): void
    {
        /** @var RefData $status */
        $status = m::mock(RefData::class)
            ->shouldReceive('getId')
            ->once()
            ->andReturn($status)
            ->getMock();

        $this->sut->setStatus($status);

        $this->assertEquals($expected, $this->sut->isPaid());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isPaidProvider')]
    public function testIsComplete(mixed $status, mixed $expected): void
    {
        /** @var RefData $status */
        $status = m::mock(RefData::class)
            ->shouldReceive('getId')
            ->once()
            ->andReturn($status)
            ->getMock();

        $this->sut->setStatus($status);

        $this->assertEquals($expected, $this->sut->isComplete());
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function isPaidProvider(): \Iterator
    {
        yield [Entity::STATUS_OUTSTANDING, false];
        yield [Entity::STATUS_CANCELLED, false];
        yield [Entity::STATUS_FAILED, false];
        yield [Entity::STATUS_PAID, true];
        yield [Entity::STATUS_COMPLETE, true];
        yield ['invalid', false];
    }

    /**
     * implicitly tests getTotalAmount() as well
     */
    public function testGetCalculatedBundleValues(): void
    {
        $ft1 = m::mock(FeeTransaction::class)
            ->shouldReceive('getAmount')->times(3)->andReturn('12.34')
            ->shouldReceive('isRefundedOrReversed')->times(3)->andReturn(false)
            ->shouldReceive('getReversedFeeTransaction')->once()->andReturn(null)
            ->getMock();

        $ft2 = m::mock(FeeTransaction::class)
            ->shouldReceive('getAmount')->times(3)->andReturn('23.45')
            ->shouldReceive('isRefundedOrReversed')->times(3)->andReturn(false)
            ->shouldReceive('getReversedFeeTransaction')->once()->andReturn(null)
            ->getMock();

        $feeTransactions = new ArrayCollection([$ft1, $ft2]);
        $this->sut->setFeeTransactions($feeTransactions);

        $this->sut->setType(new RefData(Entity::TYPE_PAYMENT));
        $this->sut->setPaymentMethod(new RefData(Fee::METHOD_CASH));
        $this->sut->setStatus(new RefData(Entity::STATUS_COMPLETE));

        $this->assertEquals(
            [
                'amount' => '35.79',
                'displayReversalOption' => true,
                'canReverse' => true,
                'displayAdjustmentOption' => true,
                'canAdjust' => true,
                'displayAmount' => '£35.79',
                'amountAfterAdjustment' => '35.79',
            ],
            $this->sut->getCalculatedBundleValues()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestDisplayReversalOption')]
    public function testDisplayReversalOption(mixed $isMigrated, mixed $isCompletePaymentOrAdjustment, mixed $expect): void
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('isMigrated')->once()->andReturn($isMigrated)
            ->shouldReceive('isCompletePaymentOrAdjustment')
            ->times($isMigrated ? 0 : 1)
            ->andReturn($isCompletePaymentOrAdjustment)
            ->getMock();

        $this->assertSame($expect, $sut->displayReversalOption());
    }

    public static function dpTestDisplayReversalOption(): \Iterator
    {
        yield [
            'isMigrated' => true,
            'isCompletePaymentOrAdjustment' => false,
            'expect' => false,
        ];
        yield [
            'isMigrated' => false,
            'isCompletePaymentOrAdjustment' => true,
            'expect' => true,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsMirgated')]
    public function testIsMirgated(mixed $paymentMethod, mixed $legacyStatus, mixed $expect): void
    {
        $this->sut->setPaymentMethod($paymentMethod);
        $this->sut->setLegacyStatus($legacyStatus);

        $this->assertSame($expect, $this->sut->isMigrated());
    }

    public static function dpTestIsMirgated(): \Iterator
    {
        yield [
            'paymentMethod' => new RefData(Fee::METHOD_MIGRATED),
            'legacyStatus' => null,
            'expect' => true,
        ];
        yield [
            new RefData(Fee::METHOD_CHEQUE),
            1,
            true,
        ];
        yield [
            new RefData(Fee::METHOD_CHEQUE),
            null,
            false,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsCompletePaymentOrAdjustment')]
    public function testIsCompletePaymentOrAdjustment(mixed $isPayment, mixed $isAdjustment, mixed $isComplete, mixed $expect): void
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('isPayment')->once()->andReturn($isPayment)
            ->shouldReceive('isAdjustment')->times($isPayment ? 0 : 1)->andReturn($isAdjustment)
            ->shouldReceive('isComplete')->once()->andReturn($isComplete)
            ->getMock();

        $this->assertSame($expect, $sut->isCompletePaymentOrAdjustment());
    }

    public static function dpTestIsCompletePaymentOrAdjustment(): \Iterator
    {
        yield [
            'isPayment' => true,
            'isAdjustment' => false,
            'isComplete' => true,
            'expect' => true,
        ];
        yield [
            'isPayment' => false,
            'isAdjustment' => true,
            'isComplete' => true,
            'expect' => true,
        ];
        yield [
            'isPayment' => true,
            'isAdjustment' => true,
            'isComplete' => false,
            'expect' => false,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCanReverse')]
    public function testCanReverse(mixed $displayReversalOption, mixed $isReversed, mixed $expect): void
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('displayReversalOption')->once()->andReturn($displayReversalOption)
            ->shouldReceive('isReversed')->times($isReversed === null ? 0 : 1)->andReturn($isReversed)
            ->getMock();

        $this->assertSame($expect, $sut->canReverse());
    }

    public static function dpTestCanReverse(): \Iterator
    {
        yield [
            'displayReversalOption' => false,
            'isReversed' => null,
            'expect' => false,
        ];
        yield [
            'displayReversalOption' => true,
            'isReversed' => true,
            'expect' => false,
        ];
        yield [
            'displayReversalOption' => true,
            'isReversed' => false,
            'expect' => true,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsReserved')]
    public function testIsReserved(mixed $transactions, mixed $expect): void
    {
        $this->sut->setFeeTransactions(new ArrayCollection($transactions));

        $this->assertSame($expect, $this->sut->isReversed());
    }

    public static function dpTestIsReserved(): \Iterator
    {
        yield 'no fee transactions' => [
            [],
            false,
        ];
        yield 'one refunded fee transaction' => [
            [
                m::mock(FeeTransaction::class)
                    ->shouldReceive('isRefundedOrReversed')
                    ->andReturn(true)
                    ->getMock(),
            ],
            true,
        ];
        yield 'one other fee transaction' => [
            [
                m::mock(FeeTransaction::class)
                    ->shouldReceive('isRefundedOrReversed')
                    ->andReturn(false)
                    ->getMock(),
            ],
            false,
        ];
        yield 'mix of fee transactions' => [
            [
                m::mock(FeeTransaction::class)
                    ->shouldReceive('isRefundedOrReversed')
                    ->andReturn(false)
                    ->getMock(),
                m::mock(FeeTransaction::class)
                    ->shouldReceive('isRefundedOrReversed')
                    ->andReturn(true)
                    ->getMock(),
            ],
            true,
        ];
    }

    public function testGetAdjustmentHelperMethods(): void
    {
        $ft1 = m::mock(FeeTransaction::class)
            ->shouldReceive('getAmount')->times(2)->andReturn('-10.00')
            ->shouldReceive('getReversedFeeTransaction')->times(4)->andReturn(new FeeTransaction())
            ->getMock();
        $ft2 = m::mock(FeeTransaction::class)
            ->shouldReceive('getAmount')->times(2)->andReturn('-5.00')
            ->shouldReceive('getReversedFeeTransaction')->times(4)->andReturn(new FeeTransaction())
            ->getMock();
        $ft3 = m::mock(FeeTransaction::class)
            ->shouldReceive('getAmount')->times(2)->andReturn('10.00')
            ->shouldReceive('getReversedFeeTransaction')->times(4)->andReturn(null)
            ->getMock();
        $ft4 = m::mock(FeeTransaction::class)
            ->shouldReceive('getAmount')->times(2)->andReturn('10.00')
            ->shouldReceive('getReversedFeeTransaction')->times(4)->andReturn(null)
            ->getMock();

        $feeTransactions = new ArrayCollection([$ft1, $ft2, $ft3, $ft4]);
        $this->sut->setFeeTransactions($feeTransactions);

        $this->sut->setType(new RefData(Entity::TYPE_ADJUSTMENT));
        $this->sut->setPaymentMethod(new RefData(Fee::METHOD_CASH));
        $this->sut->setStatus(new RefData(Entity::STATUS_COMPLETE));

        $this->assertEquals('15.00', $this->sut->getAmountBeforeAdjustment());
        $this->assertEquals('20.00', $this->sut->getAmountAfterAdjustment());
        $this->assertEquals('£15.00 to £20.00', $this->sut->getDisplayAmount());
    }

    public function testGetFeeTransactionIds(): void
    {
        $this->sut->setFeeTransactions(
            new ArrayCollection(
                [
                    new FeeTransaction()
                        ->setId(self::TRANSACTION_1_ID),
                    new FeeTransaction()
                        ->setId(self::TRANSACTION_2_ID),
                ]
            )
        );

        $this->assertEquals([self::TRANSACTION_1_ID, self::TRANSACTION_2_ID], $this->sut->getFeeTransactionIds());
    }

    public function testIsWaive(): void
    {
        $this->sut->setType(new RefData('NOT_WAIVE'));
        $this->assertFalse($this->sut->isWaive());

        $this->sut->setType(new RefData(Transaction::TYPE_WAIVE));
        $this->assertTrue($this->sut->isWaive());
    }

    public function testisPayment(): void
    {
        $this->sut->setType(new RefData('NOT_PAYMENT'));
        $this->assertFalse($this->sut->isPayment());

        $this->sut->setType(new RefData(Transaction::TYPE_PAYMENT));
        $this->assertTrue($this->sut->isPayment());
    }

    public function testIsAdjustment(): void
    {
        $this->sut->setType(new RefData('NOT_ADJUSTMENT'));
        $this->assertFalse($this->sut->isAdjustment());

        $this->sut->setType(new RefData(Transaction::TYPE_ADJUSTMENT));
        $this->assertTrue($this->sut->isAdjustment());
    }

    public function testIsReversal(): void
    {
        $this->sut->setType(new RefData('NOT_REVERSAL'));
        $this->assertFalse($this->sut->isReversal());

        $this->sut->setType(new RefData(Transaction::TYPE_REVERSAL));
        $this->assertTrue($this->sut->isReversal());
    }

    public function testGetFeeTransactionsForReversal(): void
    {
        $ft1 = new FeeTransaction()
            ->setReversedFeeTransaction(new FeeTransaction());

        $ft2 = new FeeTransaction()
            ->setReversingFeeTransactions(new ArrayCollection([new FeeTransaction()]));

        $ft3 = (new FeeTransaction());

        $this->sut->setFeeTransactions(
            new ArrayCollection([$ft1, $ft2, $ft3])
        );

        $this->assertEquals([$ft3], $this->sut->getFeeTransactionsForReversal());
    }

    public function testGetFeeTransactionsForAdjustment(): void
    {
        $ft1 = new FeeTransaction()
            ->setReversedFeeTransaction(new FeeTransaction());

        $ft2 = new FeeTransaction();

        $this->sut->setFeeTransactions(
            new ArrayCollection([$ft1, $ft2])
        );

        $this->assertEquals([$ft2], $this->sut->getFeeTransactionsForAdjustment());
    }

    public function testGetProcessedByFullName(): void
    {
        //  check is person
        $person = new Entities\Person\Person()
            ->setForename('unit_ForeName')
            ->setFamilyName('unit_FamilyName');

        $contactDetails = new Entities\ContactDetails\ContactDetails(new RefData(null))
            ->setPerson($person);

        $user = new Entities\User\User(999, null)
            ->setContactDetails($contactDetails);

        $this->sut->setProcessedByUser($user);

        $this->assertEquals('unit_ForeName unit_FamilyName', $this->sut->getProcessedByFullName());

        //  check on null
        $this->sut->setProcessedByUser(null);

        $this->assertNull($this->sut->getProcessedByFullName());
    }

    public function testGetProcessedByFullNameNoPerson(): void
    {
        $contactDetails = new Entities\ContactDetails\ContactDetails(new RefData(null))->setPerson(null);
        $user = new Entities\User\User(999, null)->setContactDetails($contactDetails);
        $user->setLoginId('foo');

        $this->sut->setProcessedByUser($user);

        $this->assertEquals('foo', $this->sut->getProcessedByFullName());
    }

    public function testGetFees(): void
    {
        $fee = new Fee(new FeeType(), null, new RefData())
            ->setId(self::FEE_1_ID);

        $ft = new FeeTransaction()
            ->setFee($fee);

        $this->sut->setFeeTransactions(
            new ArrayCollection([$ft])
        );

        $this->assertEquals([
            self::FEE_1_ID => $fee,
        ], $this->sut->getFees());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetPreviousTransaction')]
    public function testGetPreviousTransaction(mixed $transType, mixed $expect): void
    {
        $transaction = new Transaction();

        $ftPrev = new FeeTransaction()
            ->setTransaction($transaction);

        $this->sut->setFeeTransactions(
            new ArrayCollection(
                [
                    new FeeTransaction()
                        ->setReversedFeeTransaction(null),
                    new FeeTransaction()
                        ->setReversedFeeTransaction($ftPrev),
                ]
            )
        );

        //  check is Reversal
        $actual = $this->sut
            ->setType($transType)
            ->getPreviousTransaction();

        if ($expect === true) {
            $this->assertSame($transaction, $actual);
        } else {
            $this->assertNotInstanceOf(\Dvsa\Olcs\Api\Entity\Fee\Transaction::class, $actual);
        }
    }

    public static function dpTestGetPreviousTransaction(): \Iterator
    {
        yield [
            'transType' => new RefData(Transaction::TYPE_REVERSAL),
            'expect' => true,
        ];
        yield [
            'transType' => new RefData(Transaction::TYPE_ADJUSTMENT),
            'expect' => true,
        ];
        yield [
            'transType' => new RefData('TYPE_INVALID'),
            'expect' => null,
        ];
    }

    public function testGetAmountAllocatedToFeeId(): void
    {
        //  check condition - Fee Id match but has Reversed transaction
        $ft1 = new FeeTransaction()
            ->setFee(
                new Fee(new FeeType(), null, new RefData())
                    ->setId(self::FEE_1_ID)
            )
            ->setReversedFeeTransaction(new FeeTransaction())
            ->setAmount(7);

        //  check condition - Fee Id Not match and not has Reversed transaction
        $ft2 = new FeeTransaction()
            ->setFee(
                new Fee(new FeeType(), null, new RefData())
                    ->setId(self::FEE_2_ID)
            )
            ->setReversedFeeTransaction(null)
            ->setAmount(11);

        //  check condition - Fee Id is match and not has Reversed transation
        $ft3 = new FeeTransaction()
            ->setFee(
                new Fee(new FeeType(), null, new RefData())
                    ->setId(self::FEE_1_ID)
            )
            ->setReversedFeeTransaction(null)
            ->setAmount(13);

        $this->sut->setFeeTransactions(
            new ArrayCollection([$ft1, $ft2, $ft3])
        );

        //  check is Reversal
        $this->assertEquals(13, $this->sut->getAmountAllocatedToFeeId(self::FEE_1_ID));
    }

    public function testGetRelatedOrganisation(): void
    {
        $org = new Entities\Organisation\Organisation()
            ->setId(self::ORG_1_ID);

        /** @var Fee $fee */
        $fee = m::mock(Fee::class)
            ->shouldReceive('getId')->once()->andReturn(self::FEE_1_ID)
            ->shouldReceive('getRelatedOrganisation')->times(2)->andReturn($org)
            ->getMock();

        $ft = new FeeTransaction()
            ->setFee($fee);

        $this->sut->setFeeTransactions(
            new ArrayCollection([$ft])
        );

        //  check is Reversal
        $this->assertEquals([
            self::ORG_1_ID => $org,
        ], $this->sut->getRelatedOrganisation());
    }
}
