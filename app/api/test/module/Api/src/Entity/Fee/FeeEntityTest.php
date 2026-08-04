<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Fee;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Fee\Fee as Entity;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Fee\Transaction;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * Fee Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class FeeEntityTest extends EntityTester
{
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
        parent::setUp();

        $this->sut = $this->instantiate($this->entityClass);
    }

    public function testConstructor(): void
    {
        $type = new FeeType();
        $amount = '10.00';
        $status = new RefData(Entity::STATUS_OUTSTANDING);

        $fee = new Entity($type, $amount, $status);

        $this->assertSame($type, $fee->getFeeType());
        $this->assertSame($amount, $fee->getNetAmount());
        $this->assertSame($status, $fee->getFeeStatus());
    }

    /**
     * @param ArrayCollection $feeTransactions
     * @param boolean         $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('outstandingPaymentProvider')]
    public function testHadOutstandingPayment(mixed $feeTransactions, mixed $expected): void
    {
        $this->sut->setFeeTransactions($feeTransactions);

        $this->assertEquals($expected, $this->sut->hasOutstandingPayment());
    }

    public static function outstandingPaymentProvider(): \Iterator
    {
        yield 'no fee payments' => [
            [],
            false,
        ];
        yield 'one outstanding' => [
            [
                m::mock()
                    ->shouldReceive('getTransaction')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('isOutstanding')
                            ->andReturn(true)
                            ->getMock()
                    )
                    ->getMock()
            ],
            true,
        ];
    }

    public function testHadOutstandingPaymentExcludeWaiveNoPayments(): void
    {
        $pendingPaymentsTimeout = 3600;

        $this->sut->setFeeTransactions([]);

        $this->assertFalse($this->sut->hasOutstandingPaymentExcludeWaive($pendingPaymentsTimeout));
    }

    public function testHadOutstandingPaymentExcludeWaiveOutstandingNoWaives(): void
    {
        $pendingPaymentsTimeout = 3600;

        $feeTransaction = m::mock()
            ->shouldReceive('getTransaction')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isOutstanding')
                    ->andReturn(true)
                    ->shouldReceive('getType')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn(Transaction::TYPE_PAYMENT)
                            ->getMock()
                    )
                    ->shouldReceive('getCreatedOn')
                    ->andReturn(new DateTime('now'))
                    ->once()
                    ->getMock()
            )
            ->getMock();

        $this->sut->setFeeTransactions([$feeTransaction]);

        $this->assertTrue($this->sut->hasOutstandingPaymentExcludeWaive($pendingPaymentsTimeout));
    }

    public function testHadOutstandingPaymentExcludeWaiveOutstandingWithWaives(): void
    {
        $pendingPaymentsTimeout = 3600;

        $feeTransaction = m::mock()
            ->shouldReceive('getTransaction')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isOutstanding')
                    ->andReturn(true)
                    ->shouldReceive('getType')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn(Transaction::TYPE_WAIVE)
                            ->getMock()
                    )
                    ->shouldReceive('getCreatedOn')
                    ->andReturn(new DateTime('now'))
                    ->once()
                    ->getMock()
            )
            ->getMock();

        $this->sut->setFeeTransactions([$feeTransaction]);

        $this->assertFalse($this->sut->hasOutstandingPaymentExcludeWaive($pendingPaymentsTimeout));
    }

    public function testHadOutstandingPaymentExcludeWaiveOutstandingTimeoutReached(): void
    {
        $pendingPaymentsTimeout = 3600;

        $feeTransaction = m::mock()
            ->shouldReceive('getTransaction')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isOutstanding')
                    ->andReturn(true)
                    ->shouldReceive('getType')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn(Transaction::TYPE_PAYMENT)
                            ->getMock()
                    )
                    ->shouldReceive('getCreatedOn')
                    ->andReturn(new DateTime('2017-01-01'))
                    ->once()
                    ->getMock()
            )
            ->getMock();

        $this->sut->setFeeTransactions([$feeTransaction]);

        $this->assertFalse($this->sut->hasOutstandingPaymentExcludeWaive($pendingPaymentsTimeout));
    }

    /**
     * @param string $accrualRuleId,
     * @param Licence $licence
     * @param IrhpApplication $irhpApplication
     * @param DateTime $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('ruleStartDateProvider')]
    public function testGetRuleStartDate(mixed $accrualRuleId, mixed $licence, mixed $irhpApplication, mixed $expected): void
    {
        $feeType = m::mock()
            ->shouldReceive('getAccrualRule')
            ->andReturn(new RefData()->setId($accrualRuleId))
            ->getMock();

        $this->sut->setFeeType($feeType);
        if (!is_null($licence)) {
            $this->sut->setLicence($licence);
        }

        if (!is_null($irhpApplication)) {
            $this->sut->setIrhpApplication($irhpApplication);
        }

        $this->assertEquals($expected, $this->sut->getRuleStartDate());
    }

    public static function ruleStartDateProvider(): array
    {
        $now = new DateTime();
        $futureContinuationDate = new Datetime('4 years 10 days midnight');

        $irhpPermitStartDate = new DateTime('2015-04-04');

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication
            ->shouldReceive('getIrhpPermitApplications->first->getIrhpPermitWindow->getIrhpPermitStock->getValidFrom')
            ->with(true)
            ->andReturn($irhpPermitStartDate);

        $irhpApplicationWithoutIrhpPermitApp = m::mock(IrhpApplication::class);
        $irhpApplicationWithoutIrhpPermitApp
            ->shouldReceive('getIrhpPermitApplications->first')
            ->andReturn(null);

        return [
            'immediate' => [
                Entity::ACCRUAL_RULE_IMMEDIATE,
                null,
                null,
                $now,
            ],
            'licence start' => [
                Entity::ACCRUAL_RULE_LICENCE_START,
                m::mock()
                    ->shouldReceive('getInForceDate')
                    ->andReturn('2015-04-03')
                    ->getMock(),
                null,
                new DateTime('2015-04-03'),
            ],
            'licence start date missing' => [
                Entity::ACCRUAL_RULE_LICENCE_START,
                m::mock()
                    ->shouldReceive('getInForceDate')
                    ->andReturn(null)
                    ->getMock(),
                null,
                null,
            ],
            'continuation' => [
                Entity::ACCRUAL_RULE_CONTINUATION,
                m::mock()
                    ->shouldReceive('getExpiryDate')
                    ->andReturn('2015-04-03')
                    ->getMock(),
                null,
                new DateTime('2010-04-04'),
            ],
            'continuation date more than 4 year in future' => [
                Entity::ACCRUAL_RULE_CONTINUATION,
                m::mock()
                    ->shouldReceive('getExpiryDate')
                    ->andReturn($futureContinuationDate->format('Y-m-d'))
                    ->getMock(),
                null,
                $futureContinuationDate->sub(new \DateInterval('P5Y'))->add(new \DateInterval('P1D')),
            ],
            'continuation date missing' => [
                Entity::ACCRUAL_RULE_CONTINUATION,
                m::mock()
                    ->shouldReceive('getExpiryDate')
                    ->andReturn(null)
                    ->getMock(),
                null,
                null,
            ],
            'IRHP permit - 3 months - no application' => [
                Entity::ACCRUAL_RULE_IRHP_PERMIT_3_MONTHS,
                null,
                $irhpApplicationWithoutIrhpPermitApp,
                null,
            ],
            'IRHP permit - 3 months - valid from date' => [
                Entity::ACCRUAL_RULE_IRHP_PERMIT_3_MONTHS,
                null,
                $irhpApplication,
                $irhpPermitStartDate,
            ],
            'IRHP permit - 6 months - no application' => [
                Entity::ACCRUAL_RULE_IRHP_PERMIT_6_MONTHS,
                null,
                $irhpApplicationWithoutIrhpPermitApp,
                null,
            ],
            'IRHP permit - 6 months - valid from date' => [
                Entity::ACCRUAL_RULE_IRHP_PERMIT_6_MONTHS,
                null,
                $irhpApplication,
                $irhpPermitStartDate,
            ],
            'IRHP permit - 9 months - no application' => [
                Entity::ACCRUAL_RULE_IRHP_PERMIT_9_MONTHS,
                null,
                $irhpApplicationWithoutIrhpPermitApp,
                null,
            ],
            'IRHP permit - 9 months - valid from date' => [
                Entity::ACCRUAL_RULE_IRHP_PERMIT_9_MONTHS,
                null,
                $irhpApplication,
                $irhpPermitStartDate,
            ],
            'IRHP permit - 12 months - no application' => [
                Entity::ACCRUAL_RULE_IRHP_PERMIT_12_MONTHS,
                null,
                $irhpApplicationWithoutIrhpPermitApp,
                null,
            ],
            'IRHP permit - 12 months - valid from date' => [
                Entity::ACCRUAL_RULE_IRHP_PERMIT_12_MONTHS,
                null,
                $irhpApplication,
                $irhpPermitStartDate,
            ],
            'invalid' => [
                'foo',
                null,
                null,
                null,
            ],
        ];
    }

    /**
     * @param string $accrualRuleId,
     * @param int $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('defermentPeriodProvider')]
    public function testGetDefermentPeriod(mixed $accrualRuleId, mixed $expected): void
    {
        $feeType = m::mock()
            ->shouldReceive('getAccrualRule')
            ->andReturn(new RefData()->setId($accrualRuleId))
            ->getMock();

        $this->sut->setFeeType($feeType);

        $this->assertEquals($expected, $this->sut->getDefermentPeriod());
    }

    public static function defermentPeriodProvider(): \Iterator
    {
        yield 'immediate' => [
            Entity::ACCRUAL_RULE_IMMEDIATE,
            1
        ];
        yield 'licence start' => [
            Entity::ACCRUAL_RULE_LICENCE_START,
            60,
        ];
        yield 'continuation' => [
            Entity::ACCRUAL_RULE_CONTINUATION,
            60,
        ];
        yield 'IRHP permit - 3 months' => [
            Entity::ACCRUAL_RULE_IRHP_PERMIT_3_MONTHS,
            3,
        ];
        yield 'IRHP permit - 6 months' => [
            Entity::ACCRUAL_RULE_IRHP_PERMIT_6_MONTHS,
            6,
        ];
        yield 'IRHP permit - 9 months' => [
            Entity::ACCRUAL_RULE_IRHP_PERMIT_9_MONTHS,
            9,
        ];
        yield 'IRHP permit - 12 months' => [
            Entity::ACCRUAL_RULE_IRHP_PERMIT_12_MONTHS,
            12,
        ];
        yield 'no rule' => [
            null,
            null,
        ];
    }

    /**
     * @param string $status
     * @param boolean $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('allowEditProvider')]
    public function testAllowEdit(mixed $status, mixed $expected): void
    {
        $feeStatus = m::mock(RefData::class)->makePartial();
        $feeStatus->setId($status);
        $this->sut->setFeeStatus($feeStatus);

        $this->assertEquals($expected, $this->sut->allowEdit());
    }

    public static function allowEditProvider(): \Iterator
    {
        yield [Entity::STATUS_PAID, false];
        yield [Entity::STATUS_CANCELLED, false];
        yield [Entity::STATUS_OUTSTANDING, true];
        yield [Entity::STATUS_REFUND_PENDING, true];
        yield [Entity::STATUS_REFUNDED, true];
        yield [Entity::STATUS_REFUND_FAILED, true];
        yield ['invalid', true];
    }

    public function testCompatibilityGetMethods(): void
    {
        $this->assertNull($this->sut->getLatestPaymentRef());
        $this->assertNull($this->sut->getPaymentMethod());
        $this->assertNull($this->sut->getProcessedBy());
        $this->assertNull($this->sut->getPayer());
        $this->assertNull($this->sut->getSlipNo());
        $this->assertNull($this->sut->getChequePoNumber());
        $this->assertNull($this->sut->getWaiveReason());

        $ft1 = self::getStubFeeTransaction('1234.56', '2015-09-01', '2015-09-02 12:35:56');
        $ft2 = self::getStubFeeTransaction('1234.56', '2015-08-01', '2015-09-02 12:34:56');
        $ft3 = self::getStubFeeTransaction('1234.56', '2015-09-01', '2015-09-02 12:34:55');
        $ft4 = self::getStubFeeTransaction(
            '234.56',
            '2015-09-03',
            '2015-09-03 12:34:55',
            Transaction::STATUS_OUTSTANDING,
            Transaction::TYPE_WAIVE,
            'waive reason'
        );

        $transaction = $ft1->getTransaction();

        $paymentMethod = m::mock(RefData::class);
        $transaction->setPaymentMethod($paymentMethod);

        $user = m::mock(User::class)
            ->shouldReceive('getLoginId')
            ->andReturn('bob')
            ->getMock();
        $transaction->setProcessedByUser($user);

        $transaction->setPayerName('payer');

        $transaction->setPayingInSlipNumber('12345');

        $transaction->setChequePoNumber('23456');

        $transaction->setReference('OLCS-1234');

        $this->sut->getFeeTransactions()->add($ft1);
        $this->sut->getFeeTransactions()->add($ft2);
        $this->sut->getFeeTransactions()->add($ft3);
        $this->sut->getFeeTransactions()->add($ft4);

        $this->assertEquals($paymentMethod, $this->sut->getPaymentMethod());
        $this->assertEquals('bob', $this->sut->getProcessedBy());
        $this->assertEquals('payer', $this->sut->getPayer());
        $this->assertEquals('12345', $this->sut->getSlipNo());
        $this->assertEquals('23456', $this->sut->getChequePoNumber());
        $this->assertEquals('waive reason', $this->sut->getWaiveReason());
        $this->assertEquals('OLCS-1234', $this->sut->getLatestPaymentRef());
    }

    public function testGetProcessedByNullNoTransaction(): void
    {
        $this->assertNull($this->sut->getProcessedBy());
    }

    public function testGetProcessedByNullNoTransactionUser(): void
    {
        $ft1 = self::getStubFeeTransaction('1234.56', '2015-09-01', '2015-09-02 12:34:56');
        $this->sut->getFeeTransactions()->add($ft1);

        $this->assertNull($this->sut->getProcessedBy());
    }

    private static function getStubFeeTransaction(
        mixed $amount,
        mixed $completedDate,
        mixed $createdOn,
        mixed $statusId = Transaction::STATUS_COMPLETE,
        mixed $typeId = Transaction::TYPE_PAYMENT,
        string $comment = '',
        mixed $transactionId = null
    ): FeeTransaction {
        $transaction = new Transaction();
        $transaction->setId($transactionId);
        $feeTransaction = new FeeTransaction();
        $feeTransaction->setTransaction($transaction);
        $feeTransaction->setAmount($amount);
        $completed = new \DateTime($completedDate);
        $transaction->setCompletedDate($completed);
        $created = new \DateTime($createdOn);
        $transaction->setCreatedOn($created);
        $status = new RefData()->setId($statusId);
        $transaction->setStatus($status);
        $type = new RefData()->setId($typeId);
        $transaction->setType($type);
        $transaction->setComment($comment);

        return $feeTransaction;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('outstandingWaiveTransactionProvider')]
    public function testGetOutstandingWaiveTransaction(array $feeTransactions, mixed $expected): void
    {
        $this->sut->setFeeTransactions(new ArrayCollection($feeTransactions));

        $this->assertEquals($expected, $this->sut->getOutstandingWaiveTransaction());
    }

    public static function outstandingWaiveTransactionProvider(): array
    {
        $transaction1 = m::mock(Transaction::class);
        $transaction1->shouldReceive('isOutstanding')
            ->andReturn(false);
        $transaction1->shouldReceive('getType->getId')
            ->andReturn(Transaction::TYPE_WAIVE);

        $transaction2 = m::mock(Transaction::class);
        $transaction2->shouldReceive('isOutstanding')
            ->andReturn(true);
        $transaction2->shouldReceive('getType->getId')
            ->andReturn(Transaction::TYPE_WAIVE);

        $feeTransaction1 = m::mock(FeeTransaction::class)
            ->shouldReceive('getTransaction')
            ->andReturn($transaction1)
            ->getMock();
        $feeTransaction2 = m::mock(FeeTransaction::class)
            ->shouldReceive('getTransaction')
            ->andReturn($transaction2)
            ->getMock();

        return [
            'none' => [
                [],
                null,
            ],
            'valid' => [
                [$feeTransaction1, $feeTransaction2],
                $transaction2,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('outstandingAmountProvider')]
    public function testGetOutstandingAmount(mixed $feeAmount, mixed $feeTransactions, mixed $expected): void
    {
        $this->sut->setGrossAmount($feeAmount);
        $this->sut->setFeeTransactions($feeTransactions);
        $this->assertEquals($expected, $this->sut->getOutstandingAmount());
    }

    public static function outstandingAmountProvider(): \Iterator
    {
        yield 'no transactions' => [
            '1234.56',
            new ArrayCollection(),
            '1234.56',
        ];
        yield 'one complete transaction' => [
            '1234.56',
            new ArrayCollection(
                [
                    self::getStubFeeTransaction('1234.56', '2015-09-01', '2015-09-02'),
                ]
            ),
            '0.00',
        ];
        yield 'one pending transaction' => [
            '1234.56',
            new ArrayCollection(
                [
                    self::getStubFeeTransaction(
                        '1234.56',
                        '2015-09-01',
                        '2015-09-02 12:34:56',
                        Transaction::STATUS_OUTSTANDING
                    ),
                ]
            ),
            '1234.56',
        ];
        yield 'two complete one refund one pending' => [
            '1234.56',
            new ArrayCollection(
                [
                    self::getStubFeeTransaction('1000', '2015-09-01', '2015-09-02'),
                    self::getStubFeeTransaction('300', '2015-09-01', '2015-09-02'),
                    self::getStubFeeTransaction('-100', '2015-09-01', '2015-09-02'),
                    self::getStubFeeTransaction(
                        '34.56',
                        '2015-09-01',
                        '2015-09-02',
                        Transaction::STATUS_OUTSTANDING
                    ),
                ]
            ),
            '34.56',
        ];
        yield 'one overpayment' => [
            '1234.56',
            new ArrayCollection(
                [
                    self::getStubFeeTransaction('2000', '2015-09-01', '2015-09-02'),
                ]
            ),
            '-765.44',
        ];
        yield 'bug OLCS-11509' => [
            '4.56',
            new ArrayCollection([]),
            '4.56',
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('partPaidProvider')]
    public function testIsPartPaid(mixed $feeAmount, mixed $feeTransactions, mixed $expected): void
    {
        $this->sut->setGrossAmount($feeAmount);
        $this->sut->setFeeTransactions($feeTransactions);
        $this->assertEquals($expected, $this->sut->isPartPaid());
    }

    public static function partPaidProvider(): \Iterator
    {
        yield 'no transactions' => [
            '1234.56',
            new ArrayCollection(),
            false,
        ];
        yield 'one complete transaction' => [
            '1234.56',
            new ArrayCollection(
                [
                    self::getStubFeeTransaction('1234.56', '2015-09-01', '2015-09-02'),
                ]
            ),
            true, // fully paid IS part paid
        ];
        yield 'one pending transaction' => [
            '1234.56',
            new ArrayCollection(
                [
                    self::getStubFeeTransaction(
                        '1234.56',
                        '2015-09-01',
                        '2015-09-02 12:34:56',
                        Transaction::STATUS_OUTSTANDING
                    ),
                ]
            ),
            false,
        ];
        yield 'two complete one refund one pending' => [
            '1234.56',
            new ArrayCollection(
                [
                    self::getStubFeeTransaction('1000', '2015-09-01', '2015-09-02'),
                    self::getStubFeeTransaction('300', '2015-09-01', '2015-09-02'),
                    self::getStubFeeTransaction('-100', '2015-09-01', '2015-09-02'),
                    self::getStubFeeTransaction(
                        '34.56',
                        '2015-09-01',
                        '2015-09-02',
                        Transaction::STATUS_OUTSTANDING
                    ),
                ]
            ),
            true,
        ];
        yield 'one overpayment' => [
            '1234.56',
            new ArrayCollection(
                [
                    self::getStubFeeTransaction('2000', '2015-09-01', '2015-09-02'),
                ]
            ),
            true,
        ];
    }

    public function testGetLatestFeeTransactionNull(): void
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getTransaction')->never()
            ->getMock();

        $feeTr1 = self::getStubFeeTransaction(5, '2017-06-05', '2015-09-02', null, null, '', 9001);
        $sut->setFeeTransactions(new ArrayCollection([$feeTr1]));

        //  call
        $this->assertNull($sut->getPaymentMethod());
    }

    public function testGetCalculatedBundleValues(): void
    {
        /** @var Entity $sut */
        $sut = m::mock(Entity::class)
            ->makePartial()
            ->shouldReceive('getOutstandingAmount')->once()->andReturn('unit_Outstanding')
            ->shouldReceive('getLatestPaymentRef')->once()->andReturn('unit_receiptNo')
            ->shouldReceive('getGrossAmount')->once()->andReturn('unit_Amount')
            ->shouldReceive('getDueDate')->once()->andReturn('unit_dueDate')
            ->shouldReceive('isRuleBeforeInvoiceDate')->once()->andReturn('unit_RuleDateBeforeInvoice')
            ->shouldReceive('isExpiredForLicence')->once()->andReturn('unit_ExpiredForLicence')
            ->shouldReceive('isOutstanding')->once()->andReturn('unit_isOutstanding')
            ->shouldReceive('isEcmtIssuingFee')->once()->andReturn('unit_isEcmtIssuing')
            ->shouldReceive('isAccrualBeforeInvoiceDatePermitted')->once()->andReturn('unit_isAccrualBeforeInvoiceDatePermitted')
            ->getMock();

        $this->assertEquals([
            'outstanding' => 'unit_Outstanding',
            'receiptNo' => 'unit_receiptNo',
            'amount' => 'unit_Amount',
            'dueDate' => 'unit_dueDate',
            'ruleDateBeforeInvoice' => 'unit_RuleDateBeforeInvoice',
            'isExpiredForLicence' => 'unit_ExpiredForLicence',
            'isOutstanding' => 'unit_isOutstanding',
            'isEcmtIssuingFee' => 'unit_isEcmtIssuing',
            'isAccrualBeforeInvoiceDatePermitted' => 'unit_isAccrualBeforeInvoiceDatePermitted'
        ], $sut->getCalculatedBundleValues());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('providerExpiredForLicence')]
    public function testIsExpiredForLicence(mixed $expiryDate, mixed $expected): void
    {
        $mockLicence = m::mock()
            ->shouldReceive('getExpiryDate')
            ->andReturn('foo')
            ->once()
            ->shouldReceive('getExpiryDateAsDate')
            ->andReturn($expiryDate)
            ->once()
            ->getMock();

        /** @var Entity $sut */
        $sut = m::mock(Entity::class)
            ->makePartial()
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence)
            ->once()
            ->getMock();

        $this->assertEquals($sut->isExpiredForLicence(), $expected);
    }

    public static function providerExpiredForLicence(): \Iterator
    {
        yield [
            \DateTime::createFromFormat('Y-m-d', '3000-01-01'),
            false
        ];
        yield [
            \DateTime::createFromFormat('Y-m-d', '1000-01-01'),
            true
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getOrganisationProvider')]
    public function testGetOrganisation(mixed $licence, mixed $irfoGvPermit, mixed $irfoPsvAuth, mixed $expected): void
    {
        $this->sut->setLicence($licence);
        $this->sut->setIrfoGvPermit($irfoGvPermit);
        $this->sut->setIrfoPsvAuth($irfoPsvAuth);
        $this->assertSame($expected, $this->sut->getOrganisation());
    }

    public static function getOrganisationProvider(): \Iterator
    {
        $organisation = m::mock(Organisation::class);
        yield 'licence' => [
            m::mock(Licence::class)->makePartial()->setOrganisation($organisation),
            null,
            null,
            $organisation,
        ];
        yield 'irfo gv permit' => [
            null,
            m::mock(IrfoGvPermit::class)->makePartial()->setOrganisation($organisation),
            null,
            $organisation,
        ];
        yield 'irfo psv auth' => [
            null,
            null,
            m::mock(IrfoPsvAuth::class)->makePartial()->setOrganisation($organisation),
            $organisation,
        ];
        yield 'neither' => [
            null,
            null,
            null,
            null,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getCustomerNameProvider')]
    public function testGetCustomerNameForInvoice(mixed $licence, mixed $irfoGvPermit, mixed $expected): void
    {
        $this->sut->setLicence($licence);
        $this->sut->setIrfoGvPermit($irfoGvPermit);
        $this->assertEquals($expected, $this->sut->getCustomerNameForInvoice());
    }

    public static function getCustomerNameProvider(): \Iterator
    {
        $organisation = m::mock(Organisation::class)
            ->shouldReceive('getName')
            ->andReturn('Foo')
            ->getMock();
        yield 'licence' => [
            m::mock(Licence::class)->makePartial()->setOrganisation($organisation),
            null,
            'Foo',
        ];
        yield 'irfo' => [
            null,
            m::mock(IrfoGvPermit::class)->makePartial()->setOrganisation($organisation),
            'Foo',
        ];
        yield 'neither' => [
            null,
            null,
            null,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getCustomerAddressProvider')]
    public function testGetCustomerAddressForInvoice(mixed $licence, mixed $irfoGvPermit, mixed $irfoPsvAuth, mixed $expected): void
    {
        $this->sut->setLicence($licence);
        $this->sut->setIrfoGvPermit($irfoGvPermit);
        $this->sut->setIrfoPsvAuth($irfoPsvAuth);

        $actual = $this->sut->getCustomerAddressForInvoice();
        $this->assertEquals($expected, ($actual ? $actual->toArray() : $actual));
    }

    public function testGetCustomerAddressForInvoiceEmpty(): void
    {
        $this->assertNotInstanceOf(\Dvsa\Olcs\Api\Entity\ContactDetails\Address::class, $this->sut->getCustomerAddressForInvoice());
    }

    public static function getCustomerAddressProvider(): \Iterator
    {
        $address = m::mock(Address::class)
            ->shouldReceive('toArray')
            ->andReturn(
                [
                    'addressLine1' => 'Foo1',
                    'addressLine2' => 'Foo2',
                    'addressLine3' => 'Foo3',
                    'addressLine4' => 'Foo4',
                    'town' => 'FooTown',
                    'postcode' => 'FooPostcode',
                    'countryCode' => 'FooCountry',
                ]
            )
            ->getMock();

        $contactDetails = m::mock(ContactDetails::class)
            ->shouldReceive('getAddress')
            ->andReturn($address)
            ->getMock();

        $organisation = m::mock(Organisation::class)
            ->shouldReceive('getIrfoContactDetails')
            ->andReturn($contactDetails)
            ->getMock();
        yield 'licence' => [
            m::mock(Licence::class)->makePartial()->setCorrespondenceCd($contactDetails),
            null,
            null,
            [
                'addressLine1' => 'Foo1',
                'addressLine2' => 'Foo2',
                'addressLine3' => 'Foo3',
                'addressLine4' => 'Foo4',
                'town' => 'FooTown',
                'postcode' => 'FooPostcode',
                'countryCode' => 'FooCountry',
            ],
        ];
        yield 'irfo gv permit' => [
            null,
            m::mock(IrfoGvPermit::class)->makePartial()->setOrganisation($organisation),
            null,
            [
                'addressLine1' => 'Foo1',
                'addressLine2' => 'Foo2',
                'addressLine3' => 'Foo3',
                'addressLine4' => 'Foo4',
                'town' => 'FooTown',
                'postcode' => 'FooPostcode',
                'countryCode' => 'FooCountry',
            ],
        ];
        yield 'irfo psv auth' => [
            null,
            null,
            m::mock(IrfoPsvAuth::class)->makePartial()->setOrganisation($organisation),
            [
                'addressLine1' => 'Foo1',
                'addressLine2' => 'Foo2',
                'addressLine3' => 'Foo3',
                'addressLine4' => 'Foo4',
                'town' => 'FooTown',
                'postcode' => 'FooPostcode',
                'countryCode' => 'FooCountry',
            ],
        ];
        //  licence and organisation - have not corr details
        yield [
            'licence' => m::mock(Licence::class)->makePartial(),
            'irfoGvPermit' => null,
            'irfoPsvAuth' => new IrfoPsvAuth(
                new Organisation(),
                new Entities\Irfo\IrfoPsvAuthType(),
                new RefData()
            ),
            'expected' => null,
        ];
    }

    /**
     * @param string $status
     * @param boolean $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isPaidProvider')]
    public function testIsPaid(mixed $status, mixed $expected): void
    {
        $this->sut->setFeeStatus(new RefData($status));

        $this->assertEquals($expected, $this->sut->isPaid());
    }

    public static function isPaidProvider(): \Iterator
    {
        yield [Entity::STATUS_PAID, true];
        yield [Entity::STATUS_CANCELLED, false];
        yield [Entity::STATUS_OUTSTANDING, false];
        yield ['invalid', false];
    }

    /**
     * @param string $status
     * @param boolean $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isOutstandingProvider')]
    public function testIsOutstanding(mixed $status, mixed $expected): void
    {
        $this->sut->setFeeStatus(new RefData($status));

        $this->assertEquals($expected, $this->sut->isOutstanding());
    }

    public static function isOutstandingProvider(): \Iterator
    {
        yield [Entity::STATUS_PAID, false];
        yield [Entity::STATUS_CANCELLED, false];
        yield [Entity::STATUS_OUTSTANDING, true];
        yield ['invalid', false];
    }

    /**
     * @param string $status
     * @param boolean $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isCancelledProvider')]
    public function testIsCancelled(mixed $status, mixed $expected): void
    {
        $this->sut->setFeeStatus(new RefData($status));

        $this->assertEquals($expected, $this->sut->isCancelled());
    }

    public static function isCancelledProvider(): \Iterator
    {
        yield [Entity::STATUS_PAID, false];
        yield [Entity::STATUS_CANCELLED, true];
        yield [Entity::STATUS_OUTSTANDING, false];
        yield ['invalid', false];
    }

    /**
     * @param string $feeAmount
     * @param string $status
     * @param array $feeTransactions
     * @param boolean $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isFullyOutstandingProvider')]
    public function testIsFullyOutstanding(mixed $feeAmount, mixed $status, mixed $feeTransactions, mixed $expected): void
    {
        $this->sut->setFeeStatus(new RefData($status));
        $this->sut->setFeeTransactions(new ArrayCollection($feeTransactions));
        $this->sut->setGrossAmount($feeAmount);

        $this->assertEquals($expected, $this->sut->isFullyOutstanding());
    }

    public static function isFullyOutstandingProvider(): array
    {
        $paid10 = m::mock(FeeTransaction::class);
        $paid10->shouldReceive('getTransaction->isComplete')
            ->andReturn(true);
        $paid10->shouldReceive('getAmount')
            ->andReturn('10.00');

        $pending10 = m::mock(FeeTransaction::class);
        $pending10->shouldReceive('getTransaction->isComplete')
            ->andReturn(false);
        $pending10->shouldReceive('getAmount')
            ->andReturn('10.00');

        return [
            ['10.00', Entity::STATUS_PAID, [], false],
            ['10.00', Entity::STATUS_CANCELLED, [], false],
            ['10.00', Entity::STATUS_OUTSTANDING, [], true],
            ['10.00', Entity::STATUS_PAID, [$paid10], false],
            ['20.00', Entity::STATUS_OUTSTANDING, [$paid10], false],
            ['10.00', Entity::STATUS_OUTSTANDING, [$pending10], true],
        ];
    }

    /**
     * @param string $type
     * @param boolean $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isBalancingFeeProvider')]
    public function testIsBalancingFee(mixed $type, mixed $expected): void
    {
        $feeTypeType = new RefData($type);
        $feeType = new FeeType();
        $feeType->setFeeType($feeTypeType);

        $this->sut->setFeeType($feeType);

        $this->assertEquals($expected, $this->sut->isBalancingFee());
    }

    public static function isBalancingFeeProvider(): \Iterator
    {
        yield [FeeType::FEE_TYPE_APP, false];
        yield [FeeType::FEE_TYPE_VAR, false];
        yield [FeeType::FEE_TYPE_GRANT, false];
        yield [FeeType::FEE_TYPE_CONT, false];
        yield [FeeType::FEE_TYPE_VEH, false];
        yield [FeeType::FEE_TYPE_GRANTINT, false];
        yield [FeeType::FEE_TYPE_INTVEH, false];
        yield [FeeType::FEE_TYPE_DUP, false];
        yield [FeeType::FEE_TYPE_ANN, false];
        yield [FeeType::FEE_TYPE_GRANTVAR, false];
        yield [FeeType::FEE_TYPE_BUSAPP, false];
        yield [FeeType::FEE_TYPE_BUSVAR, false];
        yield [FeeType::FEE_TYPE_GVANNVEH, false];
        yield [FeeType::FEE_TYPE_INTUPGRADEVEH, false];
        yield [FeeType::FEE_TYPE_INTAMENDED, false];
        yield [FeeType::FEE_TYPE_IRFOPSVAPP, false];
        yield [FeeType::FEE_TYPE_IRFOPSVANN, false];
        yield [FeeType::FEE_TYPE_IRFOPSVCOPY, false];
        yield [FeeType::FEE_TYPE_IRFOGVPERMIT, false];
        yield [FeeType::FEE_TYPE_ADJUSTMENT, true];
    }

    /**
     * @param string $type
     * @param boolean $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isNewApplicationFeeProvider')]
    public function testIsNewApplicationFee(mixed $type, mixed $expected): void
    {
        $feeTypeType = new RefData($type);
        $feeType = new FeeType();
        $feeType->setFeeType($feeTypeType);

        $this->sut->setFeeType($feeType);

        $this->assertEquals($expected, $this->sut->isNewApplicationFee());
    }

    public static function isNewApplicationFeeProvider(): \Iterator
    {
        yield [FeeType::FEE_TYPE_APP, true];
        yield [FeeType::FEE_TYPE_VAR, false];
        yield [FeeType::FEE_TYPE_GRANT, false];
        yield [FeeType::FEE_TYPE_CONT, false];
        yield [FeeType::FEE_TYPE_VEH, false];
        yield [FeeType::FEE_TYPE_GRANTINT, false];
        yield [FeeType::FEE_TYPE_INTVEH, false];
        yield [FeeType::FEE_TYPE_DUP, false];
        yield [FeeType::FEE_TYPE_ANN, false];
        yield [FeeType::FEE_TYPE_GRANTVAR, false];
        yield [FeeType::FEE_TYPE_BUSAPP, false];
        yield [FeeType::FEE_TYPE_BUSVAR, false];
        yield [FeeType::FEE_TYPE_GVANNVEH, false];
        yield [FeeType::FEE_TYPE_INTUPGRADEVEH, false];
        yield [FeeType::FEE_TYPE_INTAMENDED, false];
        yield [FeeType::FEE_TYPE_IRFOPSVAPP, false];
        yield [FeeType::FEE_TYPE_IRFOPSVANN, false];
        yield [FeeType::FEE_TYPE_IRFOPSVCOPY, false];
        yield [FeeType::FEE_TYPE_IRFOGVPERMIT, false];
        yield [FeeType::FEE_TYPE_ADJUSTMENT, false];
    }

    /**
     * @param string $type
     * @param boolean $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isVariationFeeProvider')]
    public function testIsVariationFee(mixed $type, mixed $expected): void
    {
        $feeTypeType = new RefData($type);
        $feeType = new FeeType();
        $feeType->setFeeType($feeTypeType);

        $this->sut->setFeeType($feeType);

        $this->assertEquals($expected, $this->sut->isVariationFee());
    }

    public static function isVariationFeeProvider(): \Iterator
    {
        yield [FeeType::FEE_TYPE_APP, false];
        yield [FeeType::FEE_TYPE_VAR, true];
        yield [FeeType::FEE_TYPE_GRANT, false];
        yield [FeeType::FEE_TYPE_CONT, false];
        yield [FeeType::FEE_TYPE_VEH, false];
        yield [FeeType::FEE_TYPE_GRANTINT, false];
        yield [FeeType::FEE_TYPE_INTVEH, false];
        yield [FeeType::FEE_TYPE_DUP, false];
        yield [FeeType::FEE_TYPE_ANN, false];
        yield [FeeType::FEE_TYPE_GRANTVAR, false];
        yield [FeeType::FEE_TYPE_BUSAPP, false];
        yield [FeeType::FEE_TYPE_BUSVAR, false];
        yield [FeeType::FEE_TYPE_GVANNVEH, false];
        yield [FeeType::FEE_TYPE_INTUPGRADEVEH, false];
        yield [FeeType::FEE_TYPE_INTAMENDED, false];
        yield [FeeType::FEE_TYPE_IRFOPSVAPP, false];
        yield [FeeType::FEE_TYPE_IRFOPSVANN, false];
        yield [FeeType::FEE_TYPE_IRFOPSVCOPY, false];
        yield [FeeType::FEE_TYPE_IRFOGVPERMIT, false];
        yield [FeeType::FEE_TYPE_ADJUSTMENT, false];
    }

    /**
     * @param string $type
     * @param boolean $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isGrantFeeProvider')]
    public function testIsGrantFee(mixed $type, mixed $expected): void
    {
        $feeTypeType = new RefData($type);
        $feeType = new FeeType();
        $feeType->setFeeType($feeTypeType);

        $this->sut->setFeeType($feeType);

        $this->assertEquals($expected, $this->sut->isGrantFee());
    }

    public static function isGrantFeeProvider(): \Iterator
    {
        yield [FeeType::FEE_TYPE_APP, false];
        yield [FeeType::FEE_TYPE_VAR, false];
        yield [FeeType::FEE_TYPE_GRANT, true];
        yield [FeeType::FEE_TYPE_CONT, false];
        yield [FeeType::FEE_TYPE_VEH, false];
        yield [FeeType::FEE_TYPE_GRANTINT, false];
        yield [FeeType::FEE_TYPE_INTVEH, false];
        yield [FeeType::FEE_TYPE_DUP, false];
        yield [FeeType::FEE_TYPE_ANN, false];
        yield [FeeType::FEE_TYPE_GRANTVAR, false];
        yield [FeeType::FEE_TYPE_BUSAPP, false];
        yield [FeeType::FEE_TYPE_BUSVAR, false];
        yield [FeeType::FEE_TYPE_GVANNVEH, false];
        yield [FeeType::FEE_TYPE_INTUPGRADEVEH, false];
        yield [FeeType::FEE_TYPE_INTAMENDED, false];
        yield [FeeType::FEE_TYPE_IRFOPSVAPP, false];
        yield [FeeType::FEE_TYPE_IRFOPSVANN, false];
        yield [FeeType::FEE_TYPE_IRFOPSVCOPY, false];
        yield [FeeType::FEE_TYPE_IRFOGVPERMIT, false];
        yield [FeeType::FEE_TYPE_ADJUSTMENT, false];
    }

    /**
     * @param string $trafficAreaRef
     * @param string $costCentreReference
     * @param string $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('salesPersonRefProvider')]
    public function testGetSalesPersonReference(mixed $trafficAreaRef, mixed $costCentreReference, mixed $expected): void
    {
        $licence = m::mock(Licence::class);
        $feeType = m::mock(FeeType::class);

        $licence->shouldReceive('getTrafficArea->getSalesPersonReference')
            ->andReturn($trafficAreaRef);

        $feeType->shouldReceive('getCostCentreRef')
            ->once()
            ->andReturn($costCentreReference);

        $this->sut->setLicence($licence);
        $this->sut->setFeeType($feeType);

        $this->assertEquals($expected, $this->sut->getSalesPersonReference());
    }

    public static function salesPersonRefProvider(): \Iterator
    {
        yield ['B', 'TA', 'B'];
        yield ['C', 'TA', 'C'];
        yield ['', 'IR', 'IR'];
        yield ['', 'MGB', 'MGB'];
        yield ['', 'MNI', 'MNI'];
        yield ['', 'MR', 'MR'];
    }

    /**
     * @param FeeType $feeType
     * @param RefData $feeStatus
     * @param array $feeTransactions
     * @param bool $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('canRefundProvider')]
    public function testCanRefund(mixed $feeType, mixed $feeStatus, mixed $feeTransactions, mixed $expected): void
    {
        $this->sut
            ->setFeeType($feeType)
            ->setFeeStatus($feeStatus)
            ->setFeeTransactions(new ArrayCollection($feeTransactions));

        $this->assertSame($expected, $this->sut->canRefund());
    }

    /**
     * @return array
     */
    public static function canRefundProvider(): array
    {
        $nonMiscFeeType = m::mock(FeeType::class)
            ->shouldReceive('isMiscellaneous')
            ->andReturn(false)
            ->getMock();
        $miscFeeType = m::mock(FeeType::class)
            ->shouldReceive('isMiscellaneous')
            ->andReturn(true)
            ->getMock();

        $outstanding = new RefData(Entity::STATUS_OUTSTANDING);
        $paid        = new RefData(Entity::STATUS_PAID);
        $cancelled   = new RefData(Entity::STATUS_CANCELLED);

        // Not migrated
        $txn1 = m::mock(Transaction::class);
        // Migrated
        $txn2 = m::mock(Transaction::class);
        // Refunded
        $txn3 = m::mock(Transaction::class);

        $nonRefundedFeeTransaction = m::mock(FeeTransaction::class);
        $nonRefundedFeeTransaction->shouldReceive('getTransaction')->andReturn($txn1);

        $txn1->shouldReceive('isCompletePaymentOrAdjustment')
            ->andReturn(true)
            ->shouldReceive('isMigrated')
            ->andReturn(false);

        $nonRefundedFeeTransaction
            ->shouldReceive('isRefundedOrReversed')
            ->andReturn(false);

        $migratedTransaction = m::mock(FeeTransaction::class);
        $migratedTransaction->shouldReceive('getTransaction')->andReturn($txn2);
        $txn2->shouldReceive('isCompletePaymentOrAdjustment')
            ->andReturn(true)
            ->shouldReceive('isMigrated')
            ->andReturn(true);

        $migratedTransaction
            ->shouldReceive('isRefundedOrReversed')
            ->andReturn(false);

        $refundedFeeTransaction = m::mock(FeeTransaction::class);
        $refundedFeeTransaction->shouldReceive('getTransaction')->andReturn($txn3);
        $txn3->shouldReceive('isCompletePaymentOrAdjustment')
            ->andReturn(true)
            ->shouldReceive('isMigrated')
            ->andReturn(false);

        $refundedFeeTransaction
            ->shouldReceive('isRefundedOrReversed')
            ->andReturn(true);

        return [
            'std outstanding'  => [$nonMiscFeeType, $outstanding, [], false],
            'std paid'         => [$nonMiscFeeType, $paid, [], false],
            'std cancelled'    => [$nonMiscFeeType, $cancelled, [], false],
            'misc outstanding' => [$miscFeeType, $outstanding, [], false],
            'misc paid'        => [$miscFeeType, $paid, [$nonRefundedFeeTransaction], true],
            'misc cancelled'   => [$miscFeeType, $cancelled, [], false],
            'std not refunded' => [$nonMiscFeeType, $paid, [$nonRefundedFeeTransaction], true],
            'migrated'         => [$nonMiscFeeType, $paid, [$migratedTransaction], false],
            'std refunded'     => [$nonMiscFeeType, $paid, [$refundedFeeTransaction], false],
        ];
    }

    public function testGetFeeTransactionsForRefund(): void
    {
        $txn1 = m::mock(Transaction::class)
            ->shouldReceive('isCompletePaymentOrAdjustment')
            ->andReturn(true)
            ->getMock();
        $nonRefundedFeeTransaction = m::mock(FeeTransaction::class);
        $nonRefundedFeeTransaction
            ->shouldReceive('getTransaction')
            ->andReturn($txn1)
            ->shouldReceive('isRefundedOrReversed')
            ->andReturn(false)
            ->shouldReceive('getReversedFeeTransaction')
            ->andReturn(null);

        $txn2 = m::mock(Transaction::class)
            ->shouldReceive('isCompletePaymentOrAdjustment')
            ->andReturn(true)
            ->getMock();
        $refundedFeeTransaction = m::mock(FeeTransaction::class);
        $refundedFeeTransaction
            ->shouldReceive('getTransaction')
            ->andReturn($txn2)
            ->shouldReceive('isRefundedOrReversed')
            ->andReturn(true)
            ->shouldReceive('getReversedFeeTransaction')
            ->andReturn(null);

        $reversingFeeTransaction = m::mock(FeeTransaction::class);
        $reversingFeeTransaction
            ->shouldReceive('getTransaction')
            ->andReturn($txn2)
            ->shouldReceive('isRefundedOrReversed')
            ->andReturn(false)
            ->shouldReceive('getReversedFeeTransaction')
            ->andReturn(m::mock(FeeTransaction::class));

        $this->sut->setFeeTransactions(
            [
                $nonRefundedFeeTransaction,
                $refundedFeeTransaction,
                $reversingFeeTransaction,
            ]
        );

        $this->assertEquals(
            [$nonRefundedFeeTransaction],
            $this->sut->getFeeTransactionsForRefund()
        );
    }

    /**
     * Test VAT calculations, see OLCS-11034
     *
     * @param float $netAmount
     * @param float $rate
     * @param float $expectedVatAmount
     * @param float $expectedGrossAmount
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('vatProvider')]
    public function testSetVatAndGrossAmountsFromNetAmountUsingRate(
        mixed $netAmount,
        mixed $rate,
        mixed $expectedVatAmount,
        mixed $expectedGrossAmount
    ): void {
        $this->sut->setNetAmount($netAmount);

        $this->sut->setVatAndGrossAmountsFromNetAmountUsingRate($rate);

        $this->assertEquals($expectedVatAmount, $this->sut->getVatAmount());
        $this->assertEqualsWithDelta($expectedGrossAmount, $this->sut->getGrossAmount(), 0.01);
    }

    public static function vatProvider(): \Iterator
    {
        yield 'no_vat' => [
            100.00,
            0,
            0,
            100.00,
        ];
        yield '20pcvat' => [
            100.00,
            20,
            20,
            120.00,
        ];
        yield '17_5_pcvat' => [
            123.45,
            17.50,
            21.60,
            145.05,
        ];
        yield 'rounding_down' => [
            99.99,
            20,
            19.99, // 19.998 rounded *down*
            119.98,
        ];
    }

    /**
     * Test pounds to pence conversion
     *
     * @param  string $input
     * @param  int $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('amountToPenceProvider')]
    public function testAmountToPence(mixed $input, mixed $expected): void
    {
        $this->assertSame($expected, Entity::amountToPence($input));
    }

    public static function amountToPenceProvider(): \Iterator
    {
        yield ['1.00', (int) 100];
        yield ['1.01', (int) 101];
        yield ['1.005', (int) 101];
        yield ['1.001', (int) 100];
        yield ['4.56', (int) 456];
        yield ['35.16', (int) 3516];
        yield ['1234.56', (int) 123456];
        yield ['-4.56', (int) -456];
    }

    /**
     * Test pence to pounds conversion
     *
     * @param  string $input
     * @param  int $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('amountToPoundsProvider')]
    public function testAmountToPounds(mixed $input, mixed $expected): void
    {
        $this->assertSame($expected, Entity::amountToPounds($input));
    }

    public static function amountToPoundsProvider(): \Iterator
    {
        yield [100, '1.00'];
        yield [101, '1.01'];
        yield [456, '4.56'];
        yield [3516, '35.16'];
        yield [123456, '1234.56'];
        yield [-456, '-4.56'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('amountByTransactionProvider')]
    public function testGetAmountAllocatedByTransactionId(mixed $feeTransactions, mixed $transactionId, mixed $expected): void
    {
        $this->sut->setFeeTransactions($feeTransactions);
        $this->assertEquals($expected, $this->sut->getAmountAllocatedByTransactionId($transactionId));
    }

    public static function amountByTransactionProvider(): \Iterator
    {
        yield 'no transactions' => [
            new ArrayCollection(),
            99,
            null,
        ];
        yield 'one complete transaction matched' => [
            new ArrayCollection(
                [
                    self::getStubFeeTransaction(
                        '234.56',
                        '2015-09-01',
                        '2015-09-02',
                        Transaction::STATUS_COMPLETE,
                        Transaction::TYPE_PAYMENT,
                        '',
                        99
                    ),
                ]
            ),
            99,
            '234.56',
        ];
        yield 'one complete transaction unmatched' => [
            new ArrayCollection(
                [
                    self::getStubFeeTransaction(
                        '234.56',
                        '2015-09-01',
                        '2015-09-02',
                        Transaction::STATUS_COMPLETE,
                        Transaction::TYPE_PAYMENT,
                        '',
                        98
                    ),
                ]
            ),
            99,
            null,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderIsRuleBeforeInvoiceDate')]
    public function testIsRuleBeforeInvoiceDate(mixed $expected, mixed $invoicedDate): void
    {
        // force the rule date to be now
        $feeType = m::mock()
            ->shouldReceive('getAccrualRule')
            ->andReturn(new RefData()->setId(Entity::ACCRUAL_RULE_IMMEDIATE))
            ->getMock();
        $this->sut->setFeeType($feeType);

        $this->sut->setInvoicedDate($invoicedDate);

        $this->assertSame($expected, $this->sut->isRuleBeforeInvoiceDate());
    }

    public static function dataProviderIsRuleBeforeInvoiceDate(): \Iterator
    {
        yield [true, new DateTime()->modify('1 second')];
        yield [true, new DateTime()->modify('1 day')];
        yield [false, new DateTime()];
        yield [false, new DateTime()->modify('-1 day')];
        yield [false, new DateTime()->modify('-1 second')];
        yield [false, null];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderGetInvoicedDateTime')]
    public function testGetInvoicedDateTime(mixed $expected, mixed $invoicedDate): void
    {
        $this->sut->setInvoicedDate($invoicedDate);
        $this->assertEquals($expected, $this->sut->getInvoicedDateTime());
    }

    public static function dataProviderGetInvoicedDateTime(): \Iterator
    {
        yield [new DateTime('2016-01-25'), new DateTime('2016-01-25')];
        yield [new DateTime('2016-01-25'), '2016-01-25'];
        yield [null, null];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetRelatedOrganisation')]
    public function testGetRelatedOrganisation(Entity $sut, mixed $expect): void
    {
        $this->assertSame($expect, $sut->getRelatedOrganisation());
    }

    public static function dpTestGetRelatedOrganisation(): \Iterator
    {
        /** @var Organisation $mockOrg */
        $mockOrg = m::mock(Organisation::class);
        /** @var RefData $mockRef */
        $mockRef = m::mock(RefData::class);

        $licence = new Licence($mockOrg, $mockRef);
        yield [
            'sut' => self::instantiate(Entity::class)->setApplication(
                new Entities\Application\Application($licence, $mockRef, false)
            ),
            'expect' => $mockOrg,
        ];
        yield [
            'sut' => self::instantiate(Entity::class)->setBusReg(
                new Entities\Bus\BusReg()->setLicence($licence)
            ),
            'expect' => $mockOrg,
        ];
        yield [
            'sut' => self::instantiate(Entity::class)->setLicence($licence),
            'expect' => $mockOrg,
        ];
        yield [
            'sut' => self::instantiate(Entity::class)->setIrfoGvPermit(
                new IrfoGvPermit($mockOrg, new Entities\Irfo\IrfoGvPermitType(), $mockRef)
            ),
            'expect' => $mockOrg,
        ];
        yield [
            'sut' => self::instantiate(Entity::class)->setIrfoPsvAuth(
                new IrfoPsvAuth($mockOrg, new Entities\Irfo\IrfoPsvAuthType(), $mockRef)
            ),
            'expect' => $mockOrg,
        ];
        yield [
            'sut' => self::instantiate(Entity::class),
            'expect' => null,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetDueDate')]
    public function testGetDueDate(mixed $status, mixed $type, mixed $invoicedDate, mixed $expected): void
    {
        $this->sut->setFeeStatus(new RefData($status));
        $this->sut->setDaysToPayIssueFee(10);

        $feeTypeType = new RefData($type);
        $feeType = new FeeType();
        $feeType->setFeeType($feeTypeType);
        $this->sut->setFeeType($feeType);

        $this->sut->setInvoicedDate($invoicedDate);

        $this->assertEquals($expected, $this->sut->getDueDate(true));
    }

    public static function dpGetDueDate(): array
    {
        $now = new DateTime();

        $nowPlus10Weekdays = clone $now;
        $nowPlus10Weekdays->add(\DateInterval::createFromDateString('+10 weekdays'));

        return [
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_APP, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_VAR, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_GRANT, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_CONT, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_VEH, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_GRANTINT, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_INTVEH, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_DUP, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_ANN, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_GRANTVAR, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_BUSAPP, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_BUSVAR, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_GVANNVEH, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_INTUPGRADEVEH, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_INTAMENDED, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_IRFOPSVAPP, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_IRFOPSVANN, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_IRFOPSVCOPY, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_IRFOGVPERMIT, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_ADJUSTMENT, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_ECMT_APP, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_ECMT_ISSUE, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_IRHP_APP, null, null],
            [Entity::STATUS_PAID, FeeType::FEE_TYPE_IRHP_ISSUE, null, null],

            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_APP, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_VAR, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_GRANT, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_CONT, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_VEH, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_GRANTINT, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_INTVEH, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_DUP, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_ANN, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_GRANTVAR, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_BUSAPP, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_BUSVAR, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_GVANNVEH, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_INTUPGRADEVEH, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_INTAMENDED, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_IRFOPSVAPP, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_IRFOPSVANN, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_IRFOPSVCOPY, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_IRFOGVPERMIT, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_ADJUSTMENT, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_ECMT_APP, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_ECMT_ISSUE, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_ECMT_ISSUE, $now, $nowPlus10Weekdays],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_IRHP_APP, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_IRHP_ISSUE, null, null],
            [Entity::STATUS_OUTSTANDING, FeeType::FEE_TYPE_IRHP_ISSUE, $now, $nowPlus10Weekdays],
        ];
    }

    public function testRemoveIrhpPermitApplicationAssociation(): void
    {
        $this->sut->setIrhpPermitApplication(m::mock(IrhpPermitApplication::class));
        $this->sut->removeIrhpPermitApplicationAssociation();

        $this->assertNull($this->sut->getIrhpPermitApplication());
    }
}
