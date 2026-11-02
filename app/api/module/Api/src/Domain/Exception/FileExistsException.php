<?php

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * File Exists Exception
 */
class FileExistsException extends Exception
{
    /**
     * FileExistsException constructor.
     *
     * @param null $message Exception message text
     *
     * @return void
     */
    public function __construct($message = null)
    {
        $this->messages = [$message];

        parent::__construct($message ?? '', $this->getCode(), $this->getPrevious());
    }
}
