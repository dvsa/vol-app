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
        
        // Update working properties - versioning will be handled by repository
        $letterSection->setName($command->getName());
        
        if ($command->getDefaultContent() !== null) {
            $letterSection->setDefaultContent($command->getDefaultContent());
        }
        
        if ($command->getDisplayOrder() !== null) {
            $letterSection->setDisplayOrder($command->getDisplayOrder());
        }

        $this->getRepo()->save($letterSection);

        $this->result->addId('letterSection', $letterSection->getId());
        $this->result->addMessage("Letter section '{$letterSection->getName()}' updated");
        
        return $this->result;
    }
}