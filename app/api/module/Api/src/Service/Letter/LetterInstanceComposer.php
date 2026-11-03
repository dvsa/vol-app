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
use Dvsa\Olcs\Api\Service\Letter\IssueChange\IssueChangePlan;
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
     * thing twice (VOL-7280). Issues can also link different versions of the same to-do, so this
     * de-dupes per to-do rather than per version and always renders the to-do's current version
     * (VOL-7408). Each unique to-do attaches to the FIRST issue in display order that brought it,
     * which gives the renderer "appears under the first issue type it relates to" without the
     * renderer needing to know anything about it.
     */
    public function composeTodos(LetterInstance $letterInstance): void
    {
        $this->reconcileTodos($letterInstance);
    }

    /**
     * Change the issues on an existing letter as planned, then reconcile its to-dos (VOL-7734).
     *
     * Removed issues go first so the to-do walk only sees the issues that remain; issues are then
     * numbered in their final order, which is the order the to-dos are owned and numbered in.
     *
     * @return array{
     *     issues: list<LetterInstanceIssue>,
     *     todos: list<LetterInstanceTodo>,
     *     movedTodos: list<array{0: LetterInstanceTodo, 1: LetterInstanceTodo}>
     * } rows created, and to-dos carried over to a new row (old, new)
     */
    public function applyIssueChange(LetterInstance $letterInstance, IssueChangePlan $plan): array
    {
        foreach ($plan->removedIssues as $removedIssue) {
            $letterInstance->removeLetterInstanceIssue($removedIssue);
        }

        $createdIssues = [];
        foreach ($plan->finalIssues as $displayOrder => $finalIssue) {
            if ($finalIssue instanceof LetterIssueVersion) {
                $instanceIssue = new LetterInstanceIssue();
                $instanceIssue->setLetterIssueVersion($finalIssue);
                $letterInstance->addLetterInstanceIssue($instanceIssue);
                $createdIssues[] = $instanceIssue;
                $finalIssue = $instanceIssue;
            }
            $finalIssue->setDisplayOrder($displayOrder);
        }

        $todos = $this->reconcileTodos($letterInstance);

        return [
            'issues' => $createdIssues,
            'todos' => $todos['created'],
            'movedTodos' => $todos['moved'],
        ];
    }

    /**
     * Bring the letter's to-dos into line with its issues, keeping every row that is still needed.
     *
     * The same rules as a fresh letter (one row per to-do, owned by the first issue in display order
     * that brings it, numbered in that order), but a to-do already on the letter keeps its edits,
     * its grab snapshot and whether it is shown (VOL-7734). Rows no remaining issue brings are
     * dropped. On a letter with no to-dos yet this is plain generation.
     *
     * When a to-do's owner changes it is carried over to a NEW row rather than re-parented. Both
     * of its collections are orphanRemoval, and Doctrine deletes a row taken out of one unless it
     * lands in another already-persisted collection, which a newly added issue's is not; the
     * mapping's contract is that such rows are never re-parented at all.
     *
     * Letters generated before VOL-7408 can hold more than one row for the same to-do. Those rows
     * travel together rather than being thrown away, as any of them may carry an edit.
     *
     * @return array{
     *     created: list<LetterInstanceTodo>,
     *     moved: list<array{0: LetterInstanceTodo, 1: LetterInstanceTodo}>
     * } rows created for to-dos new to the letter, and to-dos carried over to a new owner (old, new)
     */
    public function reconcileTodos(LetterInstance $letterInstance): array
    {
        $existingByKey = [];
        foreach ($letterInstance->getLetterInstanceTodos() as $instanceTodo) {
            $key = $instanceTodo->getLetterTodoVersion()?->getDedupeKey();
            if ($key !== null) {
                $existingByKey[$key][] = $instanceTodo;
            }
        }

        $seen = [];
        $created = [];
        $moved = [];
        $displayOrder = 0;

        foreach ($this->issuesInDisplayOrder($letterInstance) as $instanceIssue) {
            foreach ($instanceIssue->getLetterIssueVersion()?->getLetterIssueTodos() ?? [] as $issueTodo) {
                $todoVersion = $issueTodo->getLetterTodoVersion();
                if ($todoVersion === null) {
                    continue;
                }

                $key = $todoVersion->getDedupeKey();
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                if (isset($existingByKey[$key])) {
                    foreach ($existingByKey[$key] as $instanceTodo) {
                        if ($instanceTodo->getLetterInstanceIssue() !== $instanceIssue) {
                            $carried = $this->carryOver($letterInstance, $instanceTodo, $instanceIssue);
                            $moved[] = [$instanceTodo, $carried];
                            $instanceTodo = $carried;
                        }
                        $instanceTodo->setDisplayOrder($displayOrder++);
                    }
                    continue;
                }

                // always show the latest wording, not whichever version this issue happened to link
                $renderVersion = $todoVersion->getLetterTodo()?->getCurrentVersion() ?? $todoVersion;

                $instanceTodo = new LetterInstanceTodo();
                $instanceTodo->setLetterTodoVersion($renderVersion);
                $instanceTodo->setDisplayOrder($displayOrder++);
                $instanceIssue->addLetterInstanceTodo($instanceTodo);
                $letterInstance->addLetterInstanceTodo($instanceTodo);

                $created[] = $instanceTodo;
            }
        }

        foreach ($existingByKey as $key => $instanceTodos) {
            if (isset($seen[$key])) {
                continue;
            }
            foreach ($instanceTodos as $instanceTodo) {
                $instanceTodo->getLetterInstanceIssue()?->removeLetterInstanceTodo($instanceTodo);
                $letterInstance->removeLetterInstanceTodo($instanceTodo);
            }
        }

        return ['created' => $created, 'moved' => $moved];
    }

    /**
     * The letter's issues sorted by display order. The collection keeps the order rows were loaded
     * or added in, which stops being display order as soon as issues are added or renumbered.
     *
     * @return LetterInstanceIssue[]
     */
    private function issuesInDisplayOrder(LetterInstance $letterInstance): array
    {
        $issues = $letterInstance->getLetterInstanceIssues()->toArray();
        usort(
            $issues,
            static fn(LetterInstanceIssue $a, LetterInstanceIssue $b): int => $a->getDisplayOrder() <=> $b->getDisplayOrder()
        );

        return $issues;
    }

    /**
     * Replace a to-do with an identical one owned by a different issue; see reconcileTodos for why
     * it isn't simply re-parented. The old row leaves both collections and is deleted as an orphan.
     */
    private function carryOver(
        LetterInstance $letterInstance,
        LetterInstanceTodo $instanceTodo,
        LetterInstanceIssue $owner
    ): LetterInstanceTodo {
        $carried = new LetterInstanceTodo();
        $carried->setLetterTodoVersion($instanceTodo->getLetterTodoVersion());
        $carried->setEditedDescription($instanceTodo->getEditedDescription());
        $carried->setGeneratedDescription($instanceTodo->getGeneratedDescription());
        $carried->setIsRendered($instanceTodo->getIsRendered());

        $instanceTodo->getLetterInstanceIssue()?->removeLetterInstanceTodo($instanceTodo);
        $letterInstance->removeLetterInstanceTodo($instanceTodo);

        $owner->addLetterInstanceTodo($carried);
        $letterInstance->addLetterInstanceTodo($carried);

        return $carried;
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
