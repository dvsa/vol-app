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
        
        // Set working properties - versioning will be handled by repository
        $letterIssue->setCategory($command->getCategory());
        $letterIssue->setSubCategory($command->getSubCategory());
        $letterIssue->setHeading($command->getHeading());
        $letterIssue->setDefaultBodyContent($command->getDefaultBodyContent());
        $letterIssue->setDefaultReasonsContent($command->getDefaultReasonsContent());
        $letterIssue->setDefaultCounterMeasuresContent($command->getDefaultCounterMeasuresContent());
        $letterIssue->setDisplayOrder($command->getDisplayOrder());

        $this->getRepo()->save($letterIssue);

        $this->result->addId('letterIssue', $letterIssue->getId());
        $this->result->addMessage("Letter issue '{$letterIssue->getHeading()}' created");
        
        return $this->result;
    }
}
