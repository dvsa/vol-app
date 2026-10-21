<?php

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * File Already Exists Exception
 */
class FileAlreadyExistsException extends Exception
{
    /**
     * FileAlreadyExistsException constructor.
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
