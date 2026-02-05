<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Scoring;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Scoring\SuccessfulCandidatePermitsFacade;
use Dvsa\Olcs\Api\Service\Permits\Scoring\SuccessfulCandidatePermitsGenerator;
use Dvsa\Olcs\Api\Service\Permits\Scoring\SuccessfulCandidatePermitsLogger;
use Dvsa\Olcs\Api\Service\Permits\Scoring\SuccessfulCandidatePermitsWriter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * SuccessfulCandidatePermitsFacadeTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SuccessfulCandidatePermitsFacadeTest extends MockeryTestCase
{
    private $candidatePermits;

    private $successfulCandidatePermitsGenerator;

    private $successfulCandidatePermitsWriter;

    private $successfulCandidatePermitsLogger;

    private $successfulCandidatePermitsFacade;

    public function setUp(): void
    {
        $this->candidatePermits = [
            [
                'id' => 1,
                'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF
            ],
            [
                'id' => 5,
                'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF
            ],
            [
                'id' => 8,
                'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF
            ],
        ];

        $this->successfulCandidatePermitsGenerator = m::mock(SuccessfulCandidatePermitsGenerator::class);

        $this->successfulCandidatePermitsWriter = m::mock(SuccessfulCandidatePermitsWriter::class);

        $this->successfulCandidatePermitsLogger = m::mock(SuccessfulCandidatePermitsLogger::class);

        $this->successfulCandidatePermitsFacade = new SuccessfulCandidatePermitsFacade(
            $this->successfulCandidatePermitsGenerator,
            $this->successfulCandidatePermitsWriter,
            $this->successfulCandidatePermitsLogger
        );
    }

    public function testGenerate(): void
    {
        $stockId = 47;
        $quotaRemaining = 22;

        $generatedCandidatePermits = [
            [
                'id' => 5,
                'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF
            ],
            [
                'id' => 8,
                'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF
            ]
        ];

        $this->successfulCandidatePermitsGenerator->shouldReceive('generate')
            ->with($this->candidatePermits, $stockId, $quotaRemaining)
            ->andReturn($generatedCandidatePermits);

        $this->assertEquals(
            $generatedCandidatePermits,
            $this->successfulCandidatePermitsFacade->generate($this->candidatePermits, $stockId, $quotaRemaining)
        );
    }

    public function testWrite(): void
    {
        $this->successfulCandidatePermitsWriter->shouldReceive('write')
            ->with($this->candidatePermits)
            ->once();

        $this->successfulCandidatePermitsFacade->write($this->candidatePermits);
    }

    public function testLog(): void
    {
        $result = new Result();

        $this->successfulCandidatePermitsLogger->shouldReceive('log')
            ->with($this->candidatePermits, $result)
            ->once();

        $this->successfulCandidatePermitsFacade->log($this->candidatePermits, $result);
    }
}
