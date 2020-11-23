<?php


namespace Olcs\DTO\Licence\Vehicle;

use Olcs\DTO\DataTransferObject;

class VehicleDTO extends DataTransferObject
{
    /**
     * @return string|null
     */
    public function getVrm(): ?string
    {
        $vrm = $this->data['vrm'] ?? null;
        return null === $vrm ? null : (string) $vrm;
    }
}
