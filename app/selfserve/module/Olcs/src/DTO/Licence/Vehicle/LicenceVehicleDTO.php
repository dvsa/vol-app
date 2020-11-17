<?php

namespace Olcs\DTO\Licence\Vehicle;

use Olcs\DTO\DataTransferObject;

class LicenceVehicleDTO extends DataTransferObject
{
    /**
     * @return Vehicle
     */
    public function getVehicle(): Vehicle
    {
        return new Vehicle($this->data['vehicle']);
    }
}
