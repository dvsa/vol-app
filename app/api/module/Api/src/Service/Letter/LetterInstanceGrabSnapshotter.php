<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;

/**
 * Resolves [[GRABS]] in a freshly composed letter and stores the result on each child.
 *
 * Runs once at generation so the caseworker edits "ACME LTD", not "[[OP_NAME_ONLY]]". Tokens
 * that cannot be resolved yet are left in place; the render pass has another go and strips
 * whatever is still unresolved, so an operator never sees one. Anything a caseworker types
 * later is resolved at render time as before.
 */
class LetterInstanceGrabSnapshotter
{
    public function __construct(
        private readonly VolGrabReplacementService $volGrabReplacementService,
        private readonly VolGrabContextBuilder $contextBuilder
    ) {
    }

    public function snapshot(LetterInstance $letterInstance): void
    {
        $context = $this->contextBuilder->build($letterInstance);

        foreach ($letterInstance->getLetterInstanceSections() as $section) {
            $resolved = $this->resolve($section->getLetterSectionVersion()?->getDefaultContentAsArray(), $context);
            if ($resolved !== null) {
                $section->setGeneratedContent($resolved);
            }
        }

        $this->snapshotIssues($letterInstance->getLetterInstanceIssues(), $context);
        $this->snapshotTodos($letterInstance->getLetterInstanceTodos(), $context);

        foreach ($letterInstance->getLetterInstanceAppendices() as $appendix) {
            $resolved = $this->resolve($appendix->getLetterAppendixVersion()?->getDefaultContentAsArray(), $context);
            if ($resolved !== null) {
                $appendix->setGeneratedContent($resolved);
            }
        }
    }

    /**
     * Snapshot only the given rows, for rows added to a letter after it was generated (VOL-7734).
     * Everything else on the letter keeps what was frozen when it was generated.
     *
     * @param iterable<LetterInstanceIssue> $issues
     * @param iterable<LetterInstanceTodo> $todos
     */
    public function snapshotRows(LetterInstance $letterInstance, iterable $issues, iterable $todos): void
    {
        $context = $this->contextBuilder->build($letterInstance);

        $this->snapshotIssues($issues, $context);
        $this->snapshotTodos($todos, $context);
    }

    /**
     * @param iterable<LetterInstanceIssue> $issues
     * @param array<string, mixed> $context
     */
    private function snapshotIssues(iterable $issues, array $context): void
    {
        foreach ($issues as $issue) {
            $resolved = $this->resolve($issue->getLetterIssueVersion()?->getDefaultBodyContentAsArray(), $context);
            if ($resolved !== null) {
                $issue->setGeneratedContent($resolved);
            }
        }
    }

    /**
     * @param iterable<LetterInstanceTodo> $todos
     * @param array<string, mixed> $context
     */
    private function snapshotTodos(iterable $todos, array $context): void
    {
        foreach ($todos as $todo) {
            $resolved = $this->resolve($todo->getLetterTodoVersion()?->getDescriptionAsArray(), $context);
            if ($resolved !== null) {
                $todo->setGeneratedDescription($resolved);
            }
        }
    }

    /**
     * @param array<string, mixed>|null $content EditorJS content
     * @param array<string, mixed> $context
     * @return array<string, mixed>|null null when there is nothing to snapshot, or nothing changed
     */
    private function resolve(?array $content, array $context): ?array
    {
        if (empty($content)) {
            return null;
        }

        $json = json_encode($content);
        if ($json === false) {
            return null;
        }

        $decoded = json_decode($this->volGrabReplacementService->replaceGrabs($json, $context, false), true);

        // content with no grabs in it would just duplicate the version default
        if (!is_array($decoded) || $decoded === $content) {
            return null;
        }

        return $decoded;
    }
}
