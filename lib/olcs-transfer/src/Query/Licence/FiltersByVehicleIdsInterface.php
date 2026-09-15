<?php

namespace Dvsa\Olcs\Transfer\Query\Licence;

interface FiltersByVehicleIdsInterface
{
    /**
     * Gets the vehicle ids which should be used to filter results.
     *
     * @return array|null
     */
    public function getVehicleIds(): ?array;
}
