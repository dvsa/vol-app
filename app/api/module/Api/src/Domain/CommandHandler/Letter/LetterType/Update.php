<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterType;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\Update as Cmd;

/**
 * Update LetterType
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterType';
    
    protected $extraRepos = ['LetterType', 'MasterTemplate', 'LetterSection', 'LetterIssue', 'LetterTodo', 'LetterAppendix'];

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */
        
        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterType $letterType */
        $letterType = $this->getRepo()->fetchUsingId($command);
        
        $letterType->setCode($command->getCode());
        $letterType->setName($command->getName());
        $letterType->setDescription($command->getDescription());
        
        if ($command->getIsActive() !== null) {
            $letterType->setIsActive($command->getIsActive());
        }
        
        // Update master template if provided
        if ($command->getMasterTemplate() !== null) {
            if ($command->getMasterTemplate()) {
                $masterTemplate = $this->getRepo('MasterTemplate')->fetchById($command->getMasterTemplate());
                $letterType->setMasterTemplate($masterTemplate);
            } else {
                $letterType->setMasterTemplate(null);
            }
        }
        
        // Update sections if provided
        if ($command->getSections() !== null) {
            $letterType->getSections()->clear();
            foreach ($command->getSections() as $sectionId) {
                $section = $this->getRepo('LetterSection')->fetchById($sectionId);
                $letterType->addSection($section);
            }
        }
        
        // Update issues if provided
        if ($command->getIssues() !== null) {
            $letterType->getIssues()->clear();
            foreach ($command->getIssues() as $issueId) {
                $issue = $this->getRepo('LetterIssue')->fetchById($issueId);
                $letterType->addIssue($issue);
            }
        }
        
        // Update todos if provided
        if ($command->getTodos() !== null) {
            $letterType->getTodos()->clear();
            foreach ($command->getTodos() as $todoId) {
                $todo = $this->getRepo('LetterTodo')->fetchById($todoId);
                $letterType->addTodo($todo);
            }
        }
        
        // Update appendices if provided
        if ($command->getAppendices() !== null) {
            // Clear existing appendices (orphanRemoval handles deletion)
            foreach ($letterType->getLetterTypeAppendices()->toArray() as $existing) {
                $letterType->removeLetterTypeAppendix($existing);
            }

            $displayOrder = 0;
            foreach ($command->getAppendices() as $appendixId) {
                $letterAppendix = $this->getRepo('LetterAppendix')->fetchById($appendixId);
                $appendixVersion = $letterAppendix->getCurrentVersion();
                if ($appendixVersion) {
                    $lta = new \Dvsa\Olcs\Api\Entity\Letter\LetterTypeAppendix();
                    $lta->setLetterAppendixVersion($appendixVersion);
                    $lta->setDisplayOrder($displayOrder++);
                    $letterType->addLetterTypeAppendix($lta);
                }
            }
        }

        $this->getRepo()->save($letterType);

        $this->result->addId('letterType', $letterType->getId());
        $this->result->addMessage("Letter type '{$letterType->getCode()}' updated");
        
        return $this->result;
    }
}