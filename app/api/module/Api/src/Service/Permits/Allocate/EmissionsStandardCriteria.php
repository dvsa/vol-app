<?php

namespace Dvsa\Olcs\Api\Service\Permits\Allocate;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;

class EmissionsStandardCriteria implements RangeMatchingCriteriaInterface
{
    /**
     * Create instance
     *
     * @param string $emissionsCategoryId
     *
     * @return EmissionsStandardCriteria
     */
    public function __construct(private $emissionsCategoryId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function matches(IrhpPermitRange $irhpPermitRange)
    {
        return $irhpPermitRange->getEmissionsCategory()->getId() == $this->emissionsCategoryId;
    }
}
