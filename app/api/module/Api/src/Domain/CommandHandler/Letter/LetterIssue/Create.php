<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterIssue;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssue as LetterIssueEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterIssue\Create as Cmd;

/**
 * Create LetterIssue
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterIssue';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */
        
        $letterIssue = new LetterIssueEntity();

        // Set all properties - versioning will be handled by repository
        $letterIssue->setIssueKey($command->getIssueKey());
        $letterIssue->setCategory($command->getCategory());
        $letterIssue->setSubCategory($command->getSubCategory());
        $letterIssue->setHeading($command->getHeading());
        $letterIssue->setDefaultBodyContent($command->getDefaultBodyContent());
        $letterIssue->setHelpText($command->getHelpText());
        $letterIssue->setMinLength($command->getMinLength());
        $letterIssue->setMaxLength($command->getMaxLength());
        $letterIssue->setRequiresInput($command->getRequiresInput());
        $letterIssue->setIsNi($command->getIsNi());
        $letterIssue->setGoodsOrPsv($command->getGoodsOrPsv());
        $letterIssue->setPublishFrom($command->getPublishFrom());

        $this->getRepo()->save($letterIssue);

        $this->result->addId('letterIssue', $letterIssue->getId());
        $this->result->addMessage("Letter issue '{$letterIssue->getHeading()}' created");
        
        return $this->result;
    }
}
