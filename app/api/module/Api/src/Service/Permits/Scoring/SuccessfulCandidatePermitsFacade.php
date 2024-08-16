<?php

namespace Dvsa\Olcs\Api\Service\Permits\Scoring;

use Dvsa\Olcs\Api\Domain\Command\Result;

class SuccessfulCandidatePermitsFacade
{
    /**
     * Create service instance
     *
     *
     * @return SuccessfulCandidatePermitsFacade
     */
    public function __construct(private readonly SuccessfulCandidatePermitsGenerator $successfulCandidatePermitsGenerator, private readonly SuccessfulCandidatePermitsWriter $successfulCandidatePermitsWriter, private readonly SuccessfulCandidatePermitsLogger $successfulCandidatePermitsLogger)
    {
    }

    /**
     * Get an array containing successful candidate permit ids and the emissions categories assigned to them
     *
     * @param int $stockId
     * @param int $quotaRemaining
     * @return array
     */
    public function generate(array $candidatePermits, $stockId, $quotaRemaining)
    {
        return $this->successfulCandidatePermitsGenerator->generate($candidatePermits, $stockId, $quotaRemaining);
    }

    /**
     * Marks a series of candidate permits as successful and records the emissions category assigned to each
     */
    public function write(array $candidatePermits)
    {
        $this->successfulCandidatePermitsWriter->write($candidatePermits);
    }

    /**
     * Logs a list of successful candidate permits to a CQRS result object
     */
    public function log(array $candidatePermits, Result $result)
    {
        $this->successfulCandidatePermitsLogger->log($candidatePermits, $result);
    }
}
