<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceAppendix;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\Olcs\Api\Service\Letter\Resolution\SectionResolution;

/**
 * Assembles the children of a letter instance.
 *
 * Shared by real letter generation and the letter type builder's preview so the two cannot
 * disagree about what a letter contains. A preview that composed its own graph would drift, and a
 * preview that drifts is worse than none.
 *
 * Pure: it mutates only the instance it is handed, never persists, and takes already-fetched
 * entities so repository access stays with the caller.
 */
class LetterInstanceComposer
{
    /**
     * Attach the resolved sections, preserving composition order.
     */
    public function composeSections(LetterInstance $letterInstance, SectionResolution $resolution): void
    {
        foreach ($resolution->resolved as $resolvedSection) {
            $instanceSection = new LetterInstanceSection();
            $instanceSection->setLetterInstance($letterInstance);
            $instanceSection->setLetterSectionVersion($resolvedSection->version);
            $instanceSection->setDisplayOrder($resolvedSection->displayOrder);

            $letterInstance->addLetterInstanceSection($instanceSection);
        }
    }

    /**
     * Attach the chosen issues in the order given.
     *
     * @param LetterIssueVersion[] $issueVersions
     */
    public function composeIssues(LetterInstance $letterInstance, array $issueVersions): void
    {
        $displayOrder = 0;

        foreach ($issueVersions as $issueVersion) {
            $instanceIssue = new LetterInstanceIssue();
            $instanceIssue->setLetterInstance($letterInstance);
            $instanceIssue->setLetterIssueVersion($issueVersion);
            $instanceIssue->setDisplayOrder($displayOrder++);

            $letterInstance->addLetterInstanceIssue($instanceIssue);
        }
    }

    /**
     * Materialise to-dos from the issues already attached, deduplicated across the whole letter.
     *
     * Two issues frequently link the same to-do; without this the operator is told to do the same
     * thing twice (VOL-7280). Each unique to-do attaches to the FIRST issue in display order that
     * brought it, which gives the renderer "appears under the first issue type it relates to"
     * without the renderer needing to know anything about it.
     */
    public function composeTodos(LetterInstance $letterInstance): void
    {
        $seenTodoVersionIds = [];

        foreach ($letterInstance->getLetterInstanceIssues() as $instanceIssue) {
            $issueVersion = $instanceIssue->getLetterIssueVersion();
            if ($issueVersion === null) {
                continue;
            }

            foreach ($issueVersion->getLetterIssueTodos() ?? [] as $issueTodo) {
                $todoVersion = $issueTodo->getLetterTodoVersion();
                if ($todoVersion === null) {
                    continue;
                }

                $key = $todoVersion->getId();
                if (isset($seenTodoVersionIds[$key])) {
                    continue;
                }
                $seenTodoVersionIds[$key] = true;

                $instanceTodo = new LetterInstanceTodo();
                $instanceTodo->setLetterInstance($letterInstance);
                $instanceTodo->setLetterInstanceIssue($instanceIssue);
                $instanceTodo->setLetterTodoVersion($todoVersion);

                $letterInstance->addLetterInstanceTodo($instanceTodo);
            }
        }
    }

    /**
     * Attach the chosen appendices in the order given.
     *
     * @param LetterAppendixVersion[] $appendixVersions
     */
    public function composeAppendices(LetterInstance $letterInstance, array $appendixVersions): void
    {
        $displayOrder = 0;

        foreach ($appendixVersions as $appendixVersion) {
            $instanceAppendix = new LetterInstanceAppendix();
            $instanceAppendix->setLetterInstance($letterInstance);
            $instanceAppendix->setLetterAppendixVersion($appendixVersion);
            $instanceAppendix->setDisplayOrder($displayOrder++);

            $letterInstance->addLetterInstanceAppendix($instanceAppendix);
        }
    }
}
