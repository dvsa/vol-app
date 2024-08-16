<?php

/**
 * Update NonPi
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\NonPi;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Hearing as Entity;
use Dvsa\Olcs\Api\Domain\Repository\NonPi as Repository;
use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Transfer\Command\Cases\NonPi\Update as UpdateCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Doctrine\ORM\Query;

/**
 * Delete NonPi
 */
final class Update extends CreateUpdateAbstract implements TransactionedInterface
{
    /**
     * Update
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $command UpdateCommand For traceability */

        $result = new Result();

        /** @var Repository $repo For traceability */
        $repo = $this->getRepo();

        /* @var Entity $entity */
        $entity = $repo->fetchById($command->getId(), Query::HYDRATE_OBJECT, $command->getVersion());
        $this->setData($entity, $command);
        $repo->save($entity);

        $result->addId('non-pi', $entity->getId());
        $result->addMessage('Updated');

        return $result;
    }
}
