<?php

/**
 * Create a Previous Licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create a Previous Licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CreatePreviousLicence extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'OtherLicence';

    protected $extraRepos = ['TransportManagerApplication'];

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command \Dvsa\Olcs\Transfer\Command\OtherLicence\CreatePreviousLicence */

        /* @var $tma \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication */
        $tma = $this->getRepo('TransportManagerApplication')->fetchById($command->getTmaId());

        $otherLicence = new OtherLicence();
        $otherLicence->setTransportManager($tma->getTransportManager());
        $otherLicence->setHolderName($command->getHolderName());
        $otherLicence->setLicNo($command->getLicNo());

        $this->getRepo()->save($otherLicence);

        $result = new Result();
        $result->addId('otherLicence', $otherLicence->getId());
        $result->addMessage("Other Licence ID {$otherLicence->getId()} created");

        return $result;
    }
}
