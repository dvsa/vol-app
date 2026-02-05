<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Allocate;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Allocate\IrhpPermitAllocator;
use Dvsa\Olcs\Api\Service\Permits\Allocate\RangeMatchingCriteriaInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * IrhpPermitAllocatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpPermitAllocatorTest extends MockeryTestCase
{
    private $irhpPermitRepo;

    private $irhpPermitAllocator;

    public function setUp(): void
    {
        $this->irhpPermitRepo = m::mock(IrhpPermitRepository::class);

        $this->irhpPermitAllocator = new IrhpPermitAllocator($this->irhpPermitRepo);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCriteriaAndExpiryDate')]
    public function testAllocatePermitInFirstRange(mixed $criteria, mixed $expiryDate): void
    {
        $pendingStatus = m::mock(RefData::class);
        $this->irhpPermitRepo->shouldReceive('getRefdataReference')
            ->with(IrhpPermit::STATUS_PENDING)
            ->andReturn($pendingStatus);

        $irhpPermitApplicationId = 305;
        $issueDate = m::mock(DateTime::class);

        $irhpPermitRange1Id = 100;
        $irhpPermitRange1AssignedNumbers = [500, 501, 503];
        $irhpPermitRange1StockValidTo = m::mock(DateTime::class);
        $irhpPermitRange1 = $this->createMockRange($irhpPermitRange1Id, 500, 504, 5, $irhpPermitRange1StockValidTo);

        $this->irhpPermitRepo->shouldReceive('getAssignedPermitNumbersByRange')
            ->with($irhpPermitRange1Id)
            ->andReturn($irhpPermitRange1AssignedNumbers);

        $irhpPermitRanges = [$irhpPermitRange1];

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getId')
            ->andReturn($irhpPermitApplicationId);
        $irhpPermitApplication->shouldReceive('generateIssueDate')
            ->withNoArgs()
            ->andReturn($issueDate);
        $irhpPermitApplication->shouldReceive(
            'getIrhpPermitWindow->getIrhpPermitStock->getNonReservedNonReplacementRangesOrderedByFromNo'
        )
        ->with($criteria)
        ->andReturn($irhpPermitRanges);

        $this->irhpPermitRepo->shouldReceive('save')
            ->once()
            ->with(m::on(function ($irhpPermit) use ($irhpPermitApplication, $irhpPermitRange1, $issueDate, $expiryDate, $pendingStatus) {
                $this->assertSame($irhpPermitApplication, $irhpPermit->getIrhpPermitApplication());
                $this->assertSame($irhpPermitRange1, $irhpPermit->getIrhpPermitRange());
                $this->assertSame($issueDate, $irhpPermit->getIssueDate());
                $this->assertSame($expiryDate, $irhpPermit->getExpiryDate());
                $this->assertEquals(502, $irhpPermit->getPermitNumber());
                $this->assertSame($pendingStatus, $irhpPermit->getStatus());
                return true;
            }));

        $result = new Result();

        $this->irhpPermitAllocator->allocate(
            $result,
            $irhpPermitApplication,
            $criteria,
            $expiryDate
        );

        $this->assertEquals(
            ['Allocated permit number 502 in range 100 for irhp permit application 305'],
            $result->getMessages()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCriteriaAndExpiryDate')]
    public function testFirstRangeFullAllocatePermitInSecondRange(mixed $criteria, mixed $expiryDate): void
    {
        $criteria = m::mock(RangeMatchingCriteriaInterface::class);

        $pendingStatus = m::mock(RefData::class);
        $this->irhpPermitRepo->shouldReceive('getRefdataReference')
            ->with(IrhpPermit::STATUS_PENDING)
            ->andReturn($pendingStatus);

        $irhpPermitApplicationId = 400;
        $issueDate = m::mock(DateTime::class);

        $irhpPermitRange1Id = 101;
        $irhpPermitRange1AssignedNumbers = [504, 502, 500, 501, 503];
        $irhpPermitRange1 = $this->createMockRange($irhpPermitRange1Id, 500, 504, 5, m::mock(DateTime::class));

        $this->irhpPermitRepo->shouldReceive('getAssignedPermitNumbersByRange')
            ->with($irhpPermitRange1Id)
            ->andReturn($irhpPermitRange1AssignedNumbers);

        $irhpPermitRange2Id = 102;
        $irhpPermitRange2AssignedNumbers = [758, 757, 750, 751, 755];
        $irhpPermitRange2StockValidTo = m::mock(DateTime::class);
        $irhpPermitRange2 = $this->createMockRange($irhpPermitRange2Id, 750, 758, 9, $irhpPermitRange2StockValidTo);

        $this->irhpPermitRepo->shouldReceive('getAssignedPermitNumbersByRange')
            ->with($irhpPermitRange2Id)
            ->andReturn($irhpPermitRange2AssignedNumbers);

        $irhpPermitRanges = [$irhpPermitRange1, $irhpPermitRange2];

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getId')
            ->andReturn($irhpPermitApplicationId);
        $irhpPermitApplication->shouldReceive('generateIssueDate')
            ->withNoArgs()
            ->andReturn($issueDate);
        $irhpPermitApplication->shouldReceive(
            'getIrhpPermitWindow->getIrhpPermitStock->getNonReservedNonReplacementRangesOrderedByFromNo'
        )
        ->with($criteria)
        ->andReturn($irhpPermitRanges);

        $this->irhpPermitRepo->shouldReceive('save')
            ->once()
            ->with(m::on(function ($irhpPermit) use ($irhpPermitApplication, $irhpPermitRange2, $issueDate, $expiryDate, $pendingStatus) {
                $this->assertSame($irhpPermitApplication, $irhpPermit->getIrhpPermitApplication());
                $this->assertSame($irhpPermitRange2, $irhpPermit->getIrhpPermitRange());
                $this->assertSame($issueDate, $irhpPermit->getIssueDate());
                $this->assertSame($expiryDate, $irhpPermit->getExpiryDate());
                $this->assertEquals(752, $irhpPermit->getPermitNumber());
                $this->assertSame($pendingStatus, $irhpPermit->getStatus());
                return true;
            }));

        $result = new Result();

        $this->irhpPermitAllocator->allocate(
            $result,
            $irhpPermitApplication,
            $criteria,
            $expiryDate
        );

        $this->assertEquals(
            ['Allocated permit number 752 in range 102 for irhp permit application 400'],
            $result->getMessages()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCriteriaAndExpiryDate')]
    public function testExceptionOnAllRangesFull(mixed $criteria, mixed $expiryDate): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to find range with free permits for irhp permit application 400');

        $criteria = m::mock(RangeMatchingCriteriaInterface::class);

        $irhpPermitApplicationId = 400;

        $irhpPermitRange1Id = 101;
        $irhpPermitRange1AssignedNumbers = [504, 502, 500, 501, 503];
        $irhpPermitRange1 = $this->createMockRange($irhpPermitRange1Id, 500, 504, 5, m::mock(DateTime::class));

        $this->irhpPermitRepo->shouldReceive('getAssignedPermitNumbersByRange')
            ->with($irhpPermitRange1Id)
            ->andReturn($irhpPermitRange1AssignedNumbers);

        $irhpPermitRange2Id = 102;
        $irhpPermitRange2AssignedNumbers = [758, 757, 750, 751, 755, 752, 753, 754, 756];
        $irhpPermitRange2 = $this->createMockRange($irhpPermitRange2Id, 750, 758, 9, m::mock(DateTime::class));

        $this->irhpPermitRepo->shouldReceive('getAssignedPermitNumbersByRange')
            ->with($irhpPermitRange2Id)
            ->andReturn($irhpPermitRange2AssignedNumbers);

        $irhpPermitRanges = [$irhpPermitRange1, $irhpPermitRange2];

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getId')
            ->andReturn($irhpPermitApplicationId);
        $irhpPermitApplication->shouldReceive(
            'getIrhpPermitWindow->getIrhpPermitStock->getNonReservedNonReplacementRangesOrderedByFromNo'
        )
        ->with($criteria)
        ->andReturn($irhpPermitRanges);

        $result = new Result();

        $this->irhpPermitAllocator->allocate(
            $result,
            $irhpPermitApplication,
            $criteria,
            $expiryDate
        );
    }

    public static function dpCriteriaAndExpiryDate(): array
    {
        return [
            [m::mock(RangeMatchingCriteriaInterface::class), null],
            [null, null],
            [null, new DateTime('2030-10-22')],
        ];
    }

    private function createMockRange(mixed $id, mixed $fromNo, mixed $toNo, mixed $size, DateTime $stockValidTo): mixed
    {
        $irhpPermitRange = m::mock(IrhpPermitRange::class);
        $irhpPermitRange->shouldReceive('getId')
            ->andReturn($id);
        $irhpPermitRange->shouldReceive('getFromNo')
            ->andReturn($fromNo);
        $irhpPermitRange->shouldReceive('getToNo')
            ->andReturn($toNo);
        $irhpPermitRange->shouldReceive('getSize')
            ->andReturn($size);

        $irhpPermitRange->shouldReceive('getIrhpPermitStock->getValidTo')
            ->with(true)
            ->andReturn($stockValidTo);

        return $irhpPermitRange;
    }
}
