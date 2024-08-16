<?php

/**
 * Create Conviction
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Conviction;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Conviction as Entity;
use Dvsa\Olcs\Api\Domain\Repository\Conviction as Repository;
use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Transfer\Command\Cases\Conviction\Create as CreateCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Create Conviction
 */
final class Create extends CreateUpdateAbstract implements TransactionedInterface
{
    /**
     * Create
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $command CreateCommand For traceability */

        $result = new Result();

        /** @var Repository $repo For traceability */
        $repo = $this->getRepo();

        /* @var Entity $entity */
        $entity = new Entity();
        $this->setData($entity, $command);
        $repo->save($entity);

        $result->addId('conviction', $entity->getId());
        $result->addMessage('Conviction Created');

        return $result;
    }
}
