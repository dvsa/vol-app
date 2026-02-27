<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Availability;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Service\Permits\Availability\CandidatePermitsAvailableCountCalculator;
use Dvsa\Olcs\Api\Service\Permits\Availability\CandidatePermitsGrantabilityChecker;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CandidatePermitsGrantabilityCheckerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CandidatePermitsGrantabilityCheckerTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsGrantable')]
    public function testIsGrantable(mixed $range1Count, mixed $range2Count, mixed $range3Count, mixed $expectedResult): void
    {
        $irhpPermitRange1RequestedPermits = 6;
        $irhpPermitRange1 = m::mock(IrhpPermitRange::class);

        $irhpPermitRange2RequestedPermits = 3;
        $irhpPermitRange2 = m::mock(IrhpPermitRange::class);

        $irhpPermitRange3RequestedPermits = 14;
        $irhpPermitRange3 = m::mock(IrhpPermitRange::class);

        $rangesWithCandidatePermitCounts = [
            23 => [
                IrhpPermitApplication::RANGE_ENTITY_KEY => $irhpPermitRange1,
                IrhpPermitApplication::REQUESTED_PERMITS_KEY => $irhpPermitRange1RequestedPermits
            ],
            34 => [
                IrhpPermitApplication::RANGE_ENTITY_KEY => $irhpPermitRange2,
                IrhpPermitApplication::REQUESTED_PERMITS_KEY => $irhpPermitRange2RequestedPermits
            ],
            62 => [
                IrhpPermitApplication::RANGE_ENTITY_KEY => $irhpPermitRange3,
                IrhpPermitApplication::REQUESTED_PERMITS_KEY => $irhpPermitRange3RequestedPermits
            ],
        ];

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getRangesWithCandidatePermitCounts')
            ->withNoArgs()
            ->andReturn($rangesWithCandidatePermitCounts);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getFirstIrhpPermitApplication')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $candidatePermitsAvailableCountCalculator = m::mock(CandidatePermitsAvailableCountCalculator::class);
        $candidatePermitsAvailableCountCalculator->shouldReceive('getCount')
            ->with($irhpPermitRange1, $irhpPermitRange1RequestedPermits)
            ->andReturn($range1Count);
        $candidatePermitsAvailableCountCalculator->shouldReceive('getCount')
            ->with($irhpPermitRange2, $irhpPermitRange2RequestedPermits)
            ->andReturn($range2Count);
        $candidatePermitsAvailableCountCalculator->shouldReceive('getCount')
            ->with($irhpPermitRange3, $irhpPermitRange3RequestedPermits)
            ->andReturn($range3Count);

        $candidatePermitsGrantabilityChecker = new CandidatePermitsGrantabilityChecker(
            $candidatePermitsAvailableCountCalculator
        );

        $this->assertEquals(
            $expectedResult,
            $candidatePermitsGrantabilityChecker->isGrantable($irhpApplication)
        );
    }

    public static function dpIsGrantable(): array
    {
        return [
            [3, 5, 0, true],
            [10, -1, 9, false]
        ];
    }
}
