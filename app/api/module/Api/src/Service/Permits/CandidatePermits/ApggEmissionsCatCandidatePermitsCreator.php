<?php

namespace Dvsa\Olcs\Api\Service\Permits\CandidatePermits;

use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Service\Permits\Allocate\EmissionsStandardCriteriaFactory;

class ApggEmissionsCatCandidatePermitsCreator
{
    /**
     * Create service instance
     *
     *
     * @return ApggEmissionsCatCandidatePermitsCreator
     */
    public function __construct(private readonly ApggCandidatePermitFactory $apggCandidatePermitFactory, private readonly IrhpCandidatePermitRepository $irhpCandidatePermitRepo, private readonly EmissionsStandardCriteriaFactory $emissionsStandardCriteriaFactory)
    {
    }

    /**
     * Create apgg candidate permits as required for the specified application
     *
     * @param string $emissionsCategoryId
     */
    public function createIfRequired(IrhpApplication $irhpApplication, $emissionsCategoryId)
    {
        $irhpPermitApplication = $irhpApplication->getFirstIrhpPermitApplication();
        $permitsRequired = $irhpPermitApplication->getRequiredPermitsByEmissionsCategory($emissionsCategoryId);

        if ($permitsRequired > 0) {
            $emissionsStandardCriteria = $this->emissionsStandardCriteriaFactory->create($emissionsCategoryId);

            $irhpPermitRange = $irhpApplication->getAssociatedStock()
                ->getFirstAvailableRangePreferWithNoCountries($emissionsStandardCriteria);

            for ($index = 0; $index < $permitsRequired; $index++) {
                $irhpCandidatePermit = $this->apggCandidatePermitFactory->create($irhpPermitApplication, $irhpPermitRange);
                $this->irhpCandidatePermitRepo->save($irhpCandidatePermit);
            }
        }
    }
}
