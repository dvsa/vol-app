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

    #[\Override]
    protected function canCreateOrReplaceFee(IrhpApplication $irhpApplication)
    {
        return $irhpApplication->canCreateOrReplaceApplicationFee();
    }

    #[\Override]
    protected function getOutstandingFees(IrhpApplication $irhpApplication)
    {
        return $irhpApplication->getOutstandingApplicationFees();
    }

    #[\Override]
    protected function getFeeProductRefsAndQuantities(IrhpApplication $irhpApplication)
    {
        return $irhpApplication->getApplicationFeeProductRefsAndQuantities();
    }
}
