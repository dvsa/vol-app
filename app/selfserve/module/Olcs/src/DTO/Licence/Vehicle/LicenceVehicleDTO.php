<?php

namespace Olcs\DTO\Licence\Vehicle;

use Olcs\DTO\DataTransferObject;

class LicenceVehicleDTO extends DataTransferObject
{
    /**
     * @return VehicleDTO
     */
    public function getVehicle(): VehicleDTO
    {
        return new VehicleDTO($this->data['vehicle']);
    }
}
