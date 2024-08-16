<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;

/**
 * Duplicate Vehicle Removal
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class RemoveDuplicateVehicle extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'LicenceVehicle';

    protected $extraRepos = ['GoodsDisc'];

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var LicenceVehicleRepo $repo */
        $repo = $this->getRepo();

        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = $repo->fetchUsingId($command);

        $licenceVehicle->setRemovalDate(new DateTime());
        $repo->save($licenceVehicle);
        $ceasedDiscsCount = $this->getRepo('GoodsDisc')->ceaseDiscsForLicenceVehicle($licenceVehicle->getId());

        if ($ceasedDiscsCount > 0) {
            $this->result->addMessage('Goods discs ceased for licence vehicle: ' . $licenceVehicle->getId());
        }

        return $this->result;
    }
}
