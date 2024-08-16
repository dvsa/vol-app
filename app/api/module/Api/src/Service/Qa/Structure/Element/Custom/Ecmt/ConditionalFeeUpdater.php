<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;

class ConditionalFeeUpdater
{
    /**
     * Create service instance
     *
     *
     * @return ConditionalFeeUpdater
     */
    public function __construct(private readonly FeeUpdater $feeUpdater)
    {
    }

    /**
     * Update fees for an ecmt application if the permit count has changed
     *
     * @param IrhpApplicationEntity $irhpApplicationEntity
     * @param int $oldTotal
     */
    public function updateFees(IrhpApplicationEntity $irhpApplication, $oldTotal)
    {
        $irhpPermitApplication = $irhpApplication->getFirstIrhpPermitApplication();

        $newTotal = $irhpPermitApplication->getTotalEmissionsCategoryPermitsRequired();
        if ($newTotal != $oldTotal) {
            $this->feeUpdater->updateFees($irhpApplication, $newTotal);
        }
    }
}
