<?php

namespace Olcs\Exception\Licence\Vehicle;

use Exception;

class VehicleNotFoundException extends Exception
{
    /**
     * Creates a new exception where one or more vehicles were not found from a given set of ids.
     *
     * @param array $vehicleIds
     * @return static
     */
    public static function withIds(array $vehicleIds)
    {
        $idsString = implode(', ', $vehicleIds);
        return new static(sprintf('One or more of the following vehicles was not found: %s.', $idsString));
    }
}
