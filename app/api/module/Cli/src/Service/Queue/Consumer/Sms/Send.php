<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Sms;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\CommandConsumer;

class Send extends CommandConsumer
{
    /**
     * @var int Max retry attempts before fails
     */
    protected $maxAttempts = 4;
}
