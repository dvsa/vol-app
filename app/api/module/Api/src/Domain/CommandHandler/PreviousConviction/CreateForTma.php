<?php

/**
 * Create a Previous Conviction for a Transport Manager Application
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\PreviousConviction;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Application\PreviousConviction;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\PreviousConviction\CreateForTma as CreateForTmaCommand;

/**
 * Create a Previous Conviction for a Transport Manager Application
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CreateForTma extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'PreviousConviction';

    protected $extraRepos = ['TransportManagerApplication'];

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command CreateForTmaCommand */

        $previousConviction = new PreviousConviction();
        $previousConviction->setConvictionDate(new \DateTime($command->getConvictionDate()));
        $previousConviction->setCategoryText($command->getCategoryText());
        $previousConviction->setNotes($command->getNotes());
        $previousConviction->setCourtFpn($command->getCourtFpn());
        $previousConviction->setPenalty($command->getPenalty());
        /* @var $tma TransportManagerApplication */
        $tma = $this->getRepo('TransportManagerApplication')->fetchById($command->getTmaId());
        $previousConviction->setTransportManager($tma->getTransportManager());

        $this->getRepo()->save($previousConviction);

        $result = new Result();
        $result->addId(lcfirst((string) $this->repoServiceName), $previousConviction->getId());
        $result->addMessage("Previous Conviction ID {$previousConviction->getId()} created");

        return $result;
    }
}
