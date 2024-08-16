<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\System\RefData;

class CurrentFieldValuesGenerator
{
    /**
     * Get the requested permits from the database in a normalised format
     *
     * @param IrhpPermitApplication $irhpPermitApplication (optional)
     * @return array
     */
    public function generate(IrhpPermitStock $irhpPermitStock, ?IrhpPermitApplication $irhpPermitApplication)
    {
        $currentFieldValues = [
            RefData::JOURNEY_SINGLE => [
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null
            ],
            RefData::JOURNEY_MULTIPLE => [
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null
            ]
        ];

        if (
            is_object($irhpPermitApplication) &&
            $irhpPermitApplication->getIrhpPermitWindow()->getIrhpPermitStock() === $irhpPermitStock
        ) {
            $permitUsageSelection = $irhpPermitApplication->getBilateralPermitUsageSelection();
            if (!is_null($permitUsageSelection)) {
                $currentFieldValues[$permitUsageSelection] = $irhpPermitApplication->getBilateralRequired();
            }
        }

        return $currentFieldValues;
    }
}
