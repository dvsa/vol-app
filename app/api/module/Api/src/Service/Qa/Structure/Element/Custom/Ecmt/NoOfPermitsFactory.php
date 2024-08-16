<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

class NoOfPermitsFactory
{
    /**
     * Create and return a NoOfPermits instance
     *
     * @param int $maxCanApplyFor
     * @param int $maxPermitted
     * @param int $applicationFee
     * @param int $issueFee
     * @param bool $skipAvailabilityValidation
     *
     * @return NoOfPermits
     */
    public function create($maxCanApplyFor, $maxPermitted, $applicationFee, $issueFee, $skipAvailabilityValidation)
    {
        return new NoOfPermits($maxCanApplyFor, $maxPermitted, $applicationFee, $issueFee, $skipAvailabilityValidation);
    }
}
