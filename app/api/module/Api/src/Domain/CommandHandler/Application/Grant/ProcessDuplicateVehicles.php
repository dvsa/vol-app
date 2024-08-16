<?php

/**
 * Process Duplicate Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity;

/**
 * Process Duplicate Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ProcessDuplicateVehicles extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['LicenceVehicle'];

    public function handleCommand(CommandInterface $command)
    {
        /* @var $application  Entity\Application\Application */
        $application = $this->getRepo()->fetchUsingId($command);

        $count = $this->getRepo('LicenceVehicle')->markDuplicateVehiclesForApplication($application);

        $this->result->addMessage($count . ' vehicle(s) marked as duplicate');

        return $this->result;
    }
}
