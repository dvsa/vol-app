<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterAppendix;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Letter\LetterAppendix\Update as Cmd;

/**
 * Update LetterAppendix
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterAppendix';
    
    protected $extraRepos = ['LetterAppendix', 'Document'];

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */
        
        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterAppendix $entity */
        $entity = $this->getRepo()->fetchUsingId($command);
        
        // Update working properties - versioning will be handled by repository
        $entity->setName($command->getName());
        
        if ($command->getDescription() \!== null) {
            $entity->setDescription($command->getDescription());
        }
        
        if ($command->getDocument() \!== null) {
            if ($command->getDocument()) {
                $document = $this->getRepo('Document')->fetchById($command->getDocument());
                $entity->setDocument($document);
            } else {
                $entity->setDocument(null);
            }
        }
