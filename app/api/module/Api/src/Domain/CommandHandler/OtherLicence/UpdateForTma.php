<?php

/**
 * Update Other Licence for a TMA
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;

/**
 * Update Other Licence for a TMA
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class UpdateForTma extends AbstractCommandHandler
{
    protected $repoServiceName = 'OtherLicence';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command \Dvsa\Olcs\Transfer\Command\OtherLicence\UpdateForTma */

        /* @var $otherLicence \Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence */
        $otherLicence = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        $otherLicence->setLicNo($command->getLicNo());
        if ($command->getRole()) {
            $otherLicence->setRole($this->getRepo()->getRefdataReference($command->getRole()));
        }
        if ($command->getOperatingCentres()) {
            $otherLicence->setOperatingCentres($command->getOperatingCentres());
        }
        if ($command->getTotalAuthVehicles()) {
            $otherLicence->setTotalAuthVehicles($command->getTotalAuthVehicles());
        }
        if ($command->getHoursPerWeek()) {
            $otherLicence->setHoursPerWeek($command->getHoursPerWeek());
        }
        if ($command->getHolderName()) {
            $otherLicence->setHolderName($command->getHolderName());
        }

        $this->getRepo()->save($otherLicence);

        $result = new Result();
        $result->addMessage("Other Licence ID {$command->getId()} has been updated");

        return $result;
    }
}
