<?php

/**
 * Send User Created Email
 */

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;

/**
 * Send User Created Email
 */
final class SendUserCreated extends AbstractCommand
{
    use User;
}
