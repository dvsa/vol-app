<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Vehicle Type
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait VehicleType
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator(
     *     "Laminas\Validator\InArray", options={"haystack": {"app_veh_type_mixed","app_veh_type_lgv"}}
     * )
     * @Transfer\Optional
     */
    protected $vehicleType;

    /**
     * @return mixed
     */
    public function getVehicleType()
    {
        return $this->vehicleType;
    }
}
