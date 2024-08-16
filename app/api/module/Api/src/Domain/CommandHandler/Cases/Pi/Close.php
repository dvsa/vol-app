<?php

/**
 * Closes a Pi
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Pi;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\Close as CloseCmd;
use Doctrine\ORM\Query;

/**
 * Closes a Pi
 */
final class Close extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Pi';

    /**
     * Closes a Pi
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /**
         * @var PiEntity $pi
         * @var CloseCmd $command
         */
        $pi = $this->getRepo()->fetchUsingCase($command, Query::HYDRATE_OBJECT);
        $pi->close();
        $this->getRepo()->save($pi);
        $result->addMessage('Pi closed');
        $result->addId('Pi', $pi->getId());

        return $result;
    }
}
