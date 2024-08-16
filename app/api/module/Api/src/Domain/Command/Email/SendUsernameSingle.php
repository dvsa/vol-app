<?php

/**
 * Send Username Single Email
 */

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;

/**
 * Send Username Single Email
 */
final class SendUsernameSingle extends AbstractCommand
{
    use User;
}
