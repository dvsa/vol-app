<?php

/**
 * Requires Variation Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * Requires Variation Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RequiresVariationException extends Exception
{
    public function __construct($message, $code)
    {
        $this->messages[$code] = $message;
    }
}
