<?php

namespace Olcs\Exception\Licence\Vehicle;

use Exception;
use Throwable;

class LicenceAlreadyAssignedVehicleException extends Exception
{
    /**
     * @var int
     */
    protected $licenceId;

    /**
     * @var string
     */
    protected $licenceNumber;

    /**
     * @var array<string>
     */
    protected $vehicleVrms;

    /**
     * @param array<string> $vehicleVrms
     * @param Throwable|null $previous
     */
    public function __construct(int $licenceId, string $licenceNumber, array $vehicleVrms, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->licenceId = $licenceId;
        $this->licenceNumber = $licenceNumber;
        $this->vehicleVrms = $vehicleVrms;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getLicenceId(): int
    {
        return $this->licenceId;
    }

    /**
     * @return string
     */
    public function getLicenceNumber(): string
    {
        return $this->licenceNumber;
    }

    /**
     * @return array<string>
     */
    public function getVehicleVrms(): array
    {
        return $this->vehicleVrms;
    }
}
