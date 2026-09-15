<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * EcmtTrips
 */
trait EcmtTrips
{
    /**
     * @var int
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 999999})
     */
    protected $ecmtTrips;

    /**
     * @return int
     */
    public function getEcmtTrips()
    {
        return $this->ecmtTrips;
    }
}
