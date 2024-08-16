<?php

/**
 * Update Prohibition
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Prohibition\Defect;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Prohibition\ProhibitionDefect as Entity;
use Dvsa\Olcs\Api\Domain\Repository\ProhibitionDefect as Repository;
use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Update as UpdateCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Doctrine\ORM\Query;

/**
 * Delete Prohibition
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

        $result->addId('defect', $entity->getId());
        $result->addMessage('Prohibition Defect Updated');

        return $result;
    }
}
