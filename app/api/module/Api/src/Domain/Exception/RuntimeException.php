<?php

/**
 * Runtime Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * Runtime Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RuntimeException extends Exception
{
    public function __construct($message, int $code = 0, \Throwable $previous = null)
    {
        $this->messages = [$message];

        parent::__construct($message, $code, $previous);
    }
}
