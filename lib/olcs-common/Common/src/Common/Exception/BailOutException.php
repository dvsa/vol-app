<?php

/**
 * Bail Out Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Exception;

/**
 * Bail Out Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BailOutException extends \Exception
{
    public function __construct($message, protected $response)
    {
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getResponse()
    {
        return $this->response;
    }
}
