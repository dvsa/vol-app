<?php

namespace Dvsa\Olcs\Api\Service\Permits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Availability\EmissionsCategoriesGrantabilityChecker;
use Dvsa\Olcs\Api\Service\Permits\Availability\CandidatePermitsGrantabilityChecker;
use RuntimeException;

class GrantabilityChecker
{
    /**
     * Create service instance
     *
     *
     * @return GrantabilityChecker
     */
    public function __construct(private readonly EmissionsCategoriesGrantabilityChecker $emissionsCategoriesGrantabilityChecker, private readonly CandidatePermitsGrantabilityChecker $candidatePermitsGrantabilityChecker)
    {
    }

    /**
     * Whether there is sufficient stock to grant the permits required by the application
     *
     *
     * @return bool
     */
    public function isGrantable(IrhpApplication $irhpApplication)
    {
        if ((string)$irhpApplication->getBusinessProcess() !== RefData::BUSINESS_PROCESS_APGG) {
            throw new RuntimeException('GrantabilityChecker is only implemented for APGG');
        }
        return match ($irhpApplication->getAllocationMode()) {
            IrhpPermitStock::ALLOCATION_MODE_EMISSIONS_CATEGORIES => $this->emissionsCategoriesGrantabilityChecker->isGrantable($irhpApplication),
            IrhpPermitStock::ALLOCATION_MODE_CANDIDATE_PERMITS => $this->candidatePermitsGrantabilityChecker->isGrantable($irhpApplication),
            default => throw new RuntimeException('Unable to grant application due to unsupported allocation mode'),
        };
    }
}
