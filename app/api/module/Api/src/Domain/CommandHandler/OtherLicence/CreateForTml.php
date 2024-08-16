<?php

/**
 * Create an Other Licence for a TML
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create an Other Licence for a TML
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateForTml extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'OtherLicence';

    public function handleCommand(CommandInterface $command)
    {
        $otherLicence = new OtherLicence();
        $otherLicence->updateOtherLicenceForTml(
            $this->getRepo()->getRefdataReference($command->getRole()),
            $this->getRepo()->getReference(TransportManagerLicence::class, $command->getTmlId()),
            $command->getHoursPerWeek(),
            $command->getLicNo(),
            $command->getOperatingCentres(),
            $command->getTotalAuthVehicles()
        );

        $this->getRepo()->save($otherLicence);

        $result = new Result();
        $result->addId('otherLicence', $otherLicence->getId());
        $result->addMessage("Other licence created");

        return $result;
    }
}
