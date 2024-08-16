<?php

/**
 * Delete Goods Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\CeaseActiveDiscs as CeaseActiveDiscsCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;

/**
 * Delete Goods Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DeleteLicenceVehicle extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'LicenceVehicle';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $vehicles = 0;

        $result->merge($this->proxyCommand($command, CeaseActiveDiscsCmd::class));

        foreach ($command->getIds() as $id) {
            /** @var LicenceVehicle $licenceVehicle */
            $licenceVehicle = $this->getRepo()->fetchById($id);

            if ($licenceVehicle->getRemovalDate() === null) {
                $licenceVehicle->setRemovalDate(new \DateTime());
                $this->getRepo()->save($licenceVehicle);
                $vehicles++;
            }
        }

        $result->addMessage($vehicles . ' Vehicle(s) Deleted');

        return $result;
    }
}
