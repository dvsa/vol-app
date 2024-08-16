<?php

namespace Dvsa\Olcs\Api\Service\Permits\CandidatePermits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Service\Permits\Scoring\CandidatePermitsCreator as ScoringCandidatePermitsCreator;

class IrhpCandidatePermitsCreator
{
    /**
     * Create service instance
     *
     *
     * @return IrhpCandidatePermitsCreator
     */
    public function __construct(private readonly ScoringCandidatePermitsCreator $scoringCandidatePermitsCreator, private readonly ApggCandidatePermitsCreator $apggCandidatePermitsCreator)
    {
    }

    /**
     * Create candidate permits as required for the specified application
     */
    public function createIfRequired(IrhpApplication $irhpApplication)
    {
        $candidatePermitCreationMode = $irhpApplication->getCandidatePermitCreationMode();

        switch ($candidatePermitCreationMode) {
            case IrhpPermitStock::CANDIDATE_MODE_APSG:
                $firstIrhpPermitApplication = $irhpApplication->getFirstIrhpPermitApplication();

                $this->scoringCandidatePermitsCreator->create(
                    $firstIrhpPermitApplication,
                    $firstIrhpPermitApplication->getRequiredEuro5(),
                    $firstIrhpPermitApplication->getRequiredEuro6()
                );
                break;
            case IrhpPermitStock::CANDIDATE_MODE_APGG:
                $this->apggCandidatePermitsCreator->create($irhpApplication);
                break;
        }
    }
}
