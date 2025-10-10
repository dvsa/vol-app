<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterIssue;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Letter\LetterIssue\Update as Cmd;

/**
 * Update LetterIssue
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterIssue';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */
        
        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterIssue $letterIssue */
        $letterIssue = $this->getRepo()->fetchUsingId($command);

        // Update all properties - versioning will be handled by repository
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
        $this->result->addMessage("Letter issue '{$letterIssue->getHeading()}' updated");
        
        return $this->result;
    }
}
