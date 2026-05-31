<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterSection;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection as LetterSectionEntity;

/**
 * Delete LetterSection
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'LetterSection';

    /**
     * The __ISSUES__ placeholder section is reserved by the letter assembler and must
     * never be removable via the admin UI.
     */
    #[\Override]
    protected function checkDeletable($id, mixed $entity): void
    {
        if ($entity instanceof LetterSectionEntity
            && $entity->getSectionKey() === '__ISSUES__'
        ) {
            throw new ValidationException(['The __ISSUES__ placeholder section cannot be deleted']);
        }

        parent::checkDeletable($id, $entity);
    }
}
