<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Doc\Document;

class CanDeleteDocument extends AbstractCanAccessEntity
{
    protected $repo = Repository\Document::class;

    /**
     * @throws NotFoundException
     */
    public function isValid($entityId): bool
    {
        $document = $this->getDocument($entityId);
        if ($this->isExternalUser() && $this->isDocumentAttachedToApplication($document)) {
            return $this->isCurrentApplicationDocAndIsNotSubmitted($document);
        }

        return parent::isValid($entityId);

    }

    private function isCurrentApplicationDocAndIsNotSubmitted($document): bool
    {
        return $document->getApplication()?->getStatus()->getId() === Application::APPLICATION_STATUS_NOT_SUBMITTED ?? false;
    }

    private function isDocumentAttachedToApplication(Document $document): bool
    {
        return $document->getApplication() !== null;
    }

    /**
     * @throws NotFoundException
     */
    private function getDocument($documentId): Document
    {
        return $this->getRepo(Repository\Document::class)->fetchById($documentId);
    }
}
