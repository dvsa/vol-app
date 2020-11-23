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
     * @param int $licenceId
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(int $licenceId, string $message = null, int $code = 0, Throwable $previous = null)
    {
        $this->licenceId = $licenceId;
        $message = null === $message ? sprintf('Licence was not found with id: "%s".', $licenceId) : $message;
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
