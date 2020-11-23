<?php

namespace Olcs\Exception\Licence;

use Exception;
use Throwable;

class LicenceVehicleLimitReachedException extends Exception
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
     * @param int $licenceId
     * @param string $licenceNumber
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(int $licenceId, string $licenceNumber, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->licenceId = $licenceId;
        $this->licenceNumber = $licenceNumber;
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
}
