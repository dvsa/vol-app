<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterSection;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\AbstractDeleteLetterContent;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection as LetterSectionEntity;

/**
 * Delete LetterSection
 */
final class Delete extends AbstractDeleteLetterContent
{
    protected $repoServiceName = 'LetterSection';
    protected string $contentName = 'section';

    /**
     * The __ISSUES__ placeholder section is reserved by the letter assembler and must
     * never be removable via the admin UI.
     */
    #[\Override]
    protected function checkDeletable(mixed $entity): void
    {
        if (
            $entity instanceof LetterSectionEntity
            && $entity->getSectionKey() === '__ISSUES__'
        ) {
            throw new ValidationException([self::ERROR_KEY => 'The __ISSUES__ placeholder section cannot be deleted']);
        }
    }

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
