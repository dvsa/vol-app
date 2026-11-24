<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Document;

class CanDownloadDocumentWithId extends CanAccessDocumentWithId
{
    #[\Override]
    public function isValid($dto)
    {
        return $this->canDownloadDocument($this->getId($dto));
    }
}
