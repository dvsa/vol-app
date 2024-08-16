<?php

/**
 * RestResponseException
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Exception;

use Laminas\Http\Response;

/**
 * RestResponseException
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class RestResponseException extends Exception
{
    public function __construct($message = null, $code = Response::STATUS_CODE_500, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->messages = [$message];
    }
}
