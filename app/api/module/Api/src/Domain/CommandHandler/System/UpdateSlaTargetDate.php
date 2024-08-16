<?php

/**
 * Update SlaTargetDate
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\System;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\System\SlaTargetDate;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\System\SlaTargetDate as SlaTargetDateEntity;

/**
 * Update SlaTargetDate
 */
final class UpdateSlaTargetDate extends AbstractCommandHandler
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
        $slaTargetDateEntity = $this->updateSlaTargetDate($command);

        $this->getRepo()->save($slaTargetDateEntity);

        $result = new Result();
        $result->addId('SlaTargetDate', $slaTargetDateEntity->getId());
        $result->addMessage('SlaTargetDate Updated successfully');

        return $result;
    }

    /**
     * @return mixed
     * @throws NotFoundException
     * @throws ValidationException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function updateSlaTargetDate(CommandInterface $command)
    {
        $repoName = ucfirst($command->getEntityType());

        if (!in_array($repoName, $this->extraRepos)) {
            throw new ValidationException(['Cannot add SLA target date for unsupported entity type']);
        }

        $slaTargetDateEntity = $this->getRepo()->fetchUsingEntityIdAndType(
            $command->getEntityType(),
            $command->getEntityId()
        );

        if (!$slaTargetDateEntity instanceof SlaTargetDate) {
            throw new NotFoundException();
        }

        $slaTargetDateEntity->setAgreedDate($command->getAgreedDate());
        $slaTargetDateEntity->setTargetDate($command->getTargetDate());
        $slaTargetDateEntity->setSentDate($command->getSentDate());
        $slaTargetDateEntity->setUnderDelegation($command->getUnderDelegation());
        $slaTargetDateEntity->setNotes($command->getNotes());

        return $slaTargetDateEntity;
    }
}
