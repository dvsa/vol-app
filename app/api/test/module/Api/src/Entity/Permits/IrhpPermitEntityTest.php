<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Permits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\System\RefData;
use DateTime;
use Mockery as m;

/**
 * IrhpPermit Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class IrhpPermitEntityTest extends EntityTester
{
    /**
     * @var (\DateTime & \Mockery\MockInterface)
     */
    public $expiryDate;
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * @var Entity
     */
    protected $sut;

    /**
     * @var DateTime
     */
    protected $issueDate;

    /**
     * @var RefData
     */
    protected $status;

    /**
     * @var int
     */
    protected $permitNumber;

    /**
     * @var IrhpPermitApplication
     */
    protected $irhpPermitApplication;

    /**
     * @var IrhpPermitRange
     */
    protected $irhpPermitRange;

    /**
     * @var IrhpCandidatePermit
     */
    protected $irhpCandidatePermit;

    public function setUp(): void
    {
        $this->issueDate = m::mock(DateTime::class);
        $this->status = new RefData();
        $this->permitNumber = 431;
        $this->expiryDate = m::mock(DateTime::class);

        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $this->irhpPermitRange = m::mock(IrhpPermitRange::class);

        $this->irhpCandidatePermit = m::mock(IrhpCandidatePermit::class);
        $this->irhpCandidatePermit->shouldReceive('getIrhpPermitApplication')
            ->andReturn($this->irhpPermitApplication);
        $this->irhpCandidatePermit->shouldReceive('getIrhpPermitRange')
            ->andReturn($this->irhpPermitRange);

        $this->sut = Entity::createNew(
            $this->irhpCandidatePermit,
            $this->issueDate,
            $this->status,
            $this->permitNumber,
            $this->expiryDate
        );
    }

    public function testCreateNew(): void
    {
        $this->assertSame($this->irhpCandidatePermit, $this->sut->getIrhpCandidatePermit());
        $this->assertSame($this->irhpPermitApplication, $this->sut->getIrhpPermitApplication());
        $this->assertSame($this->irhpPermitRange, $this->sut->getIrhpPermitRange());
        $this->assertSame($this->issueDate, $this->sut->getIssueDate());
        $this->assertSame($this->permitNumber, $this->sut->getPermitNumber());
        $this->assertSame($this->status, $this->sut->getStatus());
    }

    public function testCreateForIrhpApplication(): void
    {
        $issueDate = m::mock(DateTime::class);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('generateIssueDate')
            ->withNoArgs()
            ->andReturn($issueDate);

        $irhpPermitRange = m::mock(IrhpPermitRange::class);
        $status = m::mock(RefData::class);
        $permitNumber = 473;

        $entity = Entity::createForIrhpApplication(
            $irhpPermitApplication,
            $irhpPermitRange,
            $issueDate,
            $status,
            $permitNumber,
            null
        );

        $this->assertSame($irhpPermitApplication, $entity->getIrhpPermitApplication());
        $this->assertSame($irhpPermitRange, $entity->getIrhpPermitRange());
        $this->assertSame($issueDate, $entity->getIssueDate());
        $this->assertNull($entity->getExpiryDate());
        $this->assertSame($status, $entity->getStatus());
        $this->assertEquals($permitNumber, $entity->getPermitNumber());
    }

    public function testCreateForIrhpApplicationWithExpiry(): void
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitRange = m::mock(IrhpPermitRange::class);
        $issueDate = m::mock(DateTime::class);
        $status = m::mock(RefData::class);
        $permitNumber = 473;
        $expiryDate = new DateTime();

        $entity = Entity::createForIrhpApplication(
            $irhpPermitApplication,
            $irhpPermitRange,
            $issueDate,
            $status,
            $permitNumber,
            $expiryDate
        );

        $this->assertSame($irhpPermitApplication, $entity->getIrhpPermitApplication());
        $this->assertSame($irhpPermitRange, $entity->getIrhpPermitRange());
        $this->assertSame($issueDate, $entity->getIssueDate());
        $this->assertSame($expiryDate, $entity->getExpiryDate());
        $this->assertSame($status, $entity->getStatus());
        $this->assertEquals($permitNumber, $entity->getPermitNumber());
    }

    public function testGetPermitNumberWithPrefix(): void
    {
        $prefix = 'UK';

        $this->irhpPermitRange->shouldReceive('getPrefix')
            ->andReturn($prefix);

        $this->assertSame('UK00431', $this->sut->getPermitNumberWithPrefix());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetStartDate')]
    public function testGetStartDate(mixed $validFrom, mixed $issueDate, mixed $expected): void
    {
        $this->sut->setIssueDate($issueDate);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getValidFrom')
            ->with(true)
            ->andReturn($validFrom);

        $this->irhpPermitRange->shouldReceive('getIrhpPermitStock')
            ->andReturn($irhpPermitStock);

        $this->assertEquals($expected, $this->sut->getStartDate());
    }

    public static function dpGetStartDate(): array
    {
        $inPast = new DateTime('last year');
        $now = new DateTime();
        $inFuture = new DateTime('next year');

        return [
            'issued before valid from date' => [
                'validFrom' => $inFuture,
                'issueDate' => $now,
                'expected' => $inFuture,
            ],
            'issued after valid from date' => [
                'validFrom' => $inPast,
                'issueDate' => $now,
                'expected' => $now,
            ],
            'not yet issued' => [
                'validFrom' => $inPast,
                'issueDate' => null,
                'expected' => $inPast,
            ],
        ];
    }

    public function testGetCalculatedBundleValues(): void
    {
        $prefix = 'UK';

        $now = new DateTime();
        $then = new DateTime('+1 year');
        $this->sut->setIssueDate($now);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getValidFrom')
            ->with(true)
            ->andReturn(new DateTime('last year'));

        $this->irhpPermitRange->shouldReceive('getPrefix')
            ->andReturn($prefix)
            ->shouldReceive('getIrhpPermitStock')
            ->andReturn($irhpPermitStock);

        $this->sut->setExpiryDate($then);

        $this->assertEquals(
            [
                'permitNumberWithPrefix' => 'UK00431',
                'startDate' => $now,
                'ceasedDate' => $then
            ],
            $this->sut->getCalculatedBundleValues()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpProceedToAwaitingPrinting')]
    public function testProceedToAwaitingPrinting(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);

        if (!$expected) {
            $this->expectException(ForbiddenException::class);
        }

        $this->sut->proceedToStatus(new RefData(Entity::STATUS_AWAITING_PRINTING));

        $this->assertEquals(Entity::STATUS_AWAITING_PRINTING, $this->sut->getStatus()->getId());
    }

    public static function dpProceedToAwaitingPrinting(): array
    {
        return [
            [Entity::STATUS_PENDING, true],
            [Entity::STATUS_AWAITING_PRINTING, false],
            [Entity::STATUS_PRINTING, false],
            [Entity::STATUS_PRINTED, false],
            [Entity::STATUS_ERROR, true],
            [Entity::STATUS_CEASED, false],
            [Entity::STATUS_TERMINATED, false],
            [Entity::STATUS_EXPIRED, false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpProceedToPrinting')]
    public function testProceedToPrinting(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);

        if (!$expected) {
            $this->expectException(ForbiddenException::class);
        }

        $this->sut->proceedToStatus(new RefData(Entity::STATUS_PRINTING));

        $this->assertEquals(Entity::STATUS_PRINTING, $this->sut->getStatus()->getId());
    }

    public static function dpProceedToPrinting(): array
    {
        return [
            [Entity::STATUS_PENDING, false],
            [Entity::STATUS_AWAITING_PRINTING, true],
            [Entity::STATUS_PRINTING, false],
            [Entity::STATUS_PRINTED, false],
            [Entity::STATUS_ERROR, false],
            [Entity::STATUS_CEASED, false],
            [Entity::STATUS_TERMINATED, false],
            [Entity::STATUS_EXPIRED, false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpProceedToPrinted')]
    public function testProceedToPrinted(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);

        if (!$expected) {
            $this->expectException(ForbiddenException::class);
        }

        $this->sut->proceedToStatus(new RefData(Entity::STATUS_PRINTED));

        $this->assertEquals(Entity::STATUS_PRINTED, $this->sut->getStatus()->getId());
    }

    public static function dpProceedToPrinted(): array
    {
        return [
            [Entity::STATUS_PENDING, false],
            [Entity::STATUS_AWAITING_PRINTING, false],
            [Entity::STATUS_PRINTING, true],
            [Entity::STATUS_PRINTED, false],
            [Entity::STATUS_ERROR, false],
            [Entity::STATUS_CEASED, false],
            [Entity::STATUS_TERMINATED, false],
            [Entity::STATUS_EXPIRED, false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpProceedToError')]
    public function testProceedToError(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);

        if (!$expected) {
            $this->expectException(ForbiddenException::class);
        }

        $this->sut->proceedToStatus(new RefData(Entity::STATUS_ERROR));

        $this->assertEquals(Entity::STATUS_ERROR, $this->sut->getStatus()->getId());
    }

    public static function dpProceedToError(): array
    {
        return [
            [Entity::STATUS_PENDING, true],
            [Entity::STATUS_AWAITING_PRINTING, true],
            [Entity::STATUS_PRINTING, true],
            [Entity::STATUS_PRINTED, false],
            [Entity::STATUS_ERROR, true],
            [Entity::STATUS_CEASED, false],
            [Entity::STATUS_TERMINATED, false],
            [Entity::STATUS_EXPIRED, false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpProceedToTerminated')]
    public function testProceedToTerminated(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);

        if (!$expected) {
            $this->expectException(ForbiddenException::class);
        }

        $this->sut->proceedToStatus(new RefData(Entity::STATUS_TERMINATED));

        $this->assertEquals(Entity::STATUS_TERMINATED, $this->sut->getStatus()->getId());
    }

    public static function dpProceedToTerminated(): array
    {
        return [
            [Entity::STATUS_PENDING, true],
            [Entity::STATUS_AWAITING_PRINTING, true],
            [Entity::STATUS_PRINTING, true],
            [Entity::STATUS_PRINTED, true],
            [Entity::STATUS_ERROR, true],
            [Entity::STATUS_CEASED, false],
            [Entity::STATUS_TERMINATED, false],
            [Entity::STATUS_EXPIRED, false]
        ];
    }

    public function testProceedToUnknown(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->sut->proceedToStatus(new RefData());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsPending')]
    public function testIsPending(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);

        $this->assertEquals($expected, $this->sut->isPending());
    }

    public static function dpIsPending(): array
    {
        return [
            [Entity::STATUS_PENDING, true],
            [Entity::STATUS_AWAITING_PRINTING, false],
            [Entity::STATUS_PRINTING, false],
            [Entity::STATUS_PRINTED, false],
            [Entity::STATUS_ERROR, false],
            [Entity::STATUS_CEASED, false],
            [Entity::STATUS_TERMINATED, false],
            [Entity::STATUS_EXPIRED, false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsAwaitingPrinting')]
    public function testIsAwaitingPrinting(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);

        $this->assertEquals($expected, $this->sut->isAwaitingPrinting());
    }

    public static function dpIsAwaitingPrinting(): array
    {
        return [
            [Entity::STATUS_PENDING, false],
            [Entity::STATUS_AWAITING_PRINTING, true],
            [Entity::STATUS_PRINTING, false],
            [Entity::STATUS_PRINTED, false],
            [Entity::STATUS_ERROR, false],
            [Entity::STATUS_CEASED, false],
            [Entity::STATUS_TERMINATED, false],
            [Entity::STATUS_EXPIRED, false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsPrinting')]
    public function testIsPrinting(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);

        $this->assertEquals($expected, $this->sut->isPrinting());
    }

    public static function dpIsPrinting(): array
    {
        return [
            [Entity::STATUS_PENDING, false],
            [Entity::STATUS_AWAITING_PRINTING, false],
            [Entity::STATUS_PRINTING, true],
            [Entity::STATUS_PRINTED, false],
            [Entity::STATUS_ERROR, false],
            [Entity::STATUS_CEASED, false],
            [Entity::STATUS_TERMINATED, false],
            [Entity::STATUS_EXPIRED, false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHasError')]
    public function testHasError(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);

        $this->assertEquals($expected, $this->sut->hasError());
    }

    public static function dpHasError(): array
    {
        return [
            [Entity::STATUS_PENDING, false],
            [Entity::STATUS_AWAITING_PRINTING, false],
            [Entity::STATUS_PRINTING, false],
            [Entity::STATUS_PRINTED, false],
            [Entity::STATUS_ERROR, true],
            [Entity::STATUS_CEASED, false],
            [Entity::STATUS_TERMINATED, false],
            [Entity::STATUS_EXPIRED, false]
        ];
    }

    public static function dpCease(): array
    {
        return [
            [Entity::STATUS_PENDING, false],
            [Entity::STATUS_AWAITING_PRINTING, false],
            [Entity::STATUS_PRINTING, false],
            [Entity::STATUS_PRINTED, false],
            [Entity::STATUS_ERROR, false],
            [Entity::STATUS_CEASED, true],
            [Entity::STATUS_TERMINATED, false],
            [Entity::STATUS_EXPIRED, false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCease')]
    public function testCease(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);

        if (!$expected) {
            $this->expectException(ForbiddenException::class);
        }

        $this->sut->cease(new RefData($statusId));

        $this->assertEquals(Entity::STATUS_CEASED, $this->sut->getStatus()->getId());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsCeased')]
    public function testIsCeased(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);

        $this->assertEquals($expected, $this->sut->isCeased());
    }

    public static function dpIsCeased(): array
    {
        return [
            [Entity::STATUS_PENDING, false],
            [Entity::STATUS_AWAITING_PRINTING, false],
            [Entity::STATUS_PRINTING, false],
            [Entity::STATUS_PRINTED, false],
            [Entity::STATUS_ERROR, false],
            [Entity::STATUS_CEASED, true],
            [Entity::STATUS_TERMINATED, false],
            [Entity::STATUS_EXPIRED, false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsTerminated')]
    public function testIsTerminated(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);

        $this->assertEquals($expected, $this->sut->isTerminated());
    }

    public static function dpIsTerminated(): array
    {
        return [
            [Entity::STATUS_PENDING, false],
            [Entity::STATUS_AWAITING_PRINTING, false],
            [Entity::STATUS_PRINTING, false],
            [Entity::STATUS_PRINTED, false],
            [Entity::STATUS_ERROR, false],
            [Entity::STATUS_CEASED, false],
            [Entity::STATUS_TERMINATED, true],
            [Entity::STATUS_EXPIRED, false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValid')]
    public function testIsValid(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);

        $this->assertEquals($expected, $this->sut->isValid());
    }

    public static function dpIsValid(): array
    {
        return [
            [Entity::STATUS_PENDING, true],
            [Entity::STATUS_AWAITING_PRINTING, true],
            [Entity::STATUS_PRINTING, true],
            [Entity::STATUS_PRINTED, true],
            [Entity::STATUS_ERROR, true],
            [Entity::STATUS_CEASED, false],
            [Entity::STATUS_TERMINATED, false],
            [Entity::STATUS_EXPIRED, false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsExpired')]
    public function testIsExpired(mixed $statusId, mixed $expected): void
    {
        $this->sut->getStatus()->setId($statusId);
        $this->assertEquals($expected, $this->sut->isExpired());
    }

    public static function dpIsExpired(): array
    {
        return [
            [Entity::STATUS_PENDING, false],
            [Entity::STATUS_AWAITING_PRINTING, false],
            [Entity::STATUS_PRINTING, false],
            [Entity::STATUS_PRINTED, false],
            [Entity::STATUS_ERROR, false],
            [Entity::STATUS_CEASED, false],
            [Entity::STATUS_TERMINATED, false],
            [Entity::STATUS_EXPIRED, true]
        ];
    }

    public function testCreateReplacement(): void
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitRange = m::mock(IrhpPermitRange::class);
        $issueDate = m::mock(DateTime::class);
        $status = m::mock(RefData::class);
        $permitNumber = 473;
        $expiryDate = new DateTime('2200-10-10 10:10:10');

        $oldEntity = Entity::createForIrhpApplication(
            $irhpPermitApplication,
            $irhpPermitRange,
            $issueDate,
            $status,
            $permitNumber,
            $expiryDate
        );

        $newIrhpPermitRange = m::mock(IrhpPermitRange::class);

        $replacedEntity = Entity::createReplacement(
            $oldEntity,
            $newIrhpPermitRange,
            $status,
            785
        );

        $this->assertInstanceOf($this->entityClass, $replacedEntity);
        $this->assertSame($oldEntity->getIrhpPermitApplication(), $replacedEntity->getIrhpPermitApplication());
        $this->assertSame($newIrhpPermitRange, $replacedEntity->getIrhpPermitRange());
        $this->assertSame($oldEntity->getExpiryDate(), $replacedEntity->getExpiryDate());
        $this->assertSame($oldEntity->getStatus(), $replacedEntity->getStatus());
        $this->assertEquals(785, $replacedEntity->getPermitNumber());
    }

    public function testCreateReplacementNoExpiry(): void
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitRange = m::mock(IrhpPermitRange::class);
        $issueDate = m::mock(DateTime::class);
        $status = m::mock(RefData::class);
        $permitNumber = 473;
        $stockValidTo = new DateTime('2200-10-10 10:10:10');

        $irhpPermitRange
            ->shouldReceive('getIrhpPermitStock->getValidTo')
            ->once()
            ->withNoArgs()
            ->andReturn($stockValidTo);

        $oldEntity = Entity::createForIrhpApplication(
            $irhpPermitApplication,
            $irhpPermitRange,
            $issueDate,
            $status,
            $permitNumber,
            null
        );

        $newIrhpPermitRange = m::mock(IrhpPermitRange::class);

        $replacedEntity = Entity::createReplacement(
            $oldEntity,
            $newIrhpPermitRange,
            $status,
            555
        );

        $this->assertInstanceOf($this->entityClass, $replacedEntity);
        $this->assertSame($oldEntity->getIrhpPermitApplication(), $replacedEntity->getIrhpPermitApplication());
        $this->assertSame($newIrhpPermitRange, $replacedEntity->getIrhpPermitRange());
        $this->assertSame($stockValidTo, $replacedEntity->getExpiryDate());
        $this->assertSame($oldEntity->getStatus(), $replacedEntity->getStatus());
        $this->assertEquals(555, $replacedEntity->getPermitNumber());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetCeasedDate')]
    public function testGetCeasedDate(mixed $expiryDate, mixed $validTo, mixed $expected): void
    {
        $this->sut->setExpiryDate($expiryDate);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->allows('getValidTo')
            ->with(true)
            ->andReturn($validTo);

        $this->irhpPermitRange->allows('getIrhpPermitStock')
            ->andReturn($irhpPermitStock);

        $this->assertEquals($expected, $this->sut->getCeasedDate());
    }

    public static function dpGetCeasedDate(): array
    {
        $expAsDate = new DateTime('1st Jan 2200');
        $validToDate = new DateTime('1st Jan 2300');

        return [
            'expiry is set' => [
                'expiryDate' => $expAsDate,
                'validTo' => null,
                'expected' => $expAsDate,
            ],
            'expiry is null - valid to from stock expected' => [
                'expiryDate' => null,
                'validTo' => $validToDate,
                'expected' => $validToDate,
            ]
        ];
    }

    public function testRegenerateIssueDateAndExpiryDate(): void
    {
        $issueDate = m::mock(DateTime::class);
        $expiryDate = m::mock(DateTime::class);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('generateExpiryDate')
            ->withNoArgs()
            ->andReturn($expiryDate);
        $irhpPermitApplication->shouldReceive('generateIssueDate')
            ->withNoArgs()
            ->andReturn($issueDate);

        $this->sut->setIrhpPermitApplication($irhpPermitApplication);
        $this->sut->regenerateIssueDateAndExpiryDate();

        $this->assertSame(
            $issueDate,
            $this->sut->getIssueDate()
        );

        $this->assertSame(
            $expiryDate,
            $this->sut->getExpiryDate()
        );
    }
}
