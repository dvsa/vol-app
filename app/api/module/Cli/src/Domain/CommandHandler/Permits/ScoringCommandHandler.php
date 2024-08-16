<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;

/**
 * Scoring command handler
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
abstract class ScoringCommandHandler extends AbstractCommandHandler
{
    /**
     * Write a message to standard output accompanied by a timestamp
     *
     * @param string $message
     */
    protected function profileMessage($message)
    {
        //echo('[' . date('h:i:s') . '] ' . $message . "\n");
    }
}
