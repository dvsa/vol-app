<?php

namespace Dvsa\Olcs\Api\Service\Permits\Scoring;

use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;

class IrhpCandidatePermitFactory
{
    /**
     * Create new instance of candidate permit
     *
     * @param float $intensityOfUse
     * @param float $applicationScore
     *
     * @return IrhpCandidatePermit
     */
    public function create(
        IrhpPermitApplication $irhpPermitApplication,
        RefData $requestedEmissionsCategory,
        $intensityOfUse,
        $applicationScore
    ) {
        return IrhpCandidatePermit::createNew(
            $irhpPermitApplication,
            $requestedEmissionsCategory,
            $intensityOfUse,
            $applicationScore
        );
    }
}
