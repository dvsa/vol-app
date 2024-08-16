<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;

/**
 * Abstract Consumer Services
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AbstractConsumerServices
{
    /**
     * Create service instance
     *
     *
     * @return AbstractConsumerServices
     */
    public function __construct(private readonly CommandHandlerManager $commandHandlerManager)
    {
    }

    /**
     * Return the command handler manager service
     *
     * @return CommandHandlerManager
     */
    public function getCommandHandlerManager(): CommandHandlerManager
    {
        return $this->commandHandlerManager;
    }
}
