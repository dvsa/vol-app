<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Command\Sms;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Send the SMS held in a notify_message row. The handler arrives with the queue consumer.
 */
final class SendSms extends AbstractIdOnlyCommand
{
}
