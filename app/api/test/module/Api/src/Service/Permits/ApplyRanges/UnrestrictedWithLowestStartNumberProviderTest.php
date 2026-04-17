<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\UnrestrictedWithLowestStartNumberProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * UnrestrictedWithLowestStartNumberProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class UnrestrictedWithLowestStartNumberProviderTest extends MockeryTestCase
{
    public function testGetRanges(): void
    {
        $range1 = $this->createMockRange(['HU', 'IT'], 200);
        $range2 = $this->createMockRange([], 400);
        $range3 = $this->createMockRange(['GR', 'IT', 'AT'], 300);
        $range4 = $this->createMockRange([], 100);
        $range5 = $this->createMockRange(['AT'], 50);

        $ranges = [$range1, $range2, $range3, $range4, $range5];
        $unrestrictedWithLowestStartNumberProvider = new UnrestrictedWithLowestStartNumberProvider();

        $this->assertEquals(
            $range4,
            $unrestrictedWithLowestStartNumberProvider->getRange($ranges)
        );
    }

    public function testGetRangesNullOnNoUnrestrictedRanges(): void
    {
        $range1 = $this->createMockRange(['HU', 'IT'], 200);
        $range2 = $this->createMockRange(['IT'], 400);
        $range3 = $this->createMockRange(['GR', 'IT', 'AT'], 300);
        $range4 = $this->createMockRange(['AT', 'GR'], 100);
        $range5 = $this->createMockRange(['AT'], 50);

        $ranges = [$range1, $range2, $range3, $range4, $range5];
        $unrestrictedWithLowestStartNumberProvider = new UnrestrictedWithLowestStartNumberProvider();

        $this->assertNull(
            $unrestrictedWithLowestStartNumberProvider->getRange($ranges)
        );
    }

    private function createMockRange(mixed $countryIds, mixed $fromNo): array
    {
        $rangeEntity = m::mock(IrhpPermitRange::class);
        $rangeEntity->shouldReceive('getFromNo')
            ->andReturn($fromNo);

        return [
            'countryIds' => $countryIds,
            'entity' => $rangeEntity
        ];
    }
}
