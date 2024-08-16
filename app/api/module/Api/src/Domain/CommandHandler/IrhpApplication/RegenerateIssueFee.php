<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Create issue fee (or replace if already present)
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RegenerateIssueFee extends AbstractRegenerateFee
{
    protected $feeName = 'Issue fee';

    protected function canCreateOrReplaceFee(IrhpApplication $irhpApplication)
    {
        return $irhpApplication->canCreateOrReplaceIssueFee();
    }

    protected function getOutstandingFees(IrhpApplication $irhpApplication)
    {
        return $irhpApplication->getOutstandingIssueFees();
    }

    protected function getFeeProductRefsAndQuantities(IrhpApplication $irhpApplication)
    {
        return $irhpApplication->getIssueFeeProductRefsAndQuantities();
    }
}
