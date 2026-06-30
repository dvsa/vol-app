<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterChoice;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Letter\LetterChoice\Update as Cmd;

/**
 * Update LetterChoice
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterChoice';

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterChoice $letterChoice */
        $letterChoice = $this->getRepo()->fetchUsingId($command);

        $letterChoice->setChoiceKey($command->getChoiceKey());
        $letterChoice->setLabel($command->getLabel());

        if ($command->getGroupLabel() !== null) {
            $letterChoice->setGroupLabel($command->getGroupLabel());
        }

        if ($command->getInputType() !== null) {
            $letterChoice->setInputType($command->getInputType());
        }

        if ($command->getDisplayOrder() !== null) {
            $letterChoice->setDisplayOrder($command->getDisplayOrder());
        }

        if ($command->getIsActive() !== null) {
            $letterChoice->setIsActive($command->getIsActive());
        }

        $this->getRepo()->save($letterChoice);

        $this->result->addId('letterChoice', $letterChoice->getId());
        $this->result->addMessage("Letter choice '{$letterChoice->getLabel()}' updated");

        return $this->result;
    }
}
