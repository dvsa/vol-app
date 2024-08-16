<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Document;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

class CanAccessDocumentsWithIds extends AbstractHandler
{
    /**
     * Validate DTO
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface|\Dvsa\Olcs\Transfer\Query\QueryInterface $dto
     *
     * @return bool
     */
    public function isValid($dto)
    {
        foreach ($dto->getIds() as $documentId) {
            if (!$this->canAccessDocument($documentId)) {
                return false;
            }
        }
        return true;
    }
}
