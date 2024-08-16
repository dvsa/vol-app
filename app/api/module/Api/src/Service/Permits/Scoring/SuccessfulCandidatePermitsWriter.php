<?php

namespace Dvsa\Olcs\Api\Service\Permits\Scoring;

use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepository;

class SuccessfulCandidatePermitsWriter
{
    /**
     * Create service instance
     *
     *
     * @return SuccessfulCandidatePermitsWriter
     */
    public function __construct(private readonly IrhpCandidatePermitRepository $irhpCandidatePermitRepo)
    {
    }

    /**
     * Marks a series of candidate permits as successful and records the emissions category assigned to each
     */
    public function write(array $candidatePermits)
    {
        foreach ($candidatePermits as $candidatePermit) {
            $emissionsCategoryReference = $this->irhpCandidatePermitRepo->getRefDataReference(
                $candidatePermit['emissions_category']
            );

            $entity = $this->irhpCandidatePermitRepo->fetchById($candidatePermit['id']);
            $entity->markAsSuccessful($emissionsCategoryReference);
        }

        $this->irhpCandidatePermitRepo->flushAll();
    }
}
