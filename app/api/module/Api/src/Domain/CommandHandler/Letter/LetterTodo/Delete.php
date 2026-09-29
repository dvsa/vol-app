<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterTodo;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\AbstractDeleteLetterContent;

/**
 * Delete LetterTodo
 */
final class Delete extends AbstractDeleteLetterContent
{
    protected $repoServiceName = 'LetterTodo';
    protected string $contentName = 'to-do';
    protected string $usedByName = 'issue';

    #[\Override]
    protected function fetchUsedBy(int $id): array
    {
        return $this->getRepo()->fetchLiveIssueKeysUsing($id);
    }

    #[\Override]
    protected function isReferenced(int $id): bool
    {
        return $this->getRepo()->isReferenced($id);
    }
}
