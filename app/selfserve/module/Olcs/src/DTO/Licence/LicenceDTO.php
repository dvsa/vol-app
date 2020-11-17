<?php

namespace Olcs\DTO\Licence;

use Olcs\DTO\DataTransferObject;

class LicenceDTO extends DataTransferObject
{
    /**
     * Gets the licence number for a licence.
     *
     * @return string|null
     */
    public function getLicenceNumber(): ?string
    {
        return $this->data['licNo'] ?? null;
    }

    /**
     * Gets the number of active vehicles that a licence is associated with.
     *
     * @return int
     */
    public function getActiveVehicleCount(): int
    {
        return (int) ($this->data['activeVehicleCount'] ?? 0);
    }
}
