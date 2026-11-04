<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\IssueChange;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;

/**
 * What changing a letter's issues would do (VOL-7734). Built by IssueChangePlanner, applied by
 * LetterInstanceComposer::applyIssueChange.
 */
final readonly class IssueChangePlan
{
    /**
     * @param list<LetterInstanceIssue|LetterIssueVersion> $finalIssues kept rows, or versions to add, in final order
     * @param list<LetterInstanceIssue> $removedIssues
     * @param list<LetterInstanceTodo> $lostTodos existing rows that no final issue brings
     */
    public function __construct(
        public array $finalIssues,
        public array $removedIssues,
        public array $lostTodos,
    ) {
    }

    /**
     * Names of the caseworker's edits that applying this plan would throw away: the headings of
     * edited issues being removed, then the names of edited or hidden to-dos that go with them.
     *
     * @return list<string>
     */
    public function lostEdits(): array
    {
        $names = [];

        foreach ($this->removedIssues as $instanceIssue) {
            if ($instanceIssue->hasBeenEdited()) {
                $names[] = (string) $instanceIssue->getHeading();
            }
        }

        foreach ($this->lostTodos as $instanceTodo) {
            $hidden = !(bool) ($instanceTodo->getIsRendered() ?? true);
            if ($instanceTodo->hasBeenEdited() || $hidden) {
                $todoVersion = $instanceTodo->getLetterTodoVersion();
                $name = trim((string) $todoVersion?->getName());
                $names[] = $name !== '' ? $name : (string) $todoVersion?->getLetterTodo()?->getTodoKey();
            }
        }

        return $names;
    }

    public function hasChanges(): bool
    {
        if ($this->removedIssues !== []) {
            return true;
        }

        foreach ($this->finalIssues as $finalIssue) {
            if ($finalIssue instanceof LetterIssueVersion) {
                return true;
            }
        }

        return false;
    }
}
