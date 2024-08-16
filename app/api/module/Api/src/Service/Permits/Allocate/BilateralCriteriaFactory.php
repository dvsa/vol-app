<?php

namespace Dvsa\Olcs\Api\Service\Permits\Allocate;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;

class BilateralCriteriaFactory
{
    /**
     * Create instance
     *
     * @param bool $standardOrCabotage
     * @param string $journey
     *
     * @return BilateralCriteria
     */
    public function create($standardOrCabotage, $journey)
    {
        return new BilateralCriteria($standardOrCabotage, $journey);
    }
}
