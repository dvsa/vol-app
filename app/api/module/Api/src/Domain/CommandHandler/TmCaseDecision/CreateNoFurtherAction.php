<?php

/**
 * Create NoFurtherAction
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TmCaseDecision;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision as Entity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TmCaseDecision\CreateNoFurtherAction as Cmd;

/**
 * Create NoFurtherAction
 */
final class CreateNoFurtherAction extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TmCaseDecision';

    public function handleCommand(CommandInterface $command)
    {
        // create and save a record
        $entity = $this->createEntityObject($command);
        $this->getRepo()->save($entity);

        $result = new Result();
        $result->addId('tmCaseDecision', $entity->getId());
        $result->addMessage('Decision created successfully');

        return $result;
    }

    /**
     * @return Entity
     */
    private function createEntityObject(Cmd $command)
    {
        return Entity::create(
            $this->getRepo()->getReference(
                CasesEntity::class,
                $command->getCase()
            ),
            $this->getRepo()->getRefdataReference(Entity::DECISION_NO_FURTHER_ACTION),
            $command->getArrayCopy()
        );
    }
}
