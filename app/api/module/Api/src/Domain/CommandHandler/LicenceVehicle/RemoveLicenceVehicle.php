<?php

/**
 * Remove Licence Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\LicenceVehicle\RemoveLicenceVehicle as Cmd;

/**
 * Remove Licence Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class RemoveLicenceVehicle extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'LicenceVehicle';

    /**
     * @param Cmd $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->getRepo()->removeAllForLicence($command->getLicence());

        $this->result->addMessage('Removed vehicles for licence.');

        return $this->result;
    }
}
