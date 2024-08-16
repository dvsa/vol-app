<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Disqualification;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Disqualification\Update as Command;
use Doctrine\ORM\Query;

/**
 * Update a Disqualification
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Update extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Disqualification';

    /**
     * Update a Disqualification
     *
     * @param CommandInterface $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $command Command */

        $disqualification = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        $disqualification->update(
            $command->getIsDisqualified(),
            $command->getStartDate() ? new \DateTime($command->getStartDate()) : null,
            $command->getNotes(),
            $command->getPeriod() ?: null
        );

        $this->getRepo()->save($disqualification);

        $result = new Result();
        $result->addId('disqualification', $disqualification->getId());
        $result->addMessage('Disqualification updated');

        return $result;
    }
}
