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
        
        // Set working properties - versioning will be handled by repository
        $letterSection->setName($command->getName());
        $letterSection->setDefaultContent($command->getDefaultContent());
        $letterSection->setDisplayOrder($command->getDisplayOrder());

        $this->getRepo()->save($letterSection);

        $this->result->addId('letterSection', $letterSection->getId());
        $this->result->addMessage("Letter section '{$letterSection->getName()}' created");
        
        return $this->result;
    }
}