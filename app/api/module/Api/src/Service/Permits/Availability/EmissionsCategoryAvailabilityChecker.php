<?php

namespace Dvsa\Olcs\Api\Service\Permits\Availability;

class EmissionsCategoryAvailabilityChecker
{
    /**
     * Create service instance
     *
     *
     * @return EmissionsCategoryAvailabilityChecker
     */
    public function __construct(private readonly EmissionsCategoryAvailabilityCounter $emissionsCategoryAvailabilityCounter)
    {
    }

    /**
     * Whether there are permits available to apply for within the scope of a specific short term stock and emissions
     * category
     *
     * @param int $irhpPermitStockId
     * @param int $emissionsCategoryId
     *
     * @return bool
     */
    public function hasAvailability($irhpPermitStockId, $emissionsCategoryId)
    {
        $availableCount = $this->emissionsCategoryAvailabilityCounter->getCount(
            $irhpPermitStockId,
            $emissionsCategoryId
        );

        return($availableCount > 0);
    }
}
