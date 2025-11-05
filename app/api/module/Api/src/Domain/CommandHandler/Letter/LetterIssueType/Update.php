<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterIssueType;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Letter\LetterIssueType\Update as Cmd;

/**
 * Update LetterIssueType
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterIssueType';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterIssueType $letterIssueType */
        $letterIssueType = $this->getRepo()->fetchUsingId($command);

        $letterIssueType->setCode($command->getCode());
        $letterIssueType->setName($command->getName());
        $letterIssueType->setDescription($command->getDescription());
        $letterIssueType->setDisplayOrder($command->getDisplayOrder());
        $letterIssueType->setIsActive($command->getIsActive());

        $this->getRepo()->save($letterIssueType);

        $this->result->addId('letterIssueType', $letterIssueType->getId());
        $this->result->addMessage("Letter issue type '{$letterIssueType->getName()}' updated");

        return $this->result;
    }
}
