<?php

namespace Dvsa\Olcs\Api\Service\Permits\Availability;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;

class EmissionsCategoriesGrantabilityChecker
{
    public function __construct(private readonly EmissionsCategoryAvailabilityCounter $emissionsCategoryAvailabilityCounter)
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
        $irhpPermitApplication = $irhpApplication->getFirstIrhpPermitApplication();
        $irhpPermitStockId = $irhpPermitApplication->getIrhpPermitWindow()->getIrhpPermitStock()->getId();

        $isEuro5Grantable = $this->isEmissionsCategoryGrantable(
            $irhpPermitStockId,
            RefData::EMISSIONS_CATEGORY_EURO5_REF,
            $irhpPermitApplication->getRequiredEuro5()
        );

        if (!$isEuro5Grantable) {
            return false;
        }

        return $this->isEmissionsCategoryGrantable(
            $irhpPermitStockId,
            RefData::EMISSIONS_CATEGORY_EURO6_REF,
            $irhpPermitApplication->getRequiredEuro6()
        );
    }

    /**
     * Whether there is sufficient stock to grant the required number of permits in a specific emissions category
     *
     * @param int $irhpPermitStockId
     * @param string $emissionsCategoryId
     * @param int $requiredCount
     *
     * @return bool
     */
    private function isEmissionsCategoryGrantable($irhpPermitStockId, $emissionsCategoryId, $requiredCount)
    {
        $availableCount = $this->emissionsCategoryAvailabilityCounter->getCount(
            $irhpPermitStockId,
            $emissionsCategoryId
        );

        return ($requiredCount <= $availableCount);
    }
}
