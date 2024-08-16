<?php

/**
 * Requires Confirmation Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * Requires Confirmation Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RequiresConfirmationException extends Exception
{
    public function __construct($message, $code)
    {
        $this->message = $message;
        $this->messages[$code] = $message;
    }
}
