<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\IssueChange;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;

/**
 * Works out what changing an existing letter's issues would do, without changing anything
 * (VOL-7734), so the caller can ask before throwing a caseworker's edits away.
 *
 * An issue already on the letter is matched on its parent LetterIssue and keeps the version it was
 * generated with: the caseworker's edits were written against that wording. A newly chosen issue
 * comes in on its current version.
 */
class IssueChangePlanner
{
    /**
     * @param list<LetterIssue> $selectedIssues the issues wanted, in final order, de-duplicated
     *                                          and filtered by the caller
     */
    public function plan(LetterInstance $letterInstance, array $selectedIssues): IssueChangePlan
    {
        $onLetter = [];
        foreach ($letterInstance->getIssuesInLetterOrder() as $instanceIssue) {
            $parentId = $instanceIssue->getLetterIssueVersion()?->getLetterIssue()?->getId();
            if ($parentId !== null && !isset($onLetter[$parentId])) {
                $onLetter[$parentId] = $instanceIssue;
            }
        }

        $finalIssues = [];
        $keptIssues = [];
        foreach ($selectedIssues as $letterIssue) {
            $kept = $onLetter[$letterIssue->getId()] ?? null;
            if ($kept !== null) {
                $finalIssues[] = $kept;
                $keptIssues[] = $kept;
                continue;
            }

            $currentVersion = $letterIssue->getCurrentVersion();
            if ($currentVersion instanceof LetterIssueVersion) {
                $finalIssues[] = $currentVersion;
            }
        }

        $removedIssues = [];
        foreach ($letterInstance->getIssuesInLetterOrder() as $instanceIssue) {
            if (!in_array($instanceIssue, $keptIssues, true)) {
                $removedIssues[] = $instanceIssue;
            }
        }

        // Nothing ticked or unticked: keep the letter's own order. The modal's order can differ
        // (an issue since switched off, a type changed or reordered), and a save that changes
        // nothing shouldn't renumber the letter.
        if ($removedIssues === [] && count($keptIssues) === count($finalIssues)) {
            $finalIssues = $letterInstance->getIssuesInLetterOrder();
        }

        return new IssueChangePlan(
            $finalIssues,
            $removedIssues,
            $this->lostTodos($letterInstance, $finalIssues),
        );
    }

    /**
     * @param list<LetterInstanceIssue|LetterIssueVersion> $finalIssues
     * @return list<\Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo>
     */
    private function lostTodos(LetterInstance $letterInstance, array $finalIssues): array
    {
        $brought = [];
        foreach ($finalIssues as $finalIssue) {
            $version = $finalIssue instanceof LetterInstanceIssue ? $finalIssue->getLetterIssueVersion() : $finalIssue;
            foreach ($version?->getLetterIssueTodos() ?? [] as $issueTodo) {
                $todoVersion = $issueTodo->getLetterTodoVersion();
                if ($todoVersion !== null) {
                    $brought[$todoVersion->getDedupeKey()] = true;
                }
            }
        }

        $lost = [];
        foreach ($letterInstance->getLetterInstanceTodos() as $instanceTodo) {
            $key = $instanceTodo->getLetterTodoVersion()?->getDedupeKey();
            if ($key !== null && !isset($brought[$key])) {
                $lost[] = $instanceTodo;
            }
        }

        return $lost;
    }
}
