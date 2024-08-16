<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

class ApplicationCountryUpdater
{
    /**
     * Create service instance
     *
     *
     * @return ApplicationCountryUpdater
     */
    public function __construct(private readonly IrhpPermitApplicationCreator $irhpPermitApplicationCreator, private readonly ExistingIrhpPermitApplicationHandler $existingIrhpPermitApplicationHandler)
    {
    }

    /**
     * Update the application using the supplied country code and application data
     *
     * @param string $countryId
     * @param int $stockId
     */
    public function update(IrhpApplication $irhpApplication, $countryId, $stockId, array $requiredPermits)
    {
        $irhpPermitApplication = $irhpApplication->getIrhpPermitApplicationByStockCountryId($countryId);
        if (is_null($irhpPermitApplication)) {
            $irhpPermitApplication = $this->irhpPermitApplicationCreator->create($irhpApplication, $stockId);
        }

        $this->existingIrhpPermitApplicationHandler->handle($irhpPermitApplication, $stockId, $requiredPermits);
    }
}
