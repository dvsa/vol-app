<?php

/**
 * Create Disc Records
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\Command\Vehicle\CreateGoodsDiscs;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePsvDiscs;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;

/**
 * Create Disc Records
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateDiscRecords extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);

        if ($application->isPsv()) {
            $difference = $application->getTotAuthVehicles() - $command->getCurrentTotAuth();

            if ($difference > 0) {
                $this->result->merge($this->createPsvDiscs($application->getLicence(), $difference));
            }
        }

        $licenceVehicles = $application->getActiveVehicles();

        if ($licenceVehicles->count() > 0) {
            if ($application->isGoods()) {
                $this->result->merge($this->createGoodsDiscs($licenceVehicles));
            }

            $this->specifyVehicles($licenceVehicles);
            $this->getRepo()->save($application);
        }

        return $this->result;
    }

    /**
     * @param LicenceVehicle[] $licenceVehicles
     */
    private function specifyVehicles($licenceVehicles)
    {
        $date = new DateTime();

        foreach ($licenceVehicles as $licenceVehicle) {
            // Some vehicles might already have a specified date if they were interims
            if ($licenceVehicle->getSpecifiedDate() === null) {
                $licenceVehicle->setSpecifiedDate($date);
            }
            $licenceVehicle->setInterimApplication(null);
        }
    }

    private function createGoodsDiscs($licenceVehicles)
    {
        $ids = [];

        foreach ($licenceVehicles as $licenceVehicle) {
            $ids[] = $licenceVehicle->getId();
        }

        return $this->handleSideEffect(CreateGoodsDiscs::create(['ids' => $ids]));
    }

    private function createPsvDiscs(Licence $licence, $count)
    {
        $data = [
            'licence' => $licence->getId(),
            'amount' => $count
        ];

        return $this->handleSideEffect(CreatePsvDiscs::create($data));
    }
}
