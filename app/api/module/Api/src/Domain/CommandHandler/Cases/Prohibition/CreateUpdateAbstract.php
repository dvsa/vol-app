<?php

/**
 * Create Update Abstract
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Prohibition;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Prohibition\Prohibition as Entity;
use Dvsa\Olcs\Api\Domain\Repository\Prohibition as Repository;
use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Create as CreateCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Create Update Abstract
 */
abstract class CreateUpdateAbstract extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Prohibition';

    protected function setData($entity, CommandInterface $command)
    {
        /* @var $entity Entity For traceability */
        /* @var $command CreateCommand For traceability */

        /** @var Repository $repo For traceability */
        $repo = $this->getRepo();

        // Let's do the foreign keys first

        // Prohibition Type - required - no if needed.
        $entity->setProhibitionType($repo->getRefdataReference($command->getProhibitionType()));

        // Case - required for create, not required for update
        if ($command->getCase() !== null) {
            $case = $this->getRepo()->getReference(Entities\Cases\Cases::class, $command->getCase());
            $entity->setCase($case);
        }

        // Prohibition Date...
        if ($command->getProhibitionDate() !== null) {
            $entity->setProhibitionDate(new \DateTime($command->getProhibitionDate()));
        }

        // Cleared Date...
        if ($command->getClearedDate() !== null) {
            $entity->setClearedDate(new \DateTime($command->getClearedDate()));
        }

        // Y or N
        if ($command->getIsTrailer() !== null) {
            $entity->setIsTrailer($command->getIsTrailer());
        }

        // String
        if ($command->getImposedAt() !== null) {
            $entity->setImposedAt($command->getImposedAt());
        }

        // String
        if ($command->getVrm() !== null) {
            $entity->setVrm($command->getVrm());
        }
    }
}
