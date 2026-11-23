<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Repository;

class CanDownloadDocument extends CanAccessDocument
{
    #[\Override]
    protected function canExternalUserAccessDocument(string $documentId): bool
    {
        return $this->isMessageAttachment($documentId) || parent::canExternalUserAccessDocument($documentId);
    }

    private function isMessageAttachment(string $documentId): bool
    {
        return $this->getRepo(Repository\Document::class)->fetchById($documentId)->getMessagingMessage() !== null;
    }
}
