<?php

namespace Dvsa\Olcs\Api\Service\Permits\CandidatePermits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;

class ApggCandidatePermitFactory
{
    /**
     * Create an instance for use in an APGG context
     *
     *
     * @return IrhpCandidatePermit
     */
    public function create(IrhpPermitApplication $irhpPermitApplication, IrhpPermitRange $irhpPermitRange)
    {
        return IrhpCandidatePermit::createForApgg($irhpPermitApplication, $irhpPermitRange);
    }
}
