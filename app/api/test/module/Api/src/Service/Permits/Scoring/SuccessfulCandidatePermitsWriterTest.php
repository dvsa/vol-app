<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Scoring;

use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepository;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Scoring\SuccessfulCandidatePermitsWriter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * SuccessfulCandidatePermitsWriterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SuccessfulCandidatePermitsWriterTest extends MockeryTestCase
{
    public function testWrite(): void
    {
        $candidatePermit1Id = 1;
        $candidatePermit1EmissionsCategory = RefData::EMISSIONS_CATEGORY_EURO6_REF;

        $candidatePermit2Id = 3;
        $candidatePermit2EmissionsCategory = RefData::EMISSIONS_CATEGORY_EURO5_REF;

        $candidatePermit3Id = 8;
        $candidatePermit3EmissionsCategory = RefData::EMISSIONS_CATEGORY_EURO6_REF;

        $candidatePermits = [
            [
                'id' => $candidatePermit1Id,
                'emissions_category' => $candidatePermit1EmissionsCategory
            ],
            [
                'id' => $candidatePermit2Id,
                'emissions_category' => $candidatePermit2EmissionsCategory
            ],
            [
                'id' => $candidatePermit3Id,
                'emissions_category' => $candidatePermit3EmissionsCategory
            ],
        ];

        $euro5RefData = m::mock(RefData::class);
        $euro6RefData = m::mock(RefData::class);

        $candidatePermit1Entity = m::mock(IrhpCandidatePermit::class);
        $candidatePermit1Entity->shouldReceive('markAsSuccessful')
            ->with($euro6RefData)
            ->once()
            ->globally()
            ->ordered();

        $candidatePermit2Entity = m::mock(IrhpCandidatePermit::class);
        $candidatePermit2Entity->shouldReceive('markAsSuccessful')
            ->with($euro5RefData)
            ->once()
            ->globally()
            ->ordered();

        $candidatePermit3Entity = m::mock(IrhpCandidatePermit::class);
        $candidatePermit3Entity->shouldReceive('markAsSuccessful')
            ->with($euro6RefData)
            ->once()
            ->globally()
            ->ordered();

        $irhpCandidatePermitRepo = m::mock(IrhpCandidatePermitRepository::class);
        $irhpCandidatePermitRepo->shouldReceive('fetchById')
            ->with($candidatePermit1Id)
            ->andReturn($candidatePermit1Entity);
        $irhpCandidatePermitRepo->shouldReceive('fetchById')
            ->with($candidatePermit2Id)
            ->andReturn($candidatePermit2Entity);
        $irhpCandidatePermitRepo->shouldReceive('fetchById')
            ->with($candidatePermit3Id)
            ->andReturn($candidatePermit3Entity);
        $irhpCandidatePermitRepo->shouldReceive('getRefDataReference')
            ->with(RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($euro5RefData);
        $irhpCandidatePermitRepo->shouldReceive('getRefDataReference')
            ->with(RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($euro6RefData);
        $irhpCandidatePermitRepo->shouldReceive('flushAll')
            ->once()
            ->globally()
            ->ordered();

        $successfulCandidatePermitsWriter = new SuccessfulCandidatePermitsWriter($irhpCandidatePermitRepo);
        $successfulCandidatePermitsWriter->write($candidatePermits);
    }
}
