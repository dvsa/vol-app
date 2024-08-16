<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\CreateGoodsVehicle as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\RequiresConfirmationException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create Goods Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateGoodsVehicle extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Licence';
    protected $extraRepos = ['Application', 'Vehicle', 'LicenceVehicle'];

    /** @var Repository\Licence */
    private $repo;
    /** @var Repository\Vehicle  */
    private $vehicleRepo;
    /** @var Repository\LicenceVehicle */
    private $licVehicleRepo;

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Api\Domain\Command\Vehicle\CreateGoodsVehicle $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->repo = $this->getRepo('Licence');
        $this->vehicleRepo = $this->getRepo('Vehicle');
        $this->licVehicleRepo = $this->getRepo('LicenceVehicle');

        $applicationId = $command->getApplicationId();
        $vrm = $command->getVrm();

        /** @var LicenceEntity $licence */
        $licence = $this->repo->fetchById($command->getLicence());

        $this->checkIfVrmIsSection26($vrm);

        //  check if vehicle already assigned to licence or application
        if ($applicationId !== null) {
            $this->checkIfVrmAlreadyExistsOnApplication($licence, $vrm, $applicationId);
        } else {
            $this->checkIfVrmAlreadyExistsOnLicence($licence, $vrm);
        }

        //  process duplicates
        $duplicates = [];

        if ($command->getIdentifyDuplicates() === true) {
            $duplicates = $this->licVehicleRepo->fetchDuplicates($licence, $vrm, false);
        }

        // If we haven't confirmed, then we need to check if we need to confirm
        if ($command->getConfirm() !== true) {
            if ($command->getIdentifyDuplicates() === true) {
                $this->identifyDuplicates($duplicates);
            } else {
                $this->checkForConfirmation($licence, $command);
            }
        }

        if ($command->getIdentifyDuplicates() === true) {
            /** @var LicenceVehicle $duplicate */
            foreach ($duplicates as $duplicate) {
                $duplicate->updateDuplicateMark();
                $this->licVehicleRepo->save($duplicate);
            }
        }

        //  create/update vehicle
        $existedVehicle = $this->vehicleRepo->fetchByVrm($vrm);
        if (count($existedVehicle)) {
            $vehicle = $existedVehicle[0];
        } else {
            $vehicle = new Entity\Vehicle\Vehicle();
            $vehicle->setVrm($vrm);
        }
        $vehicle->setPlatedWeight($command->getPlatedWeight());
        $this->vehicleRepo->save($vehicle);

        $this->result
            ->addId('vehicle', $vehicle->getId())
            ->addMessage('Vehicle created');

        //  create Licence Vehicle
        $licenceVehicle = new LicenceVehicle($licence, $vehicle);
        if ($applicationId !== null) {
            $licenceVehicle->setApplication(
                $this->repo->getReference(Entity\Application\Application::class, $applicationId)
            );
        }

        if ($command->getSpecifiedDate() !== null) {
            $licenceVehicle->setSpecifiedDate(
                $licenceVehicle->processDate($command->getSpecifiedDate(), \DateTime::ISO8601, false)
            );
        }
        if ($command->getReceivedDate() !== null) {
            $licenceVehicle->setReceivedDate(new \DateTime($command->getReceivedDate()));
        }

        $this->licVehicleRepo->save($licenceVehicle);

        $this->result
            ->addId('licenceVehicle', $licenceVehicle->getId())
            ->addMessage('Licence Vehicle created')
            ->setFlag('vrm', $vrm);

        return $this->result;
    }

    /**
     * Check if the vrm exists on any other licences
     *
     * @param LicenceEntity $licence Licence
     * @param Cmd           $command Command
     *
     * @return void
     * @throws RequiresConfirmationException
     */
    private function checkForConfirmation(LicenceEntity $licence, Cmd $command)
    {
        $licences = $this->repo->fetchByVrm($command->getVrm(), true);

        if (empty($licences)) {
            return;
        }

        $otherLicences = [];

        foreach ($licences as $otherLicence) {
            if ($otherLicence !== $licence) {
                $otherLicences[] = $otherLicence;
            }
        }

        if (empty($otherLicences)) {
            return;
        }

        if ($this->isGranted(Permission::INTERNAL_USER)) {
            $refs = [];

            /** @var LicenceEntity $otherLicence */
            foreach ($otherLicences as $otherLicence) {
                $refs[] = $this->getLicNo($otherLicence);
            }

            $message = json_encode($refs);
        } else {
            $message = 'Vehicle exists on other licence';
        }

        throw new RequiresConfirmationException($message, Vehicle::ERROR_VRM_OTHER_LICENCE);
    }

    /**
     * Get list of licence/applications numbers to which vehicle already assigned
     *
     * @param array $duplicates List of Licence Vehicle Entities
     *
     * @return void
     * @throws RequiresConfirmationException
     */
    private function identifyDuplicates($duplicates)
    {
        if (empty($duplicates)) {
            return;
        }

        if ($this->isGranted(Permission::INTERNAL_USER)) {
            $refs = [];

            /** @var LicenceVehicle $duplicate */
            foreach ($duplicates as $duplicate) {
                $refs[] = $this->getLicNo($duplicate->getLicence());
            }

            $message = json_encode($refs);
        } else {
            $message = 'Vehicle exists on other licence';
        }

        throw new RequiresConfirmationException($message, Vehicle::ERROR_VRM_OTHER_LICENCE);
    }

    /**
     * Define Licence/Application number
     *
     * @param LicenceEntity $licence Licence Entity
     *
     * @return string
     */
    private function getLicNo(LicenceEntity $licence)
    {
        $licNo = $licence->getLicNo();

        if (empty($licNo)) {
            $applications = $licence->getApplications();
            if ($applications->count() > 0) {
                $licNo = 'APP-' . $applications->first()->getId();
            }
        }

        return $licNo;
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
    private function checkIfVrmAlreadyExistsOnLicence(LicenceEntity $licence, $vrm)
    {
        $currentLicenceVehicles = $licence->getActiveVehicles(true);

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

    /**
     * Check whether the VRM already exist on this application
     *
     * @param LicenceEntity $licence       Licence
     * @param string        $vrm           VRM
     * @param int           $applicationId application id
     *
     * @return void
     * @throws ValidationException
     */
    private function checkIfVrmAlreadyExistsOnApplication(LicenceEntity $licence, $vrm, $applicationId)
    {
        $currentLicenceVehicles = $licence->getActiveVehicles(false);

        if ($currentLicenceVehicles->count() < 1) {
            return;
        }

        /** @var LicenceVehicle $licenceVehicle */
        foreach ($currentLicenceVehicles as $licenceVehicle) {
            $application = $licenceVehicle->getApplication();
            if (
                $licenceVehicle->getVehicle()->getVrm() === $vrm
                && (
                    ($application !== null && $application->getId() === $applicationId)
                    || $licenceVehicle->getSpecifiedDate() !== null
                )
            ) {
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

    /**
     * Check if a vehicle has a section 26
     *
     * @param string $vrm Vehicle Registration number
     *
     * @return void
     * @throws ValidationException
     */
    private function checkIfVrmIsSection26($vrm)
    {
        $vehicles = $this->vehicleRepo->fetchByVrm($vrm);
        /* @var $vehicle Vehicle */
        foreach ($vehicles as $vehicle) {
            if ($vehicle->getSection26()) {
                throw new ValidationException(
                    ['vrm' => [Vehicle::ERROR_VRM_HAS_SECTION_26]]
                );
            }
        }
    }
}
