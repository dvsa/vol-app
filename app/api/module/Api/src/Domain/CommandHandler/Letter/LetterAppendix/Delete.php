<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterAppendix;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\AbstractDeleteLetterContent;

/**
 * Delete LetterAppendix
 */
final class Delete extends AbstractDeleteLetterContent
{
    protected $repoServiceName = 'LetterAppendix';
    protected string $contentName = 'appendix';

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
