<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;

class BilateralRequiredGenerator
{
    /**
     * Generate an array to pass to IrhpPermitApplication::updateBilateralRequired using the supplied post data
     *
     * @param string $permitUsageSelection
     * @return array
     */
    public function generate(array $postData, $permitUsageSelection)
    {
        $standardRequired = $this->generateStandardOrCabotageRequired(
            $postData,
            $permitUsageSelection,
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED
        );

        $cabotageRequired = $this->generateStandardOrCabotageRequired(
            $postData,
            $permitUsageSelection,
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED
        );

        $bilateralRequired = IrhpPermitApplication::DEFAULT_BILATERAL_REQUIRED;
        $bilateralRequired[IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED] = $standardRequired;
        $bilateralRequired[IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED] = $cabotageRequired;

        return $bilateralRequired;
    }

    /**
     * Retrieve a value from the post data for use in the bilateralRequired array
     *
     * @param string $permitUsageSelection
     * @param string $standardOrCabotage
     * @return int|null
     */
    private function generateStandardOrCabotageRequired(
        array $postData,
        $permitUsageSelection,
        $standardOrCabotage
    ) {
        $requiredKey = $standardOrCabotage . '-' . $permitUsageSelection;

        return $postData[$requiredKey] ?? null;
    }
}
