<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceIssue;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceIssue\UpdateContent as Cmd;

/**
 * Update LetterInstanceIssue edited content
 */
final class UpdateContent extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterInstanceIssue';

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue $letterInstanceIssue */
        $letterInstanceIssue = $this->getRepo()->fetchUsingId($command);

        // a sent letter's content is what the operator received; keep it as sent
        $letterInstance = $letterInstanceIssue->getLetterInstance();
        if ($letterInstance !== null && !$letterInstance->isOpenForChanges()) {
            throw new ValidationException(['Only a draft or ready letter can be changed']);
        }

        $letterInstanceIssue->setEditedContentFromArray(
            json_decode($command->getEditedContent(), true)
        );

        // only an Other issue has a caseworker-written heading; blank clears it (VOL-7735)
        $heading = $command->getEditedHeading();
        if ($heading !== null && $letterInstanceIssue->isOther()) {
            $heading = trim($heading);
            $letterInstanceIssue->setEditedHeading($heading === '' ? null : $heading);
        }

        $this->getRepo()->save($letterInstanceIssue);

        $this->result->addId('letterInstanceIssue', $letterInstanceIssue->getId());
        $this->result->addMessage('Issue content updated successfully');

        return $this->result;
    }
}
