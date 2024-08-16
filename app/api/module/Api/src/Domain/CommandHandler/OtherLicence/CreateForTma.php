<?php

/**
 * Create an Other Licence for a TMA
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\OtherLicence\Create as CreateCommand;

/**
 * Create an Other Licence for a TMA
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CreateForTma extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'OtherLicence';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command CreateCommand */

        $otherLicence = new OtherLicence();
        $otherLicence->setRole($this->getRepo()->getRefdataReference($command->getRole()));
        $otherLicence->setTransportManagerApplication(
            $this->getRepo()->getReference(TransportManagerApplication::class, $command->getTmaId())
        );
        $otherLicence->setHoursPerWeek($command->getHoursPerWeek());
        $otherLicence->setLicNo($command->getLicNo());
        $otherLicence->setOperatingCentres($command->getOperatingCentres());
        if ($command->getTotalAuthVehicles()) {
            $otherLicence->setTotalAuthVehicles($command->getTotalAuthVehicles());
        }

        $this->getRepo()->save($otherLicence);

        $result = new Result();
        $result->addId('otherLicence', $otherLicence->getId());
        $result->addMessage("Other Licence ID {$otherLicence->getId()} created");

        return $result;
    }
}
