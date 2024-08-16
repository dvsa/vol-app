<?php

/**
 * Send User Registered Email
 */

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;

/**
 * Send User Registered Email
 */
final class SendUserRegistered extends AbstractCommand
{
    use User;
}
