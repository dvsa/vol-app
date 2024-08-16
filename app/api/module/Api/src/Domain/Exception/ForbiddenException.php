<?php

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * Forbidden Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ForbiddenException extends Exception
{
    public function __construct($message = '')
    {
        if ($message === '') {
            $message = 'No permission to access this record';
        }

        $this->messages = [$message];
        parent::__construct($message);
    }
}
