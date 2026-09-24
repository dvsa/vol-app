<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterIssue;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\AbstractDeleteLetterContent;

/**
 * Delete LetterIssue
 */
final class Delete extends AbstractDeleteLetterContent
{
    protected $repoServiceName = 'LetterIssue';
    protected string $contentName = 'issue';

    #[\Override]
    protected function fetchUsedBy(int $id): array
    {
        return $this->getRepo()->fetchLetterTypeNamesUsing($id);
    }

    #[\Override]
    protected function isReferenced(int $id): bool
    {
        return $this->getRepo()->isUsedByLetterInstances($id);
    }
}
