<?php

/**
 * UpdateStatus
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Doctrine\ORM\Query;

/**
 * UpdateStatus
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class UpdateStatus extends AbstractCommandHandler implements
    \Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface
{
    protected $repoServiceName = 'TransportManagerApplication';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command \Dvsa\Olcs\Transfer\Command\TransportManagerApplication\UpdateStatus */
        $result = new Result();

        /* @var $tma TransportManagerApplication */
        if ($command->getVersion()) {
            $tma = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        } else {
            $tma = $this->getRepo()->fetchUsingId($command);
        }
        $status = $this->getRepo()->getRefdataReference($command->getStatus());

        $tma->setTmApplicationStatus($status);
        if ($command->getStatus() === TransportManagerApplication::STATUS_INCOMPLETE) {
            $tma->setOpDigitalSignature(null);
            $tma->setOpSignatureType(null);
            $tma->setTmDigitalSignature(null);
            $tma->setTmSignatureType(null);
        }
        $this->getRepo()->save($tma);

        $result->addMessage("Transport Manager ID {$tma->getId()} updated");

        return $result;
    }
}
