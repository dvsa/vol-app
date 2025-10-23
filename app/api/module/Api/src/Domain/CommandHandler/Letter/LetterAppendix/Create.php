<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterAppendix;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterAppendix as LetterAppendixEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterAppendix\Create as Cmd;

/**
 * Create LetterAppendix
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterAppendix';
    
    protected $extraRepos = ['LetterAppendix', 'Document'];

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */
        
        $entity = new LetterAppendixEntity();
        
        // Set working properties - versioning will be handled by repository
        $entity->setName($command->getName());
        $entity->setDescription($command->getDescription());
        
        if ($command->getDocument()) {
            $document = $this->getRepo('Document')->fetchById($command->getDocument());
            $entity->setDocument($document);
        }
