<?php

namespace Dvsa\Olcs\Api\Service\Permits\Allocate;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;

class BilateralCriteria implements RangeMatchingCriteriaInterface
{
    public const CABOTAGE_MAPPINGS = [
        IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => false,
        IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => true,
    ];

    /**
     * Create instance
     *
     * @param string $standardOrCabotage
     * @param string $journey
     *
     * @return BilateralCriteria
     */
    public function __construct(private $standardOrCabotage, private $journey)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function matches(IrhpPermitRange $irhpPermitRange)
    {
        return $irhpPermitRange->getCabotage() == self::CABOTAGE_MAPPINGS[$this->standardOrCabotage] &&
            $irhpPermitRange->getJourney()->getId() == $this->journey;
    }
}
