<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterChoice;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterChoice as LetterChoiceEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterChoice\Create as Cmd;

/**
 * Create LetterChoice
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterChoice';

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        $letterChoice = new LetterChoiceEntity();

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

        $letterChoice->setIsActive($command->getIsActive());

        $this->getRepo()->save($letterChoice);

        $this->result->addId('letterChoice', $letterChoice->getId());
        $this->result->addMessage("Letter choice '{$letterChoice->getLabel()}' created");

        return $this->result;
    }
}
