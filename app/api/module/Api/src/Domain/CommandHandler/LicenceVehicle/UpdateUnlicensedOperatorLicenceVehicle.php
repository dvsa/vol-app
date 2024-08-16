<?php

/**
 * Update Unlicensed Operator Licence Vehicle
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\LicenceVehicle;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\LicenceVehicle\UpdateUnlicensedOperatorLicenceVehicle as Cmd;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;

/**
 * Update Unlicensed Operator Licence Vehicle
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class UpdateUnlicensedOperatorLicenceVehicle extends AbstractCommandHandler
{
    use AuthAwareTrait;

    protected $repoServiceName = 'LicenceVehicle';

    /**
     * @param Cmd $command
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $licenceVehicle->getVehicle()->setPlatedWeight($command->getPlatedWeight() ?: null);

        $licenceVehicle->getVehicle()->setVrm($command->getVrm());

        $this->getRepo()->save($licenceVehicle);

        $this->result
            ->addId('licenceVehicle', $licenceVehicle->getId())
            ->addMessage('LicenceVehicle updated');

        return $this->result;
    }
}
