<?php

namespace Olcs\Exception\Licence\Vehicle;

use Common\Exception\ResourceNotFoundException;
use Throwable;

class VehiclesNotFoundWithIdsException extends ResourceNotFoundException
{
    /**
     * @var int
     */
    protected $vehicleIds;

    /**
     * @param array<int> $vehicleIds
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(array $vehicleIds, string $message = null, int $code = 0, Throwable $previous = null)
    {
        $this->vehicleIds = $vehicleIds;
        if (null === $message) {
            $idsString = implode(', ', $vehicleIds);
            $message = sprintf('One or more of the following vehicles were not found: %s', $idsString);
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getVehicleIds(): int
    {
        return $this->vehicleIds;
    }
}
