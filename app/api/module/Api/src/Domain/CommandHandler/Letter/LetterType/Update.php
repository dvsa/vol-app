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

    protected $extraRepos = ['LetterType', 'MasterTemplate', 'LetterSection', 'LetterIssue', 'LetterAppendix'];

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterType $letterType */
        $letterType = $this->getRepo()->fetchUsingId($command);

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
            // Clear existing sections (orphanRemoval handles deletion)
            foreach ($letterType->getLetterTypeSections()->toArray() as $existing) {
                $letterType->removeLetterTypeSection($existing);
            }

            // Flush removals so DELETEs execute before INSERTs (composite PK)
            $this->getRepo()->flushAll();

            $displayOrder = 0;
            foreach ($command->getSections() as $sectionId) {
                $letterSection = $this->getRepo('LetterSection')->fetchById($sectionId);
                $sectionVersion = $letterSection->getCurrentVersion();
                if ($sectionVersion) {
                    $lts = new \Dvsa\Olcs\Api\Entity\Letter\LetterTypeSection();
                    $lts->setLetterSectionVersion($sectionVersion);
                    $lts->setDisplayOrder($displayOrder++);
                    $letterType->addLetterTypeSection($lts);
                }
            }
        }

        // Update issues if provided
        if ($command->getIssues() !== null) {
            $letterType->getLetterTypeIssues()->clear();
            foreach ($command->getIssues() as $issueId) {
                $issue = $this->getRepo('LetterIssue')->fetchById($issueId);
                $letterType->addLetterTypeIssue($issue);
            }
        }

        // Update appendices if provided
        if ($command->getAppendices() !== null) {
            // Clear existing appendices (orphanRemoval handles deletion)
            foreach ($letterType->getLetterTypeAppendices()->toArray() as $existing) {
                $letterType->removeLetterTypeAppendix($existing);
            }

            // Flush removals so DELETEs execute before INSERTs (composite PK)
            $this->getRepo()->flushAll();

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
        $this->result->addMessage("Letter type '{$letterType->getName()}' updated");

        return $this->result;
    }
}
