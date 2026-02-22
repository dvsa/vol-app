<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Scoring;

use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as IrhpPermitRangeRepository;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Scoring\EmissionsCategoryAvailabilityCounter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EmissionsCategoryAvailabilityCounterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EmissionsCategoryAvailabilityCounterTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetCount')]
    public function testGetCount(mixed $emissionsCategoryId): void
    {
        $combinedRangeSize = 160;
        $allocatedCount = 25;
        $successfulCount = 32;
        $expectedAvailableCount = 103;
        $stockId = 7;

        $irhpPermitRangeRepo = m::mock(IrhpPermitRangeRepository::class);
        $irhpPermitRangeRepo->shouldReceive('getCombinedRangeSize')
            ->with($stockId, $emissionsCategoryId)
            ->once()
            ->andReturn($combinedRangeSize);

        $irhpPermitRepo = m::mock(IrhpPermitRepository::class);
        $irhpPermitRepo->shouldReceive('getPermitCount')
            ->with($stockId, $emissionsCategoryId)
            ->once()
            ->andReturn($allocatedCount);

        $irhpApplicationRepo = m::mock(IrhpApplicationRepository::class);
        $irhpApplicationRepo->shouldReceive('getSuccessfulCountInScope')
            ->with($stockId, $emissionsCategoryId)
            ->once()
            ->andReturn($successfulCount);

        $emissionsCategoryAvailabilityCounter = new EmissionsCategoryAvailabilityCounter(
            $irhpPermitRangeRepo,
            $irhpPermitRepo,
            $irhpApplicationRepo
        );

        $this->assertEquals(
            $expectedAvailableCount,
            $emissionsCategoryAvailabilityCounter->getCount($stockId, $emissionsCategoryId)
        );
    }

    public static function dpTestGetCount(): array
    {
        return [
            [RefData::EMISSIONS_CATEGORY_EURO6_REF],
            [RefData::EMISSIONS_CATEGORY_EURO5_REF],
        ];
    }
}
