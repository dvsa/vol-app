<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterIssueType;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueType as LetterIssueTypeEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterIssueType\Create as Cmd;

/**
 * Create LetterIssueType
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterIssueType';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        $letterIssueType = new LetterIssueTypeEntity();
        $letterIssueType->setCode($command->getCode());
        $letterIssueType->setName($command->getName());
        $letterIssueType->setDescription($command->getDescription());
        $letterIssueType->setDisplayOrder($command->getDisplayOrder());
        $letterIssueType->setIsActive($command->getIsActive());

        $this->getRepo()->save($letterIssueType);

        $this->result->addId('letterIssueType', $letterIssueType->getId());
        $this->result->addMessage("Letter issue type '{$letterIssueType->getName()}' created");

        return $this->result;
    }
}
