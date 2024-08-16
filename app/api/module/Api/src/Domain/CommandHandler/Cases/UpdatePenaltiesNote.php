<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Update Penalties Note
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class UpdatePenaltiesNote extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Cases';

    /**
     * Updates Penalties Note in case table
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $case CasesEntity */
        $result = new Result();

        $case = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        $case->setPenaltiesNote($command->getPenaltiesNote());

        $this->getRepo()->save($case);

        $result->addMessage('Penalties note updated');
        $result->addId('case', $case->getId());

        return $result;
    }
}
