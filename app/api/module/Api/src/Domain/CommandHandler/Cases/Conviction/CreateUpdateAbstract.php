<?php

/**
 * Create Update Abstract
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Conviction;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Conviction as Entity;
use Dvsa\Olcs\Api\Domain\Repository\Conviction as Repository;
use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Transfer\Command\Cases\Conviction\Create as CreateCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Create Update Abstract
 */
abstract class CreateUpdateAbstract extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Conviction';

    protected function setData($entity, CommandInterface $command)
    {
        /* @var $entity Entity For traceability */
        /* @var $command CreateCommand For traceability */

        /** @var Repository $repo For traceability */
        $repo = $this->getRepo();

        // Let's do the foreign keys first

        // Defendant Type - required - no if needed.
        $entity->setDefendantType($repo->getRefdataReference($command->getDefendantType()));

        // Conviction Category - required - no if needed.
        if ($command->getConvictionCategory() !== '') {
            $convictionCategory = $repo->getRefdataReference($command->getConvictionCategory());
        } else {
            $convictionCategory = null;
        }

        $entity->updateConvictionCategory($convictionCategory, $command->getCategoryText());

        // Transport Manager is optional if Case is present.
        if ($command->getTransportManager() !== null) {
            $tm = $this->getRepo()->getReference(Entities\Tm\TransportManager::class, $command->getTransportManager());
            $entity->setTransportManager($tm);
        }

        // Case is optional if Transport Manager is present.
        if (method_exists($command, 'getCase') && $command->getCase() !== null) {
            $case = $this->getRepo()->getReference(Entities\Cases\Cases::class, $command->getCase());
            $entity->setCase($case);
        }

        // Person...
        if ($command->getPersonFirstName() !== null) {
            $entity->setPersonFirstname($command->getPersonFirstName());
        }
        if ($command->getPersonLastName() !== null) {
            $entity->setPersonLastname($command->getPersonLastName());
        }
        if ($command->getBirthDate() !== null) {
            $entity->setBirthDate(new \DateTime($command->getBirthDate()));
        }

        // Dates - Required
        $entity->setOffenceDate(new \DateTime($command->getOffenceDate()));
        $entity->setConvictionDate(new \DateTime($command->getConvictionDate()));

        // MSI - Required
        $entity->setMsi($command->getMsi());

        if ($command->getCourt() !== null) {
            $entity->setCourt($command->getCourt());
        }
        if ($command->getPenalty() !== null) {
            $entity->setPenalty($command->getPenalty());
        }
        if ($command->getCosts() !== null) {
            $entity->setCosts($command->getCosts());
        }
        if ($command->getNotes() !== null) {
            $entity->setNotes($command->getNotes());
        }
        if ($command->getTakenIntoConsideration() !== null) {
            $entity->setTakenIntoConsideration($command->getTakenIntoConsideration());
        }

        // Is Declared & Is Dealt With - Required
        $entity->setIsDeclared($command->getIsDeclared());
        $entity->setIsDealtWith($command->getIsDealtWith());
    }
}
