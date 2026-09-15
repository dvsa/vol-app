<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Repository;

class IsDocumentCreator extends AbstractDoesOwnEntity
{
    protected $repo = Repository\Document::class;

    #[\Override]
    public function isValid($entityId): bool
    {
        $document = $this->getEntity($entityId);
        return $document->getCreatedBy()?->getId() === $this->getCurrentUser()->getId();
    }
}
