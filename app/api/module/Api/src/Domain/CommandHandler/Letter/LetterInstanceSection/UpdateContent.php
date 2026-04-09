<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceSection;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceSection\UpdateContent as Cmd;

/**
 * Update LetterInstanceSection edited content
 *
 * Stores caseworker edits on the instance only. The canonical
 * letter_section_version is never modified.
 */
final class UpdateContent extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterInstanceSection';

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection $letterInstanceSection */
        $letterInstanceSection = $this->getRepo()->fetchUsingId($command);

        $letterInstanceSection->setEditedContentFromArray(
            json_decode($command->getEditedContent(), true)
        );

        $this->getRepo()->save($letterInstanceSection);

        $this->result->addId('letterInstanceSection', $letterInstanceSection->getId());
        $this->result->addMessage('Section content updated successfully');

        return $this->result;
    }
}
