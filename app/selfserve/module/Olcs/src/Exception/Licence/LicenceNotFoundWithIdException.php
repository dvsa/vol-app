<?php

namespace Olcs\Exception\Licence;

use Exception;
use Throwable;

class LicenceNotFoundWithIdException extends Exception
{
    /**
     * @var int
     */
    protected $licenceId;

    /**
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(int $licenceId, string $message = null, int $code = 0, Throwable $previous = null)
    {
        $this->licenceId = $licenceId;
        $message ??= sprintf('Licence was not found with id: "%s".', $licenceId);
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getLicenceId(): int
    {
        return $this->licenceId;
    }
}
