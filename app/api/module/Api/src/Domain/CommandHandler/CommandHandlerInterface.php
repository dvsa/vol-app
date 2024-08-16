<?php

/**
 * Command Handler Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Exception\DisabledHandlerException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Command Handler Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface CommandHandlerInterface
{
    /**
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command);

    /**
     * @return bool
     * @throws DisabledHandlerException
     */
    public function checkEnabled(): bool;
}
