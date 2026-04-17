<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Availability;

use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Service\Permits\Availability\CandidatePermitsAvailableCountCalculator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CandidatePermitsAvailableCountCalculatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CandidatePermitsAvailableCountCalculatorTest extends MockeryTestCase
{
    public function testGetCount(): void
    {
        $rangeId = 12;
        $rangeSize = 120;
        $issuedCount = 43;
        $grantedCount = 16;
        $permitsRequired = 4;
        $expectedCount = 57;

        $irhpPermitRange = m::mock(IrhpPermitRange::class);
        $irhpPermitRange->shouldReceive('getId')
            ->andReturn($rangeId);
        $irhpPermitRange->shouldReceive('getSize')
            ->andReturn($rangeSize);

        $irhpCandidatePermitRepo = m::mock(IrhpCandidatePermitRepository::class);
        $irhpCandidatePermitRepo->shouldReceive('fetchCountInRangeWhereApplicationAwaitingFee')
            ->with($rangeId)
            ->andReturn($grantedCount);

        $irhpPermitRepo = m::mock(IrhpPermitRepository::class);
        $irhpPermitRepo->shouldReceive('getPermitCountByRange')
            ->with($rangeId)
            ->andReturn($issuedCount);

        $candidatePermitsAvailableCountCalculator = new CandidatePermitsAvailableCountCalculator(
            $irhpCandidatePermitRepo,
            $irhpPermitRepo
        );

        $this->assertEquals(
            $expectedCount,
            $candidatePermitsAvailableCountCalculator->getCount($irhpPermitRange, $permitsRequired)
        );
    }
}
