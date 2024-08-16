<?php

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * Exception thrown when we want to send an email but have no email address
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class MissingEmailException extends \Exception
{
    public const MSG_NO_ORG_EMAIL = 'No email address available for the organisation';
}
