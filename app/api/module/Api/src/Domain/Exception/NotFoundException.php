<?php

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * Not Found Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class NotFoundException extends Exception
{
    /**
     * NotFoundException constructor.
     *
     * @param null $message Exception message tezt
     *
     * @return void
     */
    public function __construct($message = null)
    {
        $this->messages = [$message];

        parent::__construct($message ?? '', $this->getCode(), $this->getPrevious());
    }
}
