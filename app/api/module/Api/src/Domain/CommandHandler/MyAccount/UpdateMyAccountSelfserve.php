<?php

/**
 * Update MyAccount Selfserve
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\MyAccount;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update MyAccount Selfserve
 */
final class UpdateMyAccountSelfserve extends AbstractCommandHandler
{
    public function handleCommand(CommandInterface $command)
    {
        return $this->proxyCommand($command, \Dvsa\Olcs\Api\Domain\Command\MyAccount\UpdateMyAccount::class);
    }
}
