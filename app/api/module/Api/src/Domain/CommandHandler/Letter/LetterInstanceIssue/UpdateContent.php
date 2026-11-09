<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceIssue;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
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
