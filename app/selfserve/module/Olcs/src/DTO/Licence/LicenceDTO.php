<?php

namespace Olcs\DTO\Licence;

use Olcs\DTO\DataTransferObject;

class LicenceDTO extends DataTransferObject
{
    protected const ATTRIBUTE_ID = 'id';
    protected const ATTRIBUTE_LICENCE_NUMBER = 'licNo';
    protected const ATTRIBUTE_ACTIVE_VEHICLE_COUNT = 'activeVehicleCount';

    /**
     * Gets the id of a licence.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        $id = $this->data[static::ATTRIBUTE_ID] ?? null;
        return null === $id ? null : (int) $id;
    }

    /**
     * Gets the licence number for a licence.
     *
     * @return string|null
     */
    public function getLicenceNumber(): ?string
    {
        $id = $this->data[static::ATTRIBUTE_LICENCE_NUMBER] ?? null;
        return null === $id ? null : (string) $id;
    }

    /**
     * Gets the number of active vehicles that a licence is associated with.
     *
     * @return int|null
     */
    public function getActiveVehicleCount(): ?int
    {
        $count = $this->data[static::ATTRIBUTE_ACTIVE_VEHICLE_COUNT] ?? null;
        return null === $count ? $count : (int) $count;
    }
}
