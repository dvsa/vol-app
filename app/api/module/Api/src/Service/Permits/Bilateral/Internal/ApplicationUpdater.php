<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

class ApplicationUpdater
{
    /**
     * Create service instance
     *
     *
     * @return ApplicationUpdater
     */
    public function __construct(private readonly ApplicationCountryUpdater $applicationCountryUpdater)
    {
    }

    /**
     * Update the application using the supplied fields data
     */
    public function update(IrhpApplication $irhpApplication, array $countries)
    {
        foreach ($countries as $countryId => $periodData) {
            $selectedStockId = $periodData['periodId'];
            $requiredPermits = $periodData['permitsRequired'];

            $this->applicationCountryUpdater->update(
                $irhpApplication,
                $countryId,
                $selectedStockId,
                $requiredPermits
            );
        }
    }
}
