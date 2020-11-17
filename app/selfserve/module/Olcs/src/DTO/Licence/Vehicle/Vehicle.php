<?php


namespace Olcs\DTO\Licence\Vehicle;

use Olcs\DTO\DataTransferObject;

class Vehicle extends DataTransferObject
{
    /**
     * @return string
     */
    public function getVrm(): string
    {
        return $this->data['vrm'];
    }
}
