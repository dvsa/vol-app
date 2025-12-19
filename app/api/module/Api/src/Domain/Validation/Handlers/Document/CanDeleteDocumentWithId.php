<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Document;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access a Document entity with an ID
 */
class CanDeleteDocumentWithId extends AbstractHandler
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
        return $this->canAccessDocument($this->getId($dto)) && $this->canDeleteDocument($this->getId($dto));
    }

    /**
     * Get the document ID
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface|\Dvsa\Olcs\Transfer\Query\QueryInterface $dto
     *
     * @return int
     */
    protected function getId($dto)
    {
        if (method_exists($dto, 'getIdentifier')) {
            return $dto->getIdentifier();
        }

        return $dto->getId();
    }
}
