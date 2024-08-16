<?php

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Send email to notify ECMT app has been automatically withdrawn
 */
final class SendEcmtAutomaticallyWithdrawn extends AbstractIdOnlyCommand
{
}
