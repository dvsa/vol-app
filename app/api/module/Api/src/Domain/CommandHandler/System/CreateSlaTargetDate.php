<?php

/**
 * Create SlaTargetDate
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\System;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\System\SlaTargetDate as SlaTargetDateEntity;
use Dvsa\Olcs\Transfer\Command\System\CreateSlaTargetDate as Cmd;

/**
 * Create SlaTargetDate
 */
final class CreateSlaTargetDate extends AbstractCommandHandler
{
    protected $repoServiceName = 'SlaTargetDate';

    protected $extraRepos = ['Document'];

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var SlaTargetDateEntity $slaTargetDateEntity */
        $slaTargetDateEntity = $this->createSlaTargetDate($command);

        $this->getRepo()->save($slaTargetDateEntity);

        $result = new Result();
        $result->addId('SlaTargetDate', $slaTargetDateEntity->getId());
        $result->addMessage('SlaTargetDate created successfully');

        return $result;
    }

    /**
     * @param Cmd $command
     * @return SlaTargetDateEntity
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function createSlaTargetDate(CommandInterface $command)
    {
        $entity = $this->fetchEntity($command);

        // @ to-do put entity types into ref data
        $slaTargetDateEntity = new SlaTargetDateEntity(
            $entity,
            new \DateTime($command->getAgreedDate()),
            $command->getUnderDelegation()
        );

        if (!empty($command->getTargetDate())) {
            $slaTargetDateEntity->setTargetDate($command->getTargetDate());
        }

        if (!empty($command->getSentDate())) {
            $slaTargetDateEntity->setSentDate($command->getSentDate());
        }

        $slaTargetDateEntity->setNotes($command->getNotes());

        return $slaTargetDateEntity;
    }

    /**
     * Fetches the required entity that the SLA target date relates to.
     *
     * @return mixed
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function fetchEntity(Cmd $command)
    {
        $repoName = ucfirst((string) $command->getEntityType());

        if (!in_array($repoName, $this->extraRepos)) {
            throw new ValidationException(['Cannot add SLA target date for unsupported entity type']);
        }

        return $this->getRepo($repoName)->fetchById($command->getEntityId());
    }
}
