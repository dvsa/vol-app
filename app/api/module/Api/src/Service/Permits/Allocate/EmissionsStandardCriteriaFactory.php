<?php

namespace Dvsa\Olcs\Api\Service\Permits\Allocate;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;

class EmissionsStandardCriteriaFactory
{
    /**
     * Create instance
     *
     * @param string $emissionsCategoryId
     *
     * @return EmissionsStandardCriteria
     */
    public function create($emissionsCategoryId)
    {
        return new EmissionsStandardCriteria($emissionsCategoryId);
    }
}
