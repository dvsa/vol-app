<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterType;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterType as LetterTypeEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\Create as Cmd;

/**
 * Create LetterType
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterType';
    
    protected $extraRepos = ['LetterType', 'MasterTemplate', 'LetterSection', 'LetterIssue', 'LetterTodo', 'LetterAppendix'];

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */
        
        $letterType = new LetterTypeEntity();
        $letterType->setCode($command->getCode());
        $letterType->setName($command->getName());
        $letterType->setDescription($command->getDescription());
        $letterType->setIsActive($command->getIsActive());
        
        // Set master template if provided
        if ($command->getMasterTemplate()) {
            $masterTemplate = $this->getRepo('MasterTemplate')->fetchById($command->getMasterTemplate());
            $letterType->setMasterTemplate($masterTemplate);
        }
        
        // Add sections
        if ($command->getSections()) {
            foreach ($command->getSections() as $sectionId) {
                $section = $this->getRepo('LetterSection')->fetchById($sectionId);
                $letterType->addSection($section);
            }
        }
        
        // Add issues
        if ($command->getIssues()) {
            foreach ($command->getIssues() as $issueId) {
                $issue = $this->getRepo('LetterIssue')->fetchById($issueId);
                $letterType->addIssue($issue);
            }
        }
        
        // Add todos
        if ($command->getTodos()) {
            foreach ($command->getTodos() as $todoId) {
                $todo = $this->getRepo('LetterTodo')->fetchById($todoId);
                $letterType->addTodo($todo);
            }
        }
        
        // Add appendices
        if ($command->getAppendices()) {
            foreach ($command->getAppendices() as $appendixId) {
                $appendix = $this->getRepo('LetterAppendix')->fetchById($appendixId);
                $letterType->addAppendix($appendix);
            }
        }

        $this->getRepo()->save($letterType);

        $this->result->addId('letterType', $letterType->getId());
        $this->result->addMessage("Letter type '{$letterType->getCode()}' created");
        
        return $this->result;
    }
}