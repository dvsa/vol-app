<?php

/**
 * Reset BusReg
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Reset BusReg
 */
final class ResetBusReg extends AbstractCommandHandler
{
    protected $repoServiceName = 'Bus';

    public function handleCommand(CommandInterface $command)
    {
        $busReg = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);
        $busReg->resetStatus();

        $this->getRepo()->save($busReg);

        $result = new Result();
        $result->addId('bus', $busReg->getId());
        $result->addMessage('Bus Reg resetted successfully');

        return $result;
    }
}
