<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;

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

        foreach ($letterInstance->getLetterInstanceIssues() as $issue) {
            $resolved = $this->resolve($issue->getLetterIssueVersion()?->getDefaultBodyContentAsArray(), $context);
            if ($resolved !== null) {
                $issue->setGeneratedContent($resolved);
            }
        }

        foreach ($letterInstance->getLetterInstanceTodos() as $todo) {
            $resolved = $this->resolve($todo->getLetterTodoVersion()?->getDescriptionAsArray(), $context);
            if ($resolved !== null) {
                $todo->setGeneratedDescription($resolved);
            }
        }

        foreach ($letterInstance->getLetterInstanceAppendices() as $appendix) {
            $resolved = $this->resolve($appendix->getLetterAppendixVersion()?->getDefaultContentAsArray(), $context);
            if ($resolved !== null) {
                $appendix->setGeneratedContent($resolved);
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
