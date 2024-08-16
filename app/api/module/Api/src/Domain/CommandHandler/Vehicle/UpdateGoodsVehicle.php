<?php

/**
 * Update Goods Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\User\Permission;

/**
 * Update Goods Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateGoodsVehicle extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'LicenceVehicle';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        if ($command->getRemovalDate() !== null && !$this->isGranted(Permission::INTERNAL_USER)) {
            throw new ForbiddenException();
        }

        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        // Can't update removalDate of active vehicle
        if ($licenceVehicle->getRemovalDate() === null && $command->getRemovalDate() !== null) {
            throw new BadRequestException('You cannot update the removal date of an active vehicle');
        }

        // Can't update specified date or received date of removed vehicle
        if (
            $licenceVehicle->getRemovalDate() !== null
            && ($command->getSpecifiedDate() !== null || $command->getReceivedDate() !== null)
        ) {
            throw new BadRequestException('You cannot update a removed vehicle');
        }

        $licenceVehicle->getVehicle()->setPlatedWeight($command->getPlatedWeight());
        if ($command->getSpecifiedDate() !== null) {
            $licenceVehicle->setSpecifiedDate(
                $licenceVehicle->processDate($command->getSpecifiedDate(), \DateTime::ISO8601, false)
            );
        }
        if ($command->getReceivedDate() !== null) {
            $licenceVehicle->setReceivedDate(new \DateTime($command->getReceivedDate()));
        }
        if ($command->getRemovalDate() !== null) {
            $licenceVehicle->setRemovalDate(new \DateTime($command->getRemovalDate()));
        }

        if ($this->isGranted(Permission::INTERNAL_USER)) {
            $date = $command->getSeedDate();

            if ($date !== null) {
                $date = new DateTime($date);
            }

            $licenceVehicle->setWarningLetterSeedDate($date);

            $date = $command->getSentDate();

            if ($date !== null) {
                $date = new DateTime($date);
            }

            $licenceVehicle->setWarningLetterSentDate($date);
        }

        $this->getRepo()->save($licenceVehicle);

        $result->addMessage('Vehicle updated');

        return $result;
    }
}
