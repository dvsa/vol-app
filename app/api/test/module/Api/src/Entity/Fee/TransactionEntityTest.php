<?php

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

/**
 * @covers Dvsa\Olcs\Api\Entity\Fee\Transaction
 * @covers Dvsa\Olcs\Api\Entity\Fee\AbstractTransaction
 */
class TransactionEntityTest extends EntityTester
{
    public const FEE_1_ID = 9001;
    public const FEE_2_ID = 9002;
    public const FEE_3_ID = 9003;

    public const ORG_1_ID = 8001;
    public const TRANSACTION_1_ID = 70001;
    public const TRANSACTION_2_ID = 70002;

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /** @var  Entity */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Entity();
    }

    public function testGetCollections()
    {
        $sut = $this->instantiate($this->entityClass);

        $feeTransactions = $sut->getFeeTransactions();

        $this->assertInstanceOf(\Doctrine\Common\Collections\ArrayCollection::class, $feeTransactions);
    }

    /**
     * @dataProvider isOutstandingProvider
     */
    public function testIsOutstanding($status, $expected)
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
     * @return array
     */
    public function isOutstandingProvider()
    {
        return [
            [Entity::STATUS_OUTSTANDING, true],
            [Entity::STATUS_CANCELLED, false],
            [Entity::STATUS_FAILED, false],
            [Entity::STATUS_PAID, false],
            ['invalid', false],
        ];
    }

    /**
     * @dataProvider isPaidProvider
     */
    public function testIsPaid($status, $expected)
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

    /**
     * @dataProvider isPaidProvider
     */
    public function testIsComplete($status, $expected)
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
     * @return array
     */
    public function isPaidProvider()
    {
        return [
            [Entity::STATUS_OUTSTANDING, false],
            [Entity::STATUS_CANCELLED, false],
            [Entity::STATUS_FAILED, false],
            [Entity::STATUS_PAID, true],
            [Entity::STATUS_COMPLETE, true],
            ['invalid', false],
        ];
    }

    /**
     * implicitly tests getTotalAmount() as well
     */
    public function testGetCalculatedBundleValues()
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

    /**
     * @dataProvider dpTestDisplayReversalOption
     */
    public function testDisplayReversalOption($isMigrated, $isCompletePaymentOrAdjustment, $expect)
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('isMigrated')->once()->andReturn($isMigrated)
            ->shouldReceive('isCompletePaymentOrAdjustment')
            ->times($isMigrated ? 0 : 1)
            ->andReturn($isCompletePaymentOrAdjustment)
            ->getMock();

        static::assertSame($expect, $sut->displayReversalOption());
    }

    public function dpTestDisplayReversalOption()
    {
        return [
            [
                'isMigrated' => true,
                'isCompletePaymentOrAdjustment' => false,
                'expect' => false,
            ],
            [
                'isMigrated' => false,
                'isCompletePaymentOrAdjustment' => true,
                'expect' => true,
            ],
        ];
    }

    /**
     * @dataProvider dpTestIsMirgated
     */
    public function testIsMirgated($paymentMethod, $legacyStatus, $expect)
    {
        $this->sut->setPaymentMethod($paymentMethod);
        $this->sut->setLegacyStatus($legacyStatus);

        static::assertSame($expect, $this->sut->isMigrated());
    }

    public function dpTestIsMirgated()
    {
        return [
            [
                'paymentMethod' => new RefData(Fee::METHOD_MIGRATED),
                'legacyStatus' => null,
                'expect' => true,
            ],
            [
                new RefData(Fee::METHOD_CHEQUE),
                1,
                true,
            ],
            [
                new RefData(Fee::METHOD_CHEQUE),
                null,
                false,
            ],
        ];
    }

    /**
     * @dataProvider dpTestIsCompletePaymentOrAdjustment
     */
    public function testIsCompletePaymentOrAdjustment($isPayment, $isAdjustment, $isComplete, $expect)
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('isPayment')->once()->andReturn($isPayment)
            ->shouldReceive('isAdjustment')->times($isPayment ? 0 : 1)->andReturn($isAdjustment)
            ->shouldReceive('isComplete')->once()->andReturn($isComplete)
            ->getMock();

        static::assertSame($expect, $sut->isCompletePaymentOrAdjustment());
    }

    public function dpTestIsCompletePaymentOrAdjustment()
    {
        return [
            [
                'isPayment' => true,
                'isAdjustment' => false,
                'isComplete' => true,
                'expect' => true,
            ],
            [
                'isPayment' => false,
                'isAdjustment' => true,
                'isComplete' => true,
                'expect' => true,
            ],
            [
                'isPayment' => true,
                'isAdjustment' => true,
                'isComplete' => false,
                'expect' => false,
            ],
        ];
    }

    /**
     * @dataProvider dpTestCanReverse
     */
    public function testCanReverse($displayReversalOption, $isReversed, $expect)
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)->makePartial()
            ->shouldReceive('displayReversalOption')->once()->andReturn($displayReversalOption)
            ->shouldReceive('isReversed')->times($isReversed === null ? 0 : 1)->andReturn($isReversed)
            ->getMock();

        static::assertSame($expect, $sut->canReverse());
    }

    public function dpTestCanReverse()
    {
        return [
            [
                'displayReversalOption' => false,
                'isReversed' => null,
                'expect' => false,
            ],
            [
                'displayReversalOption' => true,
                'isReversed' => true,
                'expect' => false,
            ],
            [
                'displayReversalOption' => true,
                'isReversed' => false,
                'expect' => true,
            ],
        ];
    }

    /**
     * @dataProvider dpTestIsReserved
     */
    public function testIsReserved($transactions, $expect)
    {
        $this->sut->setFeeTransactions(new ArrayCollection($transactions));

        static::assertSame($expect, $this->sut->isReversed());
    }

    public function dpTestIsReserved()
    {
        return [
            'no fee transactions' => [
                [],
                false,
            ],
            'one refunded fee transaction' => [
                [
                    m::mock(FeeTransaction::class)
                        ->shouldReceive('isRefundedOrReversed')
                        ->andReturn(true)
                        ->getMock(),
                ],
                true,
            ],
            'one other fee transaction' => [
                [
                    m::mock(FeeTransaction::class)
                        ->shouldReceive('isRefundedOrReversed')
                        ->andReturn(false)
                        ->getMock(),
                ],
                false,
            ],
            'mix of fee transactions' => [
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
            ],
        ];
    }

    public function testGetAdjustmentHelperMethods()
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

    public function testGetFeeTransactionIds()
    {
        $this->sut->setFeeTransactions(
            new ArrayCollection(
                [
                    (new FeeTransaction())
                        ->setId(self::TRANSACTION_1_ID),
                    (new FeeTransaction())
                        ->setId(self::TRANSACTION_2_ID),
                ]
            )
        );

        static::assertEquals(
            [self::TRANSACTION_1_ID, self::TRANSACTION_2_ID],
            $this->sut->getFeeTransactionIds()
        );
    }

    public function testIsWaive()
    {
        $this->sut->setType(new RefData('NOT_WAIVE'));
        static::assertFalse($this->sut->isWaive());

        $this->sut->setType(new RefData(Transaction::TYPE_WAIVE));
        static::assertTrue($this->sut->isWaive());
    }

    public function testisPayment()
    {
        $this->sut->setType(new RefData('NOT_PAYMENT'));
        static::assertFalse($this->sut->isPayment());

        $this->sut->setType(new RefData(Transaction::TYPE_PAYMENT));
        static::assertTrue($this->sut->isPayment());
    }

    public function testIsAdjustment()
    {
        $this->sut->setType(new RefData('NOT_ADJUSTMENT'));
        static::assertFalse($this->sut->isAdjustment());

        $this->sut->setType(new RefData(Transaction::TYPE_ADJUSTMENT));
        static::assertTrue($this->sut->isAdjustment());
    }

    public function testIsReversal()
    {
        $this->sut->setType(new RefData('NOT_REVERSAL'));
        static::assertFalse($this->sut->isReversal());

        $this->sut->setType(new RefData(Transaction::TYPE_REVERSAL));
        static::assertTrue($this->sut->isReversal());
    }

    public function testGetFeeTransactionsForReversal()
    {
        $ft1 = (new FeeTransaction())
            ->setReversedFeeTransaction(new FeeTransaction());

        $ft2 = (new FeeTransaction())
            ->setReversingFeeTransactions(new ArrayCollection([new FeeTransaction()]));

        $ft3 = (new FeeTransaction());

        $this->sut->setFeeTransactions(
            new ArrayCollection([$ft1, $ft2, $ft3])
        );

        static::assertEquals(
            [$ft3],
            $this->sut->getFeeTransactionsForReversal()
        );
    }

    public function testGetFeeTransactionsForAdjustment()
    {
        $ft1 = (new FeeTransaction())
            ->setReversedFeeTransaction(new FeeTransaction());

        $ft2 = new FeeTransaction();

        $this->sut->setFeeTransactions(
            new ArrayCollection([$ft1, $ft2])
        );

        static::assertEquals(
            [$ft2],
            $this->sut->getFeeTransactionsForAdjustment()
        );
    }

    public function testGetProcessedByFullName()
    {
        //  check is person
        $person = (new Entities\Person\Person())
            ->setForename('unit_ForeName')
            ->setFamilyName('unit_FamilyName');

        $contactDetails = (new Entities\ContactDetails\ContactDetails(new RefData(null)))
            ->setPerson($person);

        $user = (new Entities\User\User(999, null))
            ->setContactDetails($contactDetails);

        $this->sut->setProcessedByUser($user);

        static::assertEquals('unit_ForeName unit_FamilyName', $this->sut->getProcessedByFullName());

        //  check on null
        $this->sut->setProcessedByUser(null);

        static::assertNull($this->sut->getProcessedByFullName());
    }

    public function testGetProcessedByFullNameNoPerson()
    {
        $contactDetails = (new Entities\ContactDetails\ContactDetails(new RefData(null)))->setPerson(null);
        $user = (new Entities\User\User(999, null))->setContactDetails($contactDetails);
        $user->setLoginId('foo');

        $this->sut->setProcessedByUser($user);

        $this->assertEquals('foo', $this->sut->getProcessedByFullName());
    }

    public function testGetFees()
    {
        $fee = (new Fee(new FeeType(), null, new RefData()))
            ->setId(self::FEE_1_ID);

        $ft = (new FeeTransaction())
            ->setFee($fee);

        $this->sut->setFeeTransactions(
            new ArrayCollection([$ft])
        );

        static::assertEquals(
            [
                self::FEE_1_ID => $fee,
            ],
            $this->sut->getFees()
        );
    }

    /**
     * @dataProvider dpTestGetPreviousTransaction
     */
    public function testGetPreviousTransaction($transType, $expect)
    {
        $transaction = new Transaction();

        $ftPrev = (new FeeTransaction())
            ->setTransaction($transaction);

        $this->sut->setFeeTransactions(
            new ArrayCollection(
                [
                    (new FeeTransaction())
                        ->setReversedFeeTransaction(null),
                    (new FeeTransaction())
                        ->setReversedFeeTransaction($ftPrev),
                ]
            )
        );

        //  check is Reversal
        $actual = $this->sut
            ->setType($transType)
            ->getPreviousTransaction();

        if ($expect === true) {
            static::assertSame($transaction, $actual);
        } else {
            static::assertNull($actual);
        }
    }

    public function dpTestGetPreviousTransaction()
    {
        return [
            [
                'type' => new RefData(Transaction::TYPE_REVERSAL),
                'expect' => true,
            ],
            [
                'type' => new RefData(Transaction::TYPE_ADJUSTMENT),
                'expect' => true,
            ],
            [
                'type' => new RefData('TYPE_INVALID'),
                'expect' => null,
            ],
        ];
    }

    public function testGetAmountAllocatedToFeeId()
    {
        //  check condition - Fee Id match but has Reversed transaction
        $ft1 = (new FeeTransaction())
            ->setFee(
                (new Fee(new FeeType(), null, new RefData()))
                    ->setId(self::FEE_1_ID)
            )
            ->setReversedFeeTransaction(new FeeTransaction())
            ->setAmount(7);

        //  check condition - Fee Id Not match and not has Reversed transaction
        $ft2 = (new FeeTransaction())
            ->setFee(
                (new Fee(new FeeType(), null, new RefData()))
                    ->setId(self::FEE_2_ID)
            )
            ->setReversedFeeTransaction(null)
            ->setAmount(11);

        //  check condition - Fee Id is match and not has Reversed transation
        $ft3 = (new FeeTransaction())
            ->setFee(
                (new Fee(new FeeType(), null, new RefData()))
                    ->setId(self::FEE_1_ID)
            )
            ->setReversedFeeTransaction(null)
            ->setAmount(13);

        $this->sut->setFeeTransactions(
            new ArrayCollection([$ft1, $ft2, $ft3])
        );

        //  check is Reversal
        static::assertEquals(13, $this->sut->getAmountAllocatedToFeeId(self::FEE_1_ID));
    }

    public function testGetRelatedOrganisation()
    {
        $org = (new Entities\Organisation\Organisation())
            ->setId(self::ORG_1_ID);

        /** @var Fee $fee */
        $fee = m::mock(Fee::class)
            ->shouldReceive('getId')->once()->andReturn(self::FEE_1_ID)
            ->shouldReceive('getRelatedOrganisation')->times(2)->andReturn($org)
            ->getMock();

        $ft = (new FeeTransaction())
            ->setFee($fee);

        $this->sut->setFeeTransactions(
            new ArrayCollection([$ft])
        );

        //  check is Reversal
        static::assertEquals(
            [
                self::ORG_1_ID => $org,
            ],
            $this->sut->getRelatedOrganisation()
        );
    }
}
