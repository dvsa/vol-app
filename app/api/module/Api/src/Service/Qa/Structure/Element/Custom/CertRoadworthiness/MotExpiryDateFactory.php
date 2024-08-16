<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\CertRoadworthiness;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common\DateWithThreshold;

class MotExpiryDateFactory
{
    /**
     * Create and return a MotExpiryDate instance
     *
     * @param bool $enableFileUploads
     *
     * @return MotExpiryDate
     */
    public function create($enableFileUploads, DateWithThreshold $dateWithThreshold)
    {
        return new MotExpiryDate($enableFileUploads, $dateWithThreshold);
    }
}
