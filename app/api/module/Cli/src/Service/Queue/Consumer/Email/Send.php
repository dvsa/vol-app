<?php

/**
 * Send
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Email;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\CommandConsumer;

/**
 * Send
 */
class Send extends CommandConsumer
{
    /**
     * @var int Max retry attempts before fails
     */
    protected $maxAttempts = 4;
}
