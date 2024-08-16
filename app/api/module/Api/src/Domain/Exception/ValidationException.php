<?php

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * Validation Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ValidationException extends Exception
{
    public function __construct(array $messages)
    {
        $this->messages = $messages;

        parent::__construct(var_export($messages, true), $this->getCode(), $this->getPrevious());
    }
}
