<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Create application fee (or replace if already present)
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RegenerateApplicationFee extends AbstractRegenerateFee
{
    protected $feeName = 'Application fee';

    protected function canCreateOrReplaceFee(IrhpApplication $irhpApplication)
    {
        return $irhpApplication->canCreateOrReplaceApplicationFee();
    }

    protected function getOutstandingFees(IrhpApplication $irhpApplication)
    {
        return $irhpApplication->getOutstandingApplicationFees();
    }

    protected function getFeeProductRefsAndQuantities(IrhpApplication $irhpApplication)
    {
        return $irhpApplication->getApplicationFeeProductRefsAndQuantities();
    }
}
