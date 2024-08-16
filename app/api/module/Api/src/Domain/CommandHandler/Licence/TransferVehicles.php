<?php

/**
 * Transfer Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\CreateGoodsDiscs;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\Vehicle\DeleteLicenceVehicle as DeleteLicenceVehicleCmd;

/**
 * Transfer Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class TransferVehicles extends AbstractCommandHandler implements TransactionedInterface, CacheAwareInterface
{
    use CacheAwareTrait;

    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['LicenceVehicle'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $licenceVehicleIds = $command->getLicenceVehicles();

        /** @var Licence $sourceLicence */
        $sourceLicence = $this->getRepo()->fetchUsingId($command);

        /** @var Licence $targetLicence */
        $targetLicence = $this->getRepo()->fetchById($command->getTarget());

        $this->checkIfWillExceedTotAuth($targetLicence, $licenceVehicleIds);

        // Grab the selected vehicle records
        $vehicles = $this->getVehiclesFromLicenceVehicles($sourceLicence, $licenceVehicleIds);

        // Check if any vehicles already exist on the target licence
        $this->checkForOverlappingVehicles($targetLicence, $vehicles);

        // Remove the old licence vehicles (and discs)
        $result->merge($this->handleSideEffect(DeleteLicenceVehicleCmd::create(['ids' => $licenceVehicleIds])));

        // Create new licence vehicles (and discs)
        $this->createNewLicenceVehicles($targetLicence, $vehicles, $result);

        $this->clearLicenceCaches($sourceLicence);
        $this->clearLicenceCaches($targetLicence);

        return $result;
    }

    protected function createNewLicenceVehicles(Licence $targetLicence, array $vehicles, $result)
    {
        $newLicenceVehicleIds = [];

        $count = 0;

        $now = new \DateTime();

        foreach ($vehicles as $vehicle) {
            $newLicenceVehicle = new LicenceVehicle($targetLicence, $vehicle);
            $newLicenceVehicle->setSpecifiedDate($now);
            $this->getRepo('LicenceVehicle')->save($newLicenceVehicle);
            $newLicenceVehicleIds[] = $newLicenceVehicle->getId();
            $count++;
        }

        $dtoData = [
            'ids' => $newLicenceVehicleIds
        ];

        $result->addMessage($count . ' Licence Vehicle(s) created');

        $result->merge($this->handleSideEffect(CreateGoodsDiscs::create($dtoData)));
    }

    protected function getVehiclesFromLicenceVehicles(Licence $sourceLicence, $licenceVehicleIds)
    {
        $criteria = Criteria::create();
        $criteria->andWhere($criteria->expr()->in('id', $licenceVehicleIds));
        $licenceVehicles = $sourceLicence->getLicenceVehicles()->matching($criteria);

        $vehicles = [];
        foreach ($licenceVehicles as $licenceVehicle) {
            $vehicles[] = $licenceVehicle->getVehicle();
        }

        return $vehicles;
    }

    protected function checkIfWillExceedTotAuth(Licence $targetLicence, $licenceVehicleIds)
    {
        $newVehicleCount = $targetLicence->getActiveVehiclesCount() + count($licenceVehicleIds);

        if ($newVehicleCount > $targetLicence->getTotAuthVehicles()) {
            throw new ValidationException(
                [
                    Licence::ERROR_TRANSFER_TOT_AUTH => 'Total number of vehicles will exceed the total auth'
                ]
            );
        }
    }

    protected function checkForOverlappingVehicles(Licence $targetLicence, $vehicles)
    {
        $selectedVrms = [];
        foreach ($vehicles as $vehicle) {
            $selectedVrms[] = $vehicle->getVrm();
        }

        $overlappingVrms = [];

        $activeLicenceVehicles = $targetLicence->getActiveVehicles();
        foreach ($activeLicenceVehicles as $activeLicenceVehicle) {
            $activeVehicle = $activeLicenceVehicle->getVehicle();
            if (in_array($activeVehicle->getVrm(), $selectedVrms)) {
                $overlappingVrms[] = $activeVehicle->getVrm();
            }
        }

        if (count($overlappingVrms) > 1) {
            throw new ValidationException(
                [
                    Licence::ERROR_TRANSFER_OVERLAP_MANY => json_encode($overlappingVrms)
                ]
            );
        }

        if (count($overlappingVrms) == 1) {
            throw new ValidationException(
                [
                    Licence::ERROR_TRANSFER_OVERLAP_ONE => json_encode($overlappingVrms)
                ]
            );
        }
    }
}
