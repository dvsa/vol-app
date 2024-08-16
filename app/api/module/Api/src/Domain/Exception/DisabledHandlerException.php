<?php

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * DisabledHandlerException
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class DisabledHandlerException extends Exception
{
    public const MSG_TEMPLATE = 'Handler %s is currently disabled via feature toggle';

    public function __construct(string $class)
    {
        $message = sprintf(self::MSG_TEMPLATE, $class);
        parent::__construct($message);
        $this->messages = [$message];
    }
}
