<?php

/**
 * Create Update Abstract
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Prohibition\Defect;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Prohibition\ProhibitionDefect as Entity;
use Dvsa\Olcs\Api\Domain\Repository\ProhibitionDefect as Repository;
use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Defect\Create as CreateCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Create Update Abstract
 */
abstract class CreateUpdateAbstract extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ProhibitionDefect';

    protected function setData($entity, CommandInterface $command)
    {
        /* @var $entity Entity For traceability */
        /* @var $command CreateCommand For traceability */

        /** @var Repository $repo For traceability */
        $repo = $this->getRepo();

        // Let's do the foreign keys first

        // Prohibition - required if create
        if ($command->getProhibition() !== null) {
            $case = $this->getRepo()->getReference(Entities\Prohibition\Prohibition::class, $command->getProhibition());
            $entity->setProhibition($case);
        }

        // Defect Type - required - no if needed.
        $entity->setDefectType($command->getDefectType());

        $entity->setNotes($command->getNotes());
    }
}
