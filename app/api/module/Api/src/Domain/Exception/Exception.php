<?php

/**
 * Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Exception extends \Exception
{
    protected $messages;

    public function getMessages()
    {
        return $this->messages;
    }
}
