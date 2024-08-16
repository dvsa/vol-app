<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\OtherLicence\CreateForTm as CreateCommand;

/**
 * Create an Other Licence for a TM
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CreateForTm extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'OtherLicence';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command CreateCommand */

        $otherLicence = new OtherLicence();
        $otherLicence->setTransportManager(
            $this->getRepo()->getReference(TransportManager::class, $command->getTransportManagerId())
        );
        $otherLicence->setLicNo($command->getLicNo());
        $otherLicence->setHolderName($command->getHolderName());

        $this->getRepo()->save($otherLicence);

        $result = new Result();
        $result->addId('otherLicence', $otherLicence->getId());
        $result->addMessage("Other Licence ID {$otherLicence->getId()} created");

        return $result;
    }
}
