<?php

/**
 * Update Psv Licence Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\LicenceVehicle;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\LicenceVehicle\UpdatePsvLicenceVehicle as Cmd;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;

/**
 * Update Psv Licence Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdatePsvLicenceVehicle extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    public const string ERR_PSV_VE_REMOVED_1 = 'ERR_PSV_VE_REMOVED_1';

    protected $repoServiceName = 'LicenceVehicle';

    /**
     * @param Cmd $command
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        if ($licenceVehicle->getRemovalDate() !== null) {
            if ($this->isGranted(Permission::SELFSERVE_USER) || $command->getApplication() !== null) {
                throw new ForbiddenException();
            }

            if ($command->getRemovalDate() === null) {
                throw new ValidationException(
                    [
                        'removalDate' => [
                            [self::ERR_PSV_VE_REMOVED_1 => self::ERR_PSV_VE_REMOVED_1]
                        ]
                    ]
                );
            }

            $licenceVehicle->setRemovalDate(new DateTime($command->getRemovalDate()));

            $this->getRepo()->save($licenceVehicle);

            $this->result->addMessage('Removal date updated');
            return $this->result;
        }

        if ($this->isGranted(Permission::INTERNAL_USER)) {
            if ($command->getSpecifiedDate() !== null) {
                $licenceVehicle->setSpecifiedDate(
                    $licenceVehicle->processDate($command->getSpecifiedDate(), \DateTime::ISO8601, false)
                );
            }

            if ($command->getReceivedDate() !== null) {
                $licenceVehicle->setReceivedDate(new DateTime($command->getReceivedDate()));
            }
        }

        if ($command->getMakeModel() !== null) {
            $licenceVehicle->getVehicle()->setMakeModel($command->getMakeModel());
        }

        $this->getRepo()->save($licenceVehicle);

        $this->result->addMessage('Updated Vehicle');

        if ($command->getApplication() !== null) {
            $data = [
                'id' => $command->getApplication(),
                'section' => 'vehiclesPsv'
            ];

            $this->result->merge($this->handleSideEffect(UpdateApplicationCompletion::create($data)));
        }

        return $this->result;
    }
}
