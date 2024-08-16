<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Dvsa\Olcs\Transfer\Command\Application\CreatePsvVehicle as Cmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create Psv Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreatePsvVehicle extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['Vehicle', 'LicenceVehicle'];

    /**
     * @param Cmd $command
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchById($command->getApplication());

        $this->checkIfVrmAlreadyExistsOnLicence($application->getLicence(), $command->getVrm());

        $existedVehicle = $this->getRepo('Vehicle')->fetchByVrm($command->getVrm());
        if (count($existedVehicle)) {
            $vehicle = $existedVehicle[0];
        } else {
            $vehicle = new Vehicle();
            $vehicle->setVrm($command->getVrm());
        }
        $vehicle->setMakeModel($command->getMakeModel());
        $this->getRepo('Vehicle')->save($vehicle);

        $this->result->addMessage('Vehicle created');
        $this->result->addId('vehicle', $vehicle->getId());

        $licenceVehicle = new LicenceVehicle($application->getLicence(), $vehicle);
        $licenceVehicle->setApplication($application);

        if ($this->isGranted(Permission::INTERNAL_USER)) {
            $licenceVehicle->setReceivedDate(new DateTime($command->getReceivedDate()));
        }

        $this->getRepo('LicenceVehicle')->save($licenceVehicle);

        $this->result->addMessage('Licence Vehicle created');
        $this->result->addId('licenceVehicle', $licenceVehicle->getId());

        $data = [
            'id' => $application->getId(),
            'section' => 'vehiclesPsv'
        ];
        $this->result->merge($this->handleSideEffect(UpdateApplicationCompletion::create($data)));

        return $this->result;
    }

    /**
     * Check whether the VRM already exist on this licence
     *
     * @param LicenceEntity $licence Licence
     * @param string        $vrm     VRM
     *
     * @return void
     * @throws ValidationException
     */
    protected function checkIfVrmAlreadyExistsOnLicence(LicenceEntity $licence, $vrm)
    {
        $currentLicenceVehicles = $licence->getActiveVehicles(false);

        if ($currentLicenceVehicles->count() < 1) {
            return;
        }

        /** @var LicenceVehicle $licenceVehicle */
        foreach ($currentLicenceVehicles as $licenceVehicle) {
            if ($licenceVehicle->getVehicle()->getVrm() == $vrm) {
                throw new ValidationException(
                    [
                        'vrm' => [
                            Vehicle::ERROR_VRM_EXISTS => 'application.vehicle.already-exist',
                        ],
                    ]
                );
            }
        }
    }
}
