<?php

/**
 * Delete Unlicensed Licence Vehicle
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Delete Unlicensed Licence Vehicle
 */
final class DeleteUnlicensedOperatorLicenceVehicle extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'LicenceVehicle';

    /**
     * Delete Unlicensed Licence Vehicle
     *
     * This is different to DeleteLicenceVehicle and RemoveLicenceVehicle as we
     * actually just delete, rather than cease discs, set removal date, etc.
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $licenceVehicle = $this->getRepo('LicenceVehicle')->fetchUsingId($command);
        $licenceVehicleId = $licenceVehicle->getId();

        $this->getRepo('LicenceVehicle')->delete($licenceVehicle);

        $result = new Result();

        $result
            ->addId('licenceVehicle', $licenceVehicleId)
            ->addMessage('LicenceVehicle deleted');

        return $result;
    }
}
