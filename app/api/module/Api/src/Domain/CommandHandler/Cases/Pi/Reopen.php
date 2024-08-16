<?php

/**
 * Reopen a Pi
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Pi;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\Reopen as ReopenCmd;
use Doctrine\ORM\Query;

/**
 * Reopen a Pi
 */
final class Reopen extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Pi';

    /**
     * Reopen a Pi
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /**
         * @var PiEntity $pi
         * @var ReopenCmd $command
         */
        $pi = $this->getRepo()->fetchUsingCase($command, Query::HYDRATE_OBJECT);
        $pi->reopen();
        $this->getRepo()->save($pi);
        $result->addMessage('Pi reopened');
        $result->addId('Pi', $pi->getId());

        return $result;
    }
}
