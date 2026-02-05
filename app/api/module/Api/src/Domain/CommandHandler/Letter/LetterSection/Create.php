<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterSection;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection as LetterSectionEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSection\Create as Cmd;

/**
 * Create LetterSection
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterSection';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        $letterSection = new LetterSectionEntity();

        // Set all properties - versioning will be handled by repository
        $letterSection->setSectionKey($command->getSectionKey());
        $letterSection->setName($command->getName());
        $letterSection->setSectionType($this->getRepo()->getRefdataReference($command->getSectionType()));
        $letterSection->setDefaultContent($command->getDefaultContent());
        $letterSection->setHelpText($command->getHelpText());
        $letterSection->setMinLength($command->getMinLength());
        $letterSection->setMaxLength($command->getMaxLength());
        $letterSection->setRequiresInput($command->getRequiresInput());
        $letterSection->setIsNi($command->getIsNi());

        // Set goodsOrPsv only if provided
        if ($command->getGoodsOrPsv()) {
            $letterSection->setGoodsOrPsv($this->getRepo()->getRefdataReference($command->getGoodsOrPsv()));
        }

        $this->getRepo()->save($letterSection);

        $this->result->addId('letterSection', $letterSection->getId());
        $this->result->addMessage("Letter section '{$letterSection->getName()}' created");

        return $this->result;
    }
}
