<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterSection;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSection\Update as Cmd;

/**
 * Update LetterSection
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterSection';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterSection $letterSection */
        $letterSection = $this->getRepo()->fetchUsingId($command);

        // Update all properties - versioning will be handled by repository
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
        } else {
            $letterSection->setGoodsOrPsv(null);
        }

        $this->getRepo()->save($letterSection);

        $this->result->addId('letterSection', $letterSection->getId());
        $this->result->addMessage("Letter section '{$letterSection->getName()}' updated");

        return $this->result;
    }
}
